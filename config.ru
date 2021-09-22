# frozen_string_literal: true

require "dhall"
require "erb"
require "roda"
require "blather/client/dsl"
require "geoip"
require "redis"

require_relative "lib/maxmind"
require_relative "lib/roda_em_promise"
require_relative "lib/rack_fiber"
require_relative "lib/tel_query_form"

use Rack::Fiber # Must go first!

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
MAXMIND = Maxmind.new(Redis.new, GEOIP, **CONFIG[:maxmind])

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
		puts "XMPP ready..."
	end

	def self.write_with_promise(stanza)
		promise = EMPromise.new
		client.write_with_handler(stanza) { |s| promise.fulfill(s) }
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

class JmpRegister < Roda
	include ERB::Util

	plugin :common_logger, $stdout
	plugin :render, engine: "slim"
	plugin :content_for
	plugin :assets, css: ["style.scss"]
	plugin :environments
	plugin RodaEMPromise # Must go last!

	compile_assets if production?

	def faq_entry(id, q, &block)
		render(:faq_entry, locals: { id: id, q: q }, &block)
	end

	def tels_embedded(form)
		return tels("Indianapolis, IN") if form.empty?
		render :tels, locals: { form: form, embed: true }
	end

	def tels(q)
		Jabber.execute(
			"jabber:iq:register",
			TelQueryForm.new(q, request.params["max"] || 2000).to_node
		).then do |iq|
			Jabber.cancel(iq)
			form = TelQueryForm.parse(iq)
			next tels_embedded(form) if request.params.key?("embed")

			view :tels, locals: { form: form, embed: false }
		end
	end

	route do |r|
		r.assets if JmpRegister.development?

		r.root do
			canada = GEOIP.country(request.ip).country_code2 == "CA"
			Jabber.execute("jabber:iq:register").then do |iq|
				view :home, locals: { canada: canada, form: iq.form }
			end
		end

		r.get "tels" do
			EMPromise.resolve(
				request.params["q"] || MAXMIND.q(request.ip).then(&:to_q)
			).catch { "307" }.then(&method(:tels))
		end

		r.get "faq" do
			view :faq
		end

		r.get "credits" do
			view :credits
		end

		r.get "upgrade1" do
			view :upgrade1
		end

		r.get /([^\/]+)\/\Z/ do |match|
			r.redirect "/#{match}", 301
		end
	end
end

EM.next_tick { Jabber.run }
run JmpRegister.freeze.app
