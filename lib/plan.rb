# frozen_string_literal: true

# TODO: use a common plan.rb file shared with sgx-jmp,
# as this copy of sgx-jmp's could get out of sync

require "bigdecimal/util"
require "value_semantics/monkey_patched"

class Plan
	def self.for(plan_name)
		plan = CONFIG[:plans].find { |p| p[:name] == plan_name }
		raise "No plan by that name" unless plan

		new(plan)
	end

	def self.find_by_currency(currency)
		plan = CONFIG[:plans].find { |p| p[:currency] == currency.to_sym }
		new(plan) if plan
	end

	def initialize(plan)
		@plan = plan
	end

	def name
		@plan[:name]
	end

	def currency
		@plan[:currency]
	end

	def monthly_price
		BigDecimal(@plan[:monthly_price]) / 10000
	end

	def merchant_account
		CONFIG[:braintree][:merchant_accounts].fetch(currency) do
			raise "No merchant account for this currency"
		end
	end

	def minute_limit
		CallingLimit.new(Limit.for("minute", @plan[:minutes]))
	end

	def message_limit
		Limit.for(
			"in/out SMS and MMS, including international message",
			@plan[:messages]
		)
	end

	class Limit
		def self.for(unit, from_config)
			case from_config
			when :unlimited
				Unlimited.new(unit)
			else
				new(unit: unit, **from_config)
			end
		end

		value_semantics do
			unit String
			included Integer
			price Integer
		end

		def to_s
			"#{included} #{unit}s " \
			"(overage $#{'%.4f' % (price.to_d / 10000)} / #{unit})"
		end

		def to_d
			included.to_d / 10000
		end

		class Unlimited
			def initialize(unit)
				@unit = unit
			end

			def to_s
				"unlimited #{@unit}s"
			end
		end
	end

	class CallingLimit
		def initialize(limit)
			@limit = limit
		end

		def price
			BigDecimal(@limit.price) / 10000
		end

		def to_d
			@limit.to_d
		end

		def to_s
			"#{'$%.4f' % to_d} of calling credit per calendar month"
		end
	end
end
