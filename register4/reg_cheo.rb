#!/usr/bin/env ruby
#
# Copyright (C) 2017-2018, 2020  Denver Gingerich <denver@ossguy.com>
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

require 'json'
require 'net/http'
require 'uri'

require 'redis/connection/hiredis'

require 'open3'
require 'securerandom'

require 'sinatra/base'
require 'tilt/erb'
require 'webrick'

require 'timeout'


# read in the settings file, trimming the "<?php" and "?>"
eval File.readlines('../../../../settings-jmp.php')[1..-2].join("\n")

$q_send = Queue.new
$q_done = Queue.new

class SApp < Sinatra::Application
	get '/' do
		# TODO NOW: MUST confirm that reg attempt for DNE number fails
		#  spec'ly must fail gracefully - user can retry, no partial reg

		if not params.key?('number') or not params.key?('sid')
			$stderr.puts 'sError - no sid or number'
			@error_text = 'Session ID and/or number empty.  Please'\
				' <a href="../">start again</a>.'
			return erb :error
		end

		if params['number'].length != 12 or params['number'][0] !=
			'+' or params['number'][1..-1].to_i.to_s !=
			params['number'][1..-1]  # last part: is [1..-1] an int?

			$stderr.puts 'nError when trying to buy ' +
				CGI.escapeHTML(params['number'])
			@error_text = CGI.escapeHTML(params['number']) +
				' is not an E.164 NANP number.  Please '\
				'<a href="../">start again</a>.'
			return erb :error
		end

		bThread = Thread.new { BApp.run }

		conn = Hiredis::Connection.new
		conn.connect($redis_host, $redis_port)

		# TODO: support Redis auth

		jidDefinitelyKey = 'reg-jid_definitely-' + params['sid']

		conn.write ["GET", jidDefinitelyKey]
		jid = conn.read

		if jid.nil?
			$stderr.puts 'jError when trying to verify sid ' +
				params['sid']
			@error_text = 'It looks like your session has expired;'\
				' usually this means that you registered more '\
				'than 24 hours ago.  It seems you requested '\
				'JMP number ' + params['number'] + ' - if that'\
				' is what you were looking for, we\'re glad we'\
				' could help.  If you need your SIP acct info '\
				'see <a href="../#calling">this FAQ</a> (2nd '\
				'paragraph).  If you have any other questions '\
				'<a href="../#support">contact support</a>.'
			conn.disconnect
			return erb :error
		end


		# do the actual sgx-catapult registration behind its back

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


		paymentPlanKey = 'payment-plan_as_of_' +
			Date.today.strftime('%Y%m') + '-' + cheo_jid

		conn.write ["GET", paymentPlanKey]
		payPlan = conn.read

		# TODO: don't hard-code expected payment plan name
		if payPlan.nil? or payPlan != 'xxx_stable_trial-v20200913'
			$stderr.puts 'yError when trying to verify plan for ' +
				jid + ' with plan name "' + payPlan.to_s + '"'
			# TODO: confirm this text is correct
			@error_text = 'Trial account has not been approved or '\
				'other issue with payment plan for this JID.  '\
				'<a href="../#support">Contact support</a> to '\
				'fix or <a href="../">start again</a>.'
			conn.disconnect
			return erb :error
		end


		credKey = 'catapult_cred-' + cheo_jid

		# TODO: figure out better error message here; think of each case
		conn.write ["EXISTS", credKey]
		if conn.read == 1
			conn.write ["LINDEX", 'catapult_cred-' + cheo_jid, 3]
			existing = conn.read

			$stderr.puts 'cError when trying to register JID "' +
				CGI.escapeHTML(jid) + '" with number ' +
				params['number'] + ' and sid ' + params['sid'] +
				' and existing JMP number "' + existing + '"'
			@error_text = 'This JID (' + CGI.escapeHTML(jid) +
				') is/has already registered with JMP number ' +
				existing + '.  If you need your SIP acct info '\
				'see <a href="../#calling">this FAQ</a> (2nd '\
				'paragraph).  If you want to use the requested'\
				' number (' + params['number'] + ') with a new'\
				' JID, then <a href="../">start again</a>. Or '\
				'<a href="../#support">contact support</a> to '\
				'use this JID with a new number.'
			conn.disconnect
			return erb :error
		end


		# TODO NOW: delete below IP check (should be fine with new flow)
		# confirm that there haven't been too many requests from this IP
		ipHitsKey = 'reg-ipa_hits-' + request.ip
		conn.write ["INCR", ipHitsKey]
		ipHitCount = conn.read

		# key expires a day after first being set
		conn.write ["TTL", ipHitsKey]
		if conn.read < 0
			conn.write ["EXPIRE", ipHitsKey, 86400]
			conn.read  # TODO: check value to confirm worked
		end

		# if > 5 hits, do NOT allow verification to occur (rate limit)
		if ipHitCount > 5
			$stderr.puts 'lError when trying to verify jid ' +
				CGI.escapeHTML(jid)
			@error_text = 'There have been too many JMP signups '\
				'from your location today.  Please try again '\
				'tomorrow, or <a href="../#support">contact us'\
				'</a> to register an account manually.'
			conn.disconnect
			return erb :error
		end


		# buy the number
		uri = URI.parse('https://api.catapult.inetwork.com')
		http = Net::HTTP.new(uri.host, uri.port)
		http.use_ssl = true
		request = Net::HTTP::Post.new('/v1/users/' + $user +
			'/phoneNumbers')
		request.basic_auth $tuser, $token
		request.add_field('Content-Type', 'application/json')
		request.body = JSON.dump(
			'number'		=> params['number']
		)
		response = http.request(request)

		$stderr.puts 'bAPI response: ' + response.to_s + ' with code ' +
			response.code + ', body "' + response.body + '"'

		num_was_bought = false
		if response.code == '201'
			num_was_bought = true

			# TODO: check params['number'] works before using it
		end


		# check param['number'] to see if already in use by a JMP user
		if not num_was_bought
			uri = URI.parse('https://api.catapult.inetwork.com')
			http = Net::HTTP.new(uri.host, uri.port)
			http.use_ssl = true
			request = Net::HTTP::Get.new('/v1/users/' + $user +
				'/phoneNumbers/' +
				WEBrick::HTTPUtils.escape(params['number']) )
			request.basic_auth $tuser, $token
			response = http.request(request)
		end

		# control flow could be prettier but hacked for less indentation
		if not num_was_bought and response.code == '200'
			pnParams = JSON.parse(response.body)

			if pnParams['applicationId'] == $catapult_application_id
				$stderr.puts 'pError when trying to buy ' +
					params['number']
				@error_text = 'The JMP number you chose (' +
					params['number'] + ') is not available'\
					' anymore.  Please <a href="../">start'\
					' again</a>.'
				conn.disconnect
				return erb :error
			end

			# TODO: add locking of number - race exists until ID set
		elsif not num_was_bought  # and response.code != '200'
			# either couldn't get number info or number DNE; in both
			#  cases we should error out and not let user set an app

			$stderr.puts 'bError when trying to buy ' +
				params['number']
			@error_text = 'The JMP number you selected (' +
				params['number'] + ') is no longer available. '\
				' Please <a href="../">start again</a>.'
			conn.disconnect
			return erb :error
		end

		# ASSERT: had # already and its app != Catapult or # just bought


		# set the start day so we now how far back usage stats are valid
		conn.write ["SET", 'usage_start_day-' + params['number'],
			Date.today.strftime('%Y%m%d')]
		conn.read  # TODO: check value to confirm it worked


		# set param['number'] to use JMP application
		uri = URI.parse('https://api.catapult.inetwork.com')
		http = Net::HTTP.new(uri.host, uri.port)
		http.use_ssl = true
		request = Net::HTTP::Post.new('/v1/users/' + $user +
			'/phoneNumbers/' +
			WEBrick::HTTPUtils.escape(params['number']) )
		request.basic_auth $tuser, $token
		request.add_field('Content-Type', 'application/json')
		request.body = JSON.dump(
			'applicationId'		=> $catapult_application_id
		)
		response = http.request(request)

		$stderr.puts 'aAPI response: ' + response.to_s + ' with code ' +
			response.code + ', body "' + response.body + '"'

		if response.code != '200' and num_was_bought
			# TODO: unlikely, but "contact support"
			$stderr.puts "aError when trying to set application"
		elsif response.code != '200'  # and not num_was_bought
			# very unlikely (since above applicationId GET worked)
			$stderr.puts 'dError when trying to buy ' +
				params['number']
			@error_text = 'The JMP number you selected (' +
				params['number'] + ') is not available'\
				' anymore.  Please <a href="../">start'\
				' again</a>.'
			conn.disconnect
			return erb :error
		end


		# now that number setup the below can't result in getting wedged
		conn.write ["RPUSH", credKey, $user]
		conn.write ["RPUSH", credKey, $tuser]
		conn.write ["RPUSH", credKey, $token]
		conn.write ["RPUSH", credKey, params['number'] ]

		(1..4).each do |n|
			# TODO: catch/relay RuntimeError
			result = conn.read
			if result != n
				# TODO: add "contact support"
				$stderr.puts "rError when checking RPUSH retval"
				@error_text = 'An error occurred registering '\
					'this JID into the system.  Please '\
					'contact support about this issue.'
				conn.disconnect
				return erb :error
			end
		end

		conn.write ["SET", 'catapult_jid-' + params['number'], cheo_jid]
		conn.read  # TODO: check value to confirm it worked


		# now that JID is verified, register it with Cheogram
		$q_send.push(jid)

		begin
			status = Timeout::timeout(5) {
				# TODO: return val (added/removed) was expected?
				$q_done.pop
			}
		rescue Timeout::Error
			# TODO: ensure user's creds deleted and add support link
			$stderr.puts "tError when waiting for Cheogram register"
			@error_text = 'Timeout while attempting to register '\
				'JID; please contact support or feel free to '\
				'<a href="../">start again</a>.'
			conn.disconnect
			return erb :error
		end


		# Catapult supports alphanum and [-!=_*+.~]; tilde is escape sym
		@sip_user = URI.escape(jid, /[^0-9a-zA-Z!\-=_*+.]/).
			gsub('%', '~')

		# create password with https://github.com/singpolyma/mnemonicode
		stdin, stdout, stderr = Open3.popen3('./mnencode')
		# note that Catapult only allows passwords up to 25 chars so...
		stdin.print(SecureRandom.random_bytes(4))
		stdin.close
		@sip_pass = stdout.gets.strip

		# create the SIP endpoint and tell the user about it
		uri = URI.parse('https://api.catapult.inetwork.com')
		http = Net::HTTP.new(uri.host, uri.port)
		http.use_ssl = true
		request = Net::HTTP::Post.new('/v1/users/' + $user +
			'/domains/' + $catapult_domain_id + '/endpoints')
		request.basic_auth $tuser, $token
		request.add_field('Content-Type', 'application/json')
		request.body = JSON.dump(
			'name'		=> @sip_user,
			'applicationId'	=> $catapult_application_id,
			'credentials'	=> {'password' => @sip_pass}
		)
		response = http.request(request)

		$stderr.puts 'eAPI response: ' + response.to_s + ' with code ' +
			response.code + ', body "' + response.body + '"'

		if response.code != '201'
			$stderr.puts 'eError when trying to add SIP endpoint ' +
				@sip_user
			@error_text = 'Error creating SIP endpoint.  Please '\
				'<a href="../#support">contact support</a>.'
			conn.disconnect
			return erb :error
		end

		# TODO: bad indentation, but it's really really convenient
		conn.write ["SET", 'catapult_fwd-' + params['number'],
		  "sip:#{@sip_user}@#{$catapult_domain_prefix}.bwapp.bwsip.io"]
		conn.read  # TODO: check value to confirm it worked


		# let register5 know about validated JID and bought JMP number
		conn.write ["SETEX", 'reg-jid_good-' + params['sid'],
			$key_ttl_seconds, cheo_jid]
		conn.read  # TODO: check value to confirm it worked

		conn.write ["SETEX", 'reg-num_vjmp-' + params['sid'],
			$key_ttl_seconds, params['number'] ]
		conn.read  # TODO: check value to confirm it worked


		@jid = CGI.escapeHTML(jid)
		@number = params['number']
		@prefix = $catapult_domain_prefix

		EM.stop

		conn.disconnect
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
