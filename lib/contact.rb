# frozen_string_literal: true

module Contact
	def self.for(type, value)
		case type
		when "xmpp"
			"xmpp:#{value}"
		when "mailto"
			"mailto:#{value}"
		when "matrix"
			"matrix:u/#{value.sub(/\A@/, '')}"
		end
	end
end
