# frozen_string_literal: true

require "cbor"
require "em-http"
require "em-http/middleware/json_response"
require "em_promise"
require "statsd-instrument"

class Maxmind
	def initialize(memcache, geoip, user:, token:)
		@memcache = memcache
		@geoip = geoip
		@user = user
		@token = token
	end

	def cache(ip)
		promise = EMPromise.new
		@memcache.get("mmgp_result-#{ip}", &promise.method(:fulfill))

		StatsD.increment("maxmind_cache.queries")
		promise.then { |cbor|
			StatsD.increment("maxmind_cache.hit") if cbor
			CBOR.decode(cbor)
		}.catch { yield ip }.then { |result|
			@memcache.set("mmgp_result-#{ip}", result.to_cbor)
			result
		}
	end

	def call_api(ip)
		EM::HttpRequest.new(
			"https://geoip.maxmind.com/geoip/v2.1/city/#{ip}",
			tls: { verify_peer: true }
		).tap { |conn|
			conn.use EM::Middleware::JSONResponse
		}.get(
			head: {
				"Authorization" => [@user, @token]
			}
		)
	end

	def q(ip)
		unless ["US", "CA"].include?(@geoip.country(ip).country_code2)
			OriginalStdOutStdErr.write("#{ip} is not US or CA\n")
			return EMPromise.reject(nil)
		end

		cache(ip) {
			call_api(ip).then do |http|
				guard_response http
				http.response
			end
		}.then(&Response.method(:for))
	end

	def guard_response(http)
		return if http.response_header.status.to_i == 200

		raise "Maxmind bad response #{http.response_header.status}"
	end

	class Response
		def self.for(response)
			if response["country"]["iso_code"] == "US" &&
			   response.dig("postal", "code")
				Zip.new(response["postal"]["code"])
			else
				CityState.new(
					response.dig("city", "names", "en"),
					response["subdivisions"]&.first&.dig("iso_code")
				)
			end
		end

		class Zip
			def initialize(zip)
				@zip = zip
			end

			def to_q
				@zip
			end
		end

		class CityState
			def initialize(city, state)
				@city = city
				@state = state
			end

			def to_q
				"#{@city}, #{@state}"
			end
		end
	end
end
