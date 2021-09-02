# frozen_string_literal: true

require "fiber"

module Rack
	class Fiber
		def initialize(app)
			@app = app
		end

		def call(env)
			async_callback = env.delete('async.callback')
			EM.next_tick do
				::Fiber.new {
					begin
						async_callback.call(@app.call(env))
					rescue ::Exception
						async_callback.call([500, {}, [$!.to_s]])
					end
				}.resume
			end
			throw :async
		end
	end
end
