# frozen_string_literal: true

require "fiber"

module Rack
	class Fiber
		def initialize(app)
			@app = app
		end

		def call(env)
			async_callback = env.delete("async.callback")
			EM.next_tick { run_fiber(env, async_callback) }
			throw :async
		end

	protected

		def run_fiber(env, async_callback)
			::Fiber.new {
				begin
					async_callback.call(@app.call(env))
				rescue ::Exception # rubocop:disable Lint/RescueException
					async_callback.call([500, {}, [$!.to_s]])
				end
			}.resume
		end
	end
end
