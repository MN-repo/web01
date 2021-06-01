#!/usr/bin/env rackup
require "sentry-ruby"

require 'blather/client/dsl'

require 'json'
require 'net/http'
require 'uri'

require 'sinatra/base'
require 'erb'
require 'erubi'

require 'timeout'

Sentry.init do |config|
	config.logger = Logger.new(nil)
	config.traces_sample_rate = 1
end
use Sentry::Rack::CaptureExceptions

# read in the settings file, trimming the "<?php" and "?>"
eval File.readlines('../../../../settings-jmp.php')[1..-2].join("\n")

class SApp < Sinatra::Application
	set :erb, escape_html: true
	include ERB::Util

	get '/' do
		Sentry.set_user(tel: params["number"])
		if params["number"] !~ /\A\+\d{11}\Z/
			erb :invalid_tel
		else
			erb :form
		end
	end

	post '/' do
		Sentry.set_user(jid: params["jid"], tel: params["number"])
		q = Queue.new
		BApp.write_with_handler(Blather::Stanza::Iq::Command.new.tap { |cmd|
			cmd.to = $sgx_jmp_jid
			cmd.node = "web-register"
			cmd.action = :execute
			cmd.form.type = "submit"
			cmd.form.fields = [
				{ var: "jid", value: params["jid"] },
				{ var: "tel", value: params["number"] }
			]
		}) { |stanza| q << stanza }
		Timeout.timeout(30) { q.pop }
		erb :success
	end
end

module BApp
	extend Blather::DSL

	@ready = Queue.new

	when_ready { @ready << :ready }

	def self.start
		# workqueue_count MUST be 0 or else Blather uses threads!
		setup(
			$cheogram_register_jid,
			$cheogram_register_token,
			nil, nil, nil, nil,
			workqueue_count: 0
		)

		EM.error_handler { |e| Sentry.capture_exception(e) }
		@thread = Thread.new do
			EM.run do
				client.run
			end
		end

		at_exit { wait_then_exit }

		Timeout.timeout(30) { @ready.pop }
	end

	def self.write_with_handler(stanza, &block)
		client.write_with_handler(stanza, &block)
	end

	def self.wait_then_exit
		shutdown
		disconnected { EM.stop }
		@thread.join
	end
end

BApp.start
run SApp
