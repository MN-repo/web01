#!/usr/bin/env ruby
#
# Copyright (C) 2017  Denver Gingerich <denver@ossguy.com>
#
# This file is part of jmp-register.
#
# jmp-register is free software: you can redistribute it and/or modify it under
# the terms of the GNU Affero General Public License as published by the Free
# Software Foundation, either version 3 of the License, or (at your option) any
# later version.
#
# jmp-register is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
# details.
#
# You should have received a copy of the GNU Affero General Public License along
# with jmp-register.  If not, see <http://www.gnu.org/licenses/>.

require 'blather/client/dsl'

require 'redis/connection/hiredis'

require 'sinatra/base'
require 'tilt/erb'

require 'timeout'


# read in the settings file, trimming the "<?php" and "?>"
eval File.readlines('../../../../settings-jmp.php')[1..-2].join("\n")

$q_send = Queue.new
$q_done = Queue.new

class SApp < Sinatra::Application
	get '/' do
		if not params.key?('jcode')
			@error_text = 'Verification code not entered.  Please '\
				'press Back and enter a verification code or '\
				'<a href="../">start again</a>.'
			return erb :error

		elsif not params.key?('number') or not params.key?('sid')
			@error_text = 'Session ID and/or number empty.  Please'\
				' <a href="../">start again</a>.'
			return erb :error
		end

		if params['number'].length != 12 or params['number'][0] !=
			'+' or params['number'][1..-1].to_i.to_s !=
			params['number'][1..-1]  # last part: is [1..-1] an int?

			@error_text = CGI.escapeHTML(params['number']) +
				' is not an E.164 NANP number.  Please '\
				'<a href="../">start again</a>.'
			return erb :error
		end

		bThread = Thread.new { BApp.run }

		conn = Hiredis::Connection.new
		conn.connect($redis_host, $redis_port)

		# TODO: support Redis auth

		jidMaybeKey = 'reg-jid_maybe-' + params['sid']
		jidGoodKey = 'reg-jid_good-' + params['sid']

		conn.write ["GET", jidMaybeKey]
		maybeJid = conn.read

		if maybeJid.nil?
			@error_text = 'Could not find JID to verify; perhaps '\
				'it has already been verified.  Feel free to '\
				'<a href="../">start again</a> if not.'
			return erb :error
		end

		hitsKey = 'reg-jid_hits-' + maybeJid;
		conn.write ["INCR", hitsKey]
		hitCount = conn.read

		jcodeKey = 'reg-jcode-' + maybeJid;

		@cleanSid = params['sid'].gsub(/[^0-9a-f]/, "")


	        # if > 10 hits, do NOT allow verification to occur (rate limit)
		if hitCount > 10
			conn.write ["TTL", hitsKey]
			if conn.read < 0
				conn.write ["EXPIRE", hitsKey, 600]
				conn.read  # TODO: check value to confirm worked
			end

			@error_text = 'Too many verification attempts.  Please'\
				' refresh this page in about 10 minutes or '\
				'<a href="../">start again</a>.'
			return erb :error
		end

		conn.write ["GET", jcodeKey]
		if params['jcode'].downcase != conn.read
			@error_text = '</p>
<form action="../register4/">
<p>
<input type="hidden" name="number" value="' + params['number'] + '" />
<input type="hidden" name="sid" value="' + @cleanSid + '" />
Invalid verification code (' + CGI.escapeHTML(params['jcode']) + ').  Please
enter a new code to try again: <input type="text" name="jcode" />
<input type="submit" value="Submit" />
</p>
</form>
<p>'
			return erb :error
		end

		# we overwrite old value - if multiple JIDs verified, use last
		conn.write ["RENAME", jidMaybeKey, jidGoodKey]
		conn.read  # TODO: check value to confirm worked

		conn.write ["GET", jidGoodKey]
		jid = conn.read


		# now that JID is verified, register it with Cheogram
		$q_send.push(jid)

		begin
			status = Timeout::timeout(5) {
				# TODO: return val (added/removed) was expected?
				$q_done.pop
			}
		rescue Timeout::Error
			# TODO: ensure user's creds deleted and add support link
			@error_text = 'Timeout while attempting to register '\
				'JID; please contact support or feel free to '\
				'<a href="../">start again</a>.'
			return erb :error
		end


		# TODO: XEP-0106 Sec 4.3 compliance; won't work with pre-escaped
		cheo_jid = jid.
			gsub("\\", "\\\\5c").
			gsub(' ', "\\\\20").
			gsub('"', "\\\\22").
			gsub('&', "\\\\26").
			gsub("'", "\\\\27").
			gsub('/', "\\\\2f").
			gsub(':', "\\\\3a").
			gsub('<', "\\\\3c").
			gsub('>', "\\\\3e").
			gsub('@', "\\\\40") + '@' + $cheogram_jid

		# TODO: should set TTL as well, but this line of code won't last
		conn.write ["SET", jidGoodKey, cheo_jid]
		conn.read  # TODO: check value to confirm worked


		@jid = CGI.escapeHTML(jid)
		@number = params['number']

		EM.stop

		return erb :success
	end
end

module BApp
	extend Blather::DSL

	def self.run
		EM.run { client.run }
	end

	setup $cheogram_register_jid, $cheogram_register_token

	def self.m_command(jid, action, sid = '', node_to_add = nil)
		msg = Blather::Stanza::Iq.new :set
		msg.to = $cheogram_jid

		f = Nokogiri::XML::Node.new 'forwarded', msg.document
		f['xmlns'] = 'urn:xmpp:forward:0'
		msg.add_child(f)

		i = Blather::Stanza::Iq::Command.new(:set,
			'configure-direct-message-route', action)

		if not sid.empty?
			i.command['sessionid'] = sid
		end

		if not node_to_add.nil?
			i.command.add_child(node_to_add)
		end

		i.from = jid
		i['xmlns'] = 'jabber:client'
		f.add_child(i)

		return msg
	end

	# TODO: upgrade to multi-use (how we know how many?) currently one-shot;
	#  this would mainly be if we ran from Passenger instead of from CGI
	when_ready do
		jid = $q_send.pop

		client.write_with_handler(m_command(jid, :execute)) { |i|
			$stderr.puts 'I: ' + i.inspect
			cn = i.children.find { |v| v.element_name == "command" }
			ch_sid = cn['sessionid']

			# TODO: i.document wrong - we're making x before its doc
			x = Nokogiri::XML::Node.new 'x', i.document
			x['xmlns'] = 'jabber:x:data'
			x['type'] = 'submit'

			if $sgx_jid.empty?
				# mostly for future expansion; not usually empty
				client.write_with_handler(m_command(jid, :next,
					ch_sid, x)) { |m|

					$stderr.puts 'M: ' + m.inspect
					$q_done.push(:removed)
				}
			else
				# TODO: i.document wrong
				field = Nokogiri::XML::Node.new 'field',
					i.document
				field['var'] = 'gateway-jid'
				field['type'] = 'jid-single'
				x.add_child(field)

				# TODO: i.document wrong
				v = Nokogiri::XML::Node.new 'value', i.document
				v.content = $sgx_jid
				field.add_child(v)

				client.write_with_handler(m_command(jid, :next,
					ch_sid, x)) { |j|

					$stderr.puts 'J: ' + j.inspect
					client.write_with_handler(m_command(jid,
						:complete, ch_sid)) { |k|

						$stderr.puts 'K: ' + k.inspect
						$q_done.push(:added)
					}
				}
			end
		}
	end
end
