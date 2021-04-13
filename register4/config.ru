#!/usr/bin/env rackup
require "sentry-ruby"
Sentry.init do |config|
	config.logger = Logger.new(nil)
	config.traces_sample_rate = 1
end
use Sentry::Rack::CaptureExceptions

require './reg_cheo.rb'
run SApp
