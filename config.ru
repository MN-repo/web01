# frozen_string_literal: true

require "roda"
require "blather/client/dsl"

require_relative "lib/roda_em_promise"
require_relative "lib/rack_fiber"

use Rack::Fiber # Must go first!

if ENV["RACK_ENV"] == "development"
	require "pry-rescue"
	use PryRescue::Rack
end

module Jabber
	extend Blather::DSL

	# workqueue_count MUST be 0 or else Blather uses threads!
	setup(
		"test@localhost",
		"test",
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
end

class JmpRegister < Roda
	plugin :common_logger, $stdout
	plugin :render, engine: "slim"
	plugin :content_for
	plugin :assets, css: ["style.scss"]
	plugin :environments
	plugin RodaEMPromise # Must go last!

	compile_assets if production?

	route do |r|
		r.assets if JmpRegister.development?

		r.root do
			Jabber.write_with_promise(
				Blather::Stanza::Iq::Command.new.tap { |cmd|
					cmd.to = "component2.localhost"
					cmd.node = "jabber:iq:register"
					cmd.action = :execute
				}
			).then do |iq|
				view(:iq, locals: { form: iq.form })
			end
		end
	end
end

EM.next_tick { Jabber.run }
run JmpRegister.freeze.app
