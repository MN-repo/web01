# frozen_string_literal: true

require "value_semantics/monkey_patched"

class MXID
	class BadFormat < StandardError; end

	value_semantics do
		node String
		server(/\./)
	end

	def self.parse(str)
		node, server = str.split(/:/, 2)
		case node[0]
		when "@"
			new(node: node[1..], server: server)
		when "#"
			Channel.new(node: node[1..], server: server)
		else
			raise BadFormat, "Bad MXID format: #{str}"
		end
	end

	def jid(gateway="aria-net.org")
		"#{node}_#{server}@#{gateway}"
	end

	class Channel < MXID
		def jid(gateway="aria-net.org")
			"##{node}##{server}@#{gateway}"
		end
	end
end
