# frozen_string_literal: true

require "dhall"
require "em-hiredis"
require "erb"
require "roda"
require "blather/client/dsl"
require "geoip"

require_relative "lib/maxmind"
require_relative "lib/roda_em_promise"
require_relative "lib/rack_fiber"
require_relative "lib/tel_query_form"
require_relative "lib/to_form"

use Rack::Fiber # Must go first!

require "sentry-ruby"
Sentry.init do |config|
	config.traces_sample_rate = 1
end
use Sentry::Rack::CaptureExceptions

if ENV["RACK_ENV"] == "development"
	require "pry-rescue"
	use PryRescue::Rack
end

CONFIG =
	Dhall::Coder
	.new(safe: Dhall::Coder::JSON_LIKE + [Symbol, Proc])
	.load(
		"env:CONFIG : ./config-schema.dhall",
		transform_keys: ->(k) { k&.to_sym }
	)
GEOIP = GeoIP.new("/usr/share/GeoIP/GeoIPv6.dat")

EM.next_tick do
	REDIS = EM::Hiredis.connect
	MAXMIND = Maxmind.new(REDIS, GEOIP, **CONFIG[:maxmind])
end

module OriginalStdOutStdErr
	OUT = $stdout.dup
	ERR = $stderr.dup
	OUT.sync = true

	# After a daemonize action or similar, reset standard streams to original
	# Unless they are to a TTY
	def self.reset
		$stdout.reopen(OUT) unless OUT.tty?
		$stderr.reopen(ERR) unless ERR.tty?
	end

	# Write to original out stream, unless it was a tty and has been changed
	def self.write(s)
		OUT.write(s) unless OUT.closed? || (OUT.tty? && !$stdout.tty?)
	rescue Errno::EIO
		# Writing to a disconnected terminal
		nil
	end
end

module Jabber
	extend Blather::DSL

	# workqueue_count MUST be 0 or else Blather uses threads!
	setup(
		CONFIG[:jid],
		CONFIG[:password],
		nil,
		nil,
		nil,
		nil,
		workqueue_count: 0
	)

	when_ready do
		OriginalStdOutStdErr.reset
		$stdout.sync = true
		$stderr.sync = true
		puts "XMPP ready..."
	end

	def self.write_with_promise(stanza)
		promise = EMPromise.new
		client.write_with_handler(stanza) do |s|
			if s.error?
				promise.reject(s)
			else
				promise.fulfill(s)
			end
		end
		promise
	end

	def self.command(node, sessionid=nil, action: :execute, form: nil)
		Blather::Stanza::Iq::Command.new.tap do |cmd|
			cmd.to = CONFIG[:sgx_jmp]
			cmd.node = node
			cmd.command[:sessionid] = sessionid if sessionid
			cmd.action = action
			cmd.command << form if form
		end
	end

	def self.execute(command_node, form=nil)
		write_with_promise(command(command_node)).then do |iq|
			next iq unless form

			write_with_promise(command(command_node, iq.sessionid, form: form))
		end
	end

	def self.cancel(iq)
		write_with_promise(command(iq.node, iq.sessionid, action: :cancel))
	end
end

# rubocop:disable Metrics/ClassLength
class JmpRegister < Roda
	include ERB::Util
	using ToForm

	plugin :common_logger, OriginalStdOutStdErr
	plugin :render, engine: "slim"
	plugin :content_for
	plugin :branch_locals
	plugin :assets, css: ["style.scss"], add_suffix: true
	plugin :public
	plugin :environments
	plugin RodaEMPromise # Must go last!

	def faq_entry(id, q, &block)
		render(:faq_entry, locals: { id: id, q: q }, &block)
	end

	def tels_embedded(form, fallbacks)
		return tels(fallbacks.shift, fallbacks) if form.empty? && !fallbacks.empty?
		render :tels, locals: { form: form, embed: true }
	end

	def tels(q, fallbacks=CONFIG[:fallback_searches])
		Jabber.execute(
			"jabber:iq:register",
			TelQueryForm.new(q, request.params["max"] || 2000).to_node
		).then do |iq|
			Jabber.cancel(iq)
			form = TelQueryForm.parse(iq)
			next tels_embedded(form, fallbacks) if request.params.key?("embed")

			view :tels, locals: { form: form, embed: false }
		end
	end

	def tel
		request.params["tel"] || request.params["number"]
	end

	route do |r|
		r.root do
			canada = GEOIP.country(request.ip).country_code2 == "CA"
			Jabber.execute("jabber:iq:register").catch {
				OpenStruct.new(form: Blather::Stanza::X.new)
			}.then do |iq|
				view :home, locals: { canada: canada, form: iq.form }
			end
		end

		r.get "tels" do
			EMPromise.resolve(
				request.params["q"] || MAXMIND.q(request.ip).then(&:to_q)
			).catch { CONFIG[:fallback_searches].first }.then(&method(:tels))
		end

		r.on "register" do
			r.redirect "/" unless tel.to_s.match?(/\A\+1\d{10}\Z/)
			Sentry.set_user(tel: tel)

			set_view_locals city: request.params["city"]

			r.on "jabber" do
				r.get "new" do
					view "register/jabber/new"
				end

				r.get true do
					view "register/jabber"
				end

				r.post true do
					Sentry.set_user(jid: request.params["jid"], tel: tel)
					Jabber.execute(
						"web-register",
						{ jid: request.params["jid"], tel: tel }.to_form(:submit)
					).then do
						view "register/jabber/success"
					end
				end
			end

			r.get "snikket" do
				view "register/snikket"
			end

			r.get true do
				view :register
			end
		end

		r.on "ipn-endpoint" do
			REDIS.xadd("paypal_ipn", "*", *request.params.flatten(1).to_a).then do
				"OK"
			end
		end

		r.get "faq" do
			view :faq
		end

		r.get "privacy" do
			view :privacy
		end

		r.get "credits" do
			view :credits
		end

		r.get "upgrade1" do
			view :upgrade1
		end

		r.get "paypal-migration" do
			view :paypal_migration
		end

		r.get(/porting[12]\Z/) do
			view :porting
		end

		r.get "register1" do
			r.redirect "/", 301
		end

		r.get "getjid" do
			r.redirect "/register/jabber/new?#{request.query_string}", 301
		end

		r.get(/register(?:-jid|2)\Z/) do
			r.redirect "/register/jabber?#{request.query_string}", 301
		end

		r.get(/([^\/]+)\/\Z/) do |match|
			qs = request.query_string.to_s != "" ? "?#{request.query_string}" : ""
			r.redirect "/#{match}#{qs}", 301
		end

		r.assets if JmpRegister.development?
		r.public if JmpRegister.development?
	end
end
# rubocop:enable Metrics/ClassLength

EM.next_tick { Jabber.run }
run JmpRegister.freeze.app
