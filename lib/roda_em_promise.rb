# frozen_string_literal: true

require "em_promise"

module RodaEMPromise
	module RequestMethods
		def block_result(result)
			super(EMPromise.resolve(result).sync)
		end
	end
end
