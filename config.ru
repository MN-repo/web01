# frozen_string_literal: true

require "dhall"
require "em-hiredis"
require "em_promise"
require "erb"
require "pg/em/connection_pool"
require "roda"
require "blather/client/dsl"
require "geoip"

require_relative "lib/maxmind"
require_relative "lib/mxid"
require_relative "lib/plan"
require_relative "lib/rack_fiber"
require_relative "lib/rates"
require_relative "lib/roda_em_promise"
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
	MEMCACHE = EM::P::Memcache.connect
	MAXMIND = Maxmind.new(MEMCACHE, GEOIP, **CONFIG[:maxmind])
	DB = PG::EM::ConnectionPool.new(dbname: "jmp") { |conn|
		conn.type_map_for_results = PG::BasicTypeMapForResults.new(conn)
		conn.type_map_for_queries = PG::BasicTypeMapForQueries.new(conn)
	}
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
		EM.add_timer(6.5) { promise.reject("Timeout") }
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
	plugin :render, engine: "slim", engine_opts: {
		"slim" => { markdown: { f: "commonmark" } }
	}
	plugin :content_for
	plugin :branch_locals
	plugin(
		:assets,
		css: { global: "style.scss", tom_select: "tom_select.scss" },
		js: {
			section_list: "section_list.js",
			tom_select: "tom_select.js",
			htmx: "htmx.js"
		},
		add_suffix: true
	)
	plugin :public
	plugin :environments
	plugin :head
	plugin :status_handler
	plugin RodaEMPromise # Must go last!

	status_handler(404) do
		view 404
	end

	def faq_entry(id, q, &block)
		render(:faq_entry, locals: { id: id, q: q }, &block)
	end

	def strip_trailing_slash!
		request.get(/(?:.*\/)?\Z/) do
			qs = request.query_string.to_s != "" ? "?#{request.query_string}" : ""
			request.redirect "#{request.path.sub(/\/\Z/, '')}#{qs}", 301
		end
	end

	def embed
		request.params.key?("embed")
	end

	def embedded_tel_falback(form, fallbacks)
		if embed && form.empty? && !fallbacks.empty?
			tels(fallbacks[0], fallbacks[1..])
		else
			form
		end
	end

	def tels(q, fallbacks=CONFIG[:fallback_searches])
		Jabber.execute(
			"jabber:iq:register",
			TelQueryForm.new(q, request.params["max"] || 1000).to_node
		).then do |iq|
			Jabber.cancel(iq)
			form = TelQueryForm.parse(iq)
			embedded_tel_falback(form, fallbacks)
		end
	end

	def tel
		request.params["tel"] || request.params["number"]
	end

	def canada?
		GEOIP.country(request.ip).country_code2 == "CA"
	end

	route do |r|
		r.root do
			Jabber.execute("jabber:iq:register").catch {
				OpenStruct.new(form: Blather::Stanza::X.new)
			}.then do |iq|
				view :home, locals: { form: iq.form }
			end
		end

		r.get "tels" do
			EMPromise.resolve(
				request.params["q"] || MAXMIND.q(request.ip).then(&:to_q)
			).catch { |e|
				OriginalStdOutStdErr.write("#{request.ip} lookup failed: #{e.inspect}")
				CONFIG[:fallback_searches].first
			}.then(&method(:tels)).then { |form|
				if embed
					render :tels, locals: { form: form }
				else
					view :tels, locals: { form: form }
				end
			}.catch { |e|
				render :retry, locals: { error: e }
			}
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
					).then do |iq|
						if iq.note_type
							view "register/jabber/note", locals: { iq: iq }
						else
							view "register/jabber/success"
						end
					end
				end

				strip_trailing_slash!
			end

			r.get "snikket" do
				view "register/snikket"
			end

			r.on "matrix" do
				r.get true do
					view "register/matrix"
				end

				r.post true do
					Sentry.set_user(mxid: request.params["mxid"], tel: tel)
					Jabber.execute(
						"web-register",
						{
							jid: MXID.parse(request.params["mxid"]).jid,
							tel: tel
						}.to_form(:submit)
					).then do |iq|
						if iq.note_type
							view "register/jabber/note", locals: { iq: iq }
						else
							view "register/matrix/success"
						end
					end
				rescue MXID::BadFormat
					$!.message
				end
			end

			r.get true do
				view :register
			end

			strip_trailing_slash!
		end

		r.on "pricing" do
			r.get :currency do |currency|
				plan = Plan.find_by_currency(currency)
				if plan && (prefix = request.params["prefix"])
					RateRepo.new.find_by_prefix(plan.name, prefix).then do |rate|
						rate ? "$%.4f" % rate : ""
					end
				elsif plan
					RateRepo.new.plan_cards(plan.name).then do |cards|
						view "pricing", locals: { plan: plan, cards: cards }
					end
				else
					response.status = 404
					false
				end
			end

			r.get true do
				if canada?
					r.redirect "/pricing/CAD", 303
				else
					r.redirect "/pricing/USD", 303
				end
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

		r.get "notify_signup" do
			r.redirect "https://soprani.ca/cgi-bin/mailman/listinfo/jmp-news", 301
		end

		r.get(/register(?:-jid|2)\Z/) do
			r.redirect "/register/jabber?#{request.query_string}", 301
		end

		r.on "sp1a", method: :get do
			r.redirect r.remaining_path, 301
		end

		strip_trailing_slash!

		r.assets if JmpRegister.development?
		r.public if JmpRegister.development?
	end
end
# rubocop:enable Metrics/ClassLength

EM.next_tick { Jabber.run }
run JmpRegister.freeze.app
