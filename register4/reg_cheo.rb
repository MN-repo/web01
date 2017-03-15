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

require 'redis/connection/hiredis'

require 'sinatra/base'
require 'tilt/erb'

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

		# read in the settings file, trimming the "<?php" and "?>"
		eval(File.readlines('../../../../settings-jmp.php')[1..-2].
			join("\n"))

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
		@jid = CGI.escapeHTML(conn.read)
		@number = params['number']

		return erb :success
	end
end
