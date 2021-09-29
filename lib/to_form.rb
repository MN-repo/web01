# frozen_string_literal: true

require "blather"

module ToForm
	refine ::Hash do
		def to_fields
			map { |k, v| { var: k.to_s, value: v.to_s } }
		end

		def to_form(type)
			Blather::Stanza::Iq::X.new(type).tap do |form|
				form.fields = to_fields
			end
		end
	end
end
