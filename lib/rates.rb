# frozen_string_literal: true

require "csv"
require "countries"

# Kosovo not recognized by ISO yet
ISO3166::Data.register(
	alpha2: "XK",
	alpha3: "XKX",
	name: "Kosovo",
	currency: "EUR",
	translations: { "en" => "Kosovo" }
)

NON_GEOGRAPHIC = Class.new {
	def emoji_flag; end

	def alpha2
		"non-geographic"
	end

	def translation(_lang)
		"Non-Geographic (sattelite phone, etc)"
	end
}.new

class Rate
	attr_reader :prefix

	def initialize(row)
		@prefix = row["prefix"]
		@rate = row["rate"]
	end

	def rate
		if prefix == "+1"
			"$%.4f" % @rate
		else
			"$%.3f" % @rate
		end
	end
end

class Prefixes
	def initialize(countries)
		# Group country codes by the first digit in each associated prefix
		@countries = countries.group_by { |c| c[1][1] }
		freeze
	end

	def [](prefix)
		code = @countries[prefix[1]].select { |(_, p)|
			prefix.start_with?(p)
		}.max_by(&:length)&.first

		if code == "NonGeographic"
			NON_GEOGRAPHIC
		else
			ISO3166::Country[code]
		end
	end
end

class RateRepo
	COUNTRIES = Prefixes.new(CSV.new(
		File.open("#{__dir__}/../countries.csv")
	))

	def initialize(db: DB)
		@db = db
	end

	ALL_SQL = <<~SQL
		SELECT prefix, rate
		FROM call_rates
		WHERE
			direction='outbound' AND
			plan_name=$1
	SQL

	def plan_cards(plan)
		@db.query_defer(ALL_SQL, [plan]).then do |rates|
			rates.group_by { |rate|
				COUNTRIES[rate["prefix"]]
			}.transform_values { |rs|
				rs.map(&Rate.method(:new))
			}.tap { |cards|
				# These two share a country code and a rate
				cards[ISO3166::Country["US"]] = cards[ISO3166::Country["CA"]]
			}.sort_by { |(country, _)| country.translation("en") }
		end
	end
end
