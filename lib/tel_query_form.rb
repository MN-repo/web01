# frozen_string_literal: true

require "blather"

require_relative "to_form"

class TelQueryForm
	using ToForm

	def initialize(q, max)
		@q = q
		@max = max
	end

	def to_node
		{ q: @q }.to_form(:submit).tap do |form|
			Nokogiri::XML::Builder.with(form) do |xml|
				xml.set xmlns: "http://jabber.org/protocol/rsm" do
					xml.max @max
				end
			end
		end
	end

	def self.parse(iq)
		form = iq.form
		def form.empty?
			Array(field("tel")&.options).empty?
		end
		form
	end
end
