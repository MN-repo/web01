# frozen_string_literal: true

require "blather"

class TelQueryForm
	def initialize(q, max)
		@q = q
		@max = max
	end

	def to_node
		Blather::Stanza::Iq::X.new(:form).tap do |form|
			form.fields = [var: "q", value: @q]
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
