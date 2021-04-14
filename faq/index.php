<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>JMP: Frequently Asked Questions</title>

		<link rel="stylesheet" type="text/css" href="../style.css" />
		<style type="text/css">
			body > section {
				counter-increment: heading;
			}

			body > section h1:before {
				content: "Q"counter(heading)". ";
			}
		</style>
	</head>

	<body>
		<h1><img src="../static/jmp_beta.png" alt="JMP" /></h1>
		<h1>Frequently Asked Questions</h1>

		<section id="features">
			<h1>What do I get when I signup for JMP?</h1>

			<p>JMP gives you a Canadian or US phone number that is yours to keep (for <a href="https://soprani.ca/vonage/prices_by_country.html">46 other countries</a> you can use the <a href="https://wiki.soprani.ca/VonageSetup">the Vonage SGX</a>, also part of <a href="https://soprani.ca/">Soprani.ca</a>).  JMP allows you to send and receive text messages and picture messages using your <a href="#jabber_client">Jabber client</a>, with calls <a href="#voicemail"> delivered to voicemail</a> (including a text transcription) by default.</p>
			<p>If you like, you can <a href="#calling">make and receive phone calls</a> using your <a href="https://arstechnica.com/business/2010/01/voip-in-depth-an-introduction-to-the-sip-protocol-part-1/">SIP</a> client (or receive with an existing Canadian or US phone number or directly in a supporting Jabber client).  You can <a href="#voicemail">configure a voicemail timeout</a> if you'd like JMP to automatically handle any calls that ring for too long.</p>
		</section>

		<section id="jabber">
			<h1>What's this Jabber thing?</h1>
			<p>XMPP (formerly Jabber) is a <a href="https://en.wikipedia.org/wiki/Federation_%28information_technology%29">federated protocol</a> and <a href="https://singpolyma.net/2009/01/beasts-of-the-standards-world/">open standard</a> for messaging.  It uses Jabber IDs (JIDs) to communicate, which are similar to email addresses.  As with email, you can get a Jabber ID from one of <a href="../suggested_servers.html">many free and open servers</a>.</p>

			<p id="jabber_client">And just like email, there are many different <a href="https://xmpp.org/software/clients.html">Jabber clients</a> available, so you can use Jabber from your phone (<a href="https://conversations.im/">Conversations</a> and <a href="https://siskin.im/">Siskin IM</a> are our recommended apps), <a href="https://mov.im">the web</a>, and <a href="https://gajim.org/">your computer</a>.</p>
			<p>Jabber is <a href="https://xmpp.org/about/history.html">long-standing</a>, widely-used, and privacy-focused.  If you have ever used <a href="https://developers.google.com/talk/open_communications">Google Chat</a>, HipChat, the pre-2016 Facebook Messenger, WhatsApp, Kik, <a href="https://movim.eu">Movim</a>, <a href="https://blog.process-one.net/google-cloud-messaging-update-boosted-by-xmpp/">Android Push Notifications</a>, or a private company chat server, then you have used <a href="https://xmpp.org/">XMPP</a>.</p>
			<p>JMP extends the freedom of Jabber and the XMPP network to cell phone texting.</p>
		</section>

		<section id="sending">
			<h1>How do I send text messages?</h1>

			<p>Text messages are sent and received using special Jabber IDs (JIDs).  To send a text message, first add the JID representing the destination phone number to your roster.  For example, to send a text message to +1 416 993 8000 you would add "+14169938000@cheogram.com" to your roster.  You can then send the contact a message or picture and they will receive it as an SMS/MMS message.</p>
			<p>To send text messages to short codes, use the special suffix for short codes, ie. "33733;phone-context=ca-us.phone-context.soprani.ca@cheogram.com" represents the 33733 short code.  This will be simplified in the future, but is required for now in order to maintain proper uniqueness going forward.</p>
		</section>


		<section id="pricing">
			<h1>How much does JMP cost?</h1>

			<p>JMP is available for the prices below, either at signup time or after a trial period of up to 30 days.  A trial period can be requested during the signup process and is granted at JMP's discretion.  During a trial period you can send up to 300 text or pictures messages and use up to 30 minutes of voice calls.  And you can receive as many text and picture messages as you like.  To see how many minutes and messages you've used, follow <a href="#usage">these instructions</a>.</p>
			<p>At any time within the first 30 days of a trial period you can <a href="../upgrade1/">upgrade to a paid account</a> (or pay for JMP at signup time)  to send and receive unlimited text and picture messages, as well as use up to 120 minutes of voice calls per month.  JMP is currently in beta - the introductory rate for beta users is US$2.99 per month (or US$34.99 per year).</p>
			<p>Once the beta period for JMP is over, the US$2.99/month and US$34.99/year subscriptions will still be available and will still have unlimited incoming SMS and MMS, but the number of outgoing SMS/MMS will be limited; other plans will be available.</p>
			<p>You may cancel your subscription at any time (via PayPal or by <a href="#support">contacting the support team</a>). After cancellation (or after your trial period expires), your number will be reclaimed after 30 days unless you <a href="https://www.fcc.gov/consumers/guides/porting-keeping-your-phone-number-when-you-change-providers">port</a> it to another carrier.</p>
			<p>The beta period will last until at least July 2021, and a new unlimited messaging plan will be made available once the beta period has ended.  After the beta period, additional <a href="#payment">non-PayPal methods of payment</a> will be made available.</p>
		</section>

		<section id="support">
			<h1>How do I get help with JMP?</h1>

			<p>The best venue for help using or developing features for JMP is the Soprani.ca chatroom, which you can join <a href="xmpp:discuss@conference.soprani.ca?join">from your chat client</a> or <a href="https://anonymous.cheogram.com/discuss@conference.soprani.ca">on the web</a>.  It is an active and enthusiastic channel, and the fastest way to solve problems with the developers or other users.</p>
			<p>If you'd like to send a private inquiry instead, or if you cannot join the chatroom, send a text message to the support team at <a href="sms:+14169938000">+1 416 993 8000</a> (Canada) or <a href="sms:+13127968000">+1 312 796 8000</a> (US) and we will get back to you within 8 business hours.  Both numbers can be texted from most other countries, though your carrier may charge a fee for international text messaging.</p>
			<p>If you have found a bug in JMP, please file an issue at <a href="https://gitlab.com/ossguy/sgx-catapult/issues">https://gitlab.com/ossguy/sgx-catapult/issues</a>.</p>
		</section>

		<section id="about">
			<h1>How do I learn more about JMP?</h1>

			<p>All of the software that makes up JMP is <a href="https://en.wikipedia.org/wiki/Free_and_open-source_software">free and open source</a> software.  You can view, download, and modify the source code <a href="https://soprani.ca">here</a>.</p>
			<p>For news about JMP, you can signup for our low-volume notification list <a href="https://soprani.ca/cgi-bin/mailman/listinfo/jmp-news">here</a>. You can also <a href="https://twitter.com/JMP_chat">follow us on Twitter</a>.</p>
		</section>

		<section id="sms_features">
			<h1>Which text messaging features are supported?</h1>

			<p>JMP supports the following text messaging features:</p>
			<ul>
				<li>Send and receive text and picture messages (SMS and MMS) between your JMP number and any other Canadian or US phone number, as well as most other countries in the world (the latter is currently in alpha).</li>
				<li>Send and receive text and picture messages with most Canadian and US short codes.  For example, you can send "info" to 33733 and receive the reply.</li>
				<li>Use any emoji or other Unicode characters in your text messages.</li>
				<li>Delivery receipts, as indicated by the carrier.  To receive these for a given contact, they must be in your contact list (roster).</li>
				<li>Send and receive group text messages (to send group texts, please <a href="#support">contact support</a> to enable for your account).</li>
			</ul>

			<p>JMP does not (yet) support these features:</p>
			<ul>
				<li><a href="https://en.wikipedia.org/wiki/Rich_Communication_Services">RCS</a>, which allows for video calls over the phone network.</li>
			</ul>
			</section>

		<section id="calling">
			<h1>How do I make a phone call with my JMP number?</h1>

			<p>Currently JMP supports calling to Canada and the US.</p>
			<p>The easiest way is to make a call from your Jabber client, if you are using a supporting client such as Conversations, Siskin, Movim, or Gajim.  Simply add a contact just as you would for <a href="#sending">messaging</a> and then select the voice call option in your client.</a>
			<p>Another way to make calls is to login to the SIP account that was provided during the signup process using a SIP client (we recommend <a href="https://f-droid.org/archive/com.csipsimple_2459.apk" >CSipSimple</a> for Android and <a href="https://itunes.apple.com/us/app/linphone/id360065638">Linphone</a> for iOS).  Then enter the 10-digit phone number that you'd like to call.</p>
			<p>If you don't have your SIP account information, you can reset your SIP account and receive the new password and other details by communicating with <a href="xmpp:cheogram.com">cheogram.com</a>, which should already be in your contacts.  In some clients you can find "Execute command" or "Actions" in the menus and select "Reset SIP Account" from there.  Otherwise send the message "help" and the bot will reply with a list of options.</p>
		</section>

		<section id="voicemail">
			<h1>How does voicemail work?</h1>

			<p>Calls will be delivered to voicemail if your SIP account (or forwarding number) is busy or not logged in.  Voicemails will be sent as messages to your XMPP client, both as an audio file as soon as the voicemail is left, and also as transcribed text once our transcription engine has finished converting the audio to text for you (normally this takes just a few seconds).</p>
			<p>If SIP or forwarding is setup, you can configure calls to be sent to voicemail after a certain number of rings by using an XMPP ad-hoc command, which is done (ie. on a computer, using your JID in <a href="https://mov.im">Movim</a>) by clicking on cheogram.com in your contacts.  Under Actions, you can then choose to either "Configure Calls" (set the seconds until voicemail - a ring is about 5 seconds and "-1" means unlimited rings) or "Record Voicemail Greeting" (see below).</p>
			<p>The default voicemail greeting is: "You have reached the voicemail of a user of <a href="https://jmp.chat/">JMP.chat</a>.  Please send a text message, or leave a message after the tone."  If your XMPP user <a href="https://xmpp.org/extensions/xep-0054.html">has a vCard</a> with FN or NICKNAME specified, then JMP will use that instead of "a user of <a href="https://jmp.chat/">JMP.chat</a>" in your voicemail greeting.  You can also set your own voicemail greeting using the "Record Voicemail Greeting" ad-hoc command (see above), which will call your SIP account (or forwarding number) to record the greeting.</p>
		</section>

		<!-- FIXME: merge into #pricing? -->
		<section id="payment">
			<h1>Do I have to use PayPal to signup for JMP?</h1>

			<p>No.  Other payment methods are available.  For details, please contact our support team by sending a text message to <a href="sms:+14169938000">+1 416 993 8000</a> (Canada) or <a href="sms:+13127968000">+1 312 796 8000</a> (US) or send a private message to a moderator/owner in the JMP/Soprani.ca chatroom at <a href="xmpp:discuss@conference.soprani.ca?join">discuss@conference.soprani.ca</a> (just ask in the room if it's unclear who the moderators are).</p>
		</section>

		<section id="existing">
			<h1>Can I port my existing number into JMP?</h1>

			<p>Yes!  We support most regions in Canada and the US.  The process normally takes 1-2 weeks, depending on the number being ported in.  You can <a href="/sp1a/porting1/">submit your port request here</a>.  For questions, please reach our support team by sending a text message to <a href="sms:+14169938000">+1 416 993 8000</a> (Canada) or <a href="sms:+13127968000">+1 312 796 8000</a> (US) or send a private message to a moderator/owner in the JMP/Soprani.ca chatroom at <a href="xmpp:discuss@conference.soprani.ca?join">discuss@conference.soprani.ca</a> (just ask in the room if it's unclear who the moderators are).</p>
		</section>

		<section id="clients">
			<h1>Which XMPP clients are supported?</h1>

			<p>You can use any XMPP client you like.  If a client doesn't work for some reason, please <a href="https://gitlab.com/ossguy/sgx-catapult/issues">file a ticket</a> or <a href="#support">discuss it with the team</a>.  JMP is normally tested with <a href="https://gajim.org/">Gajim</a> and <a href="https://conversations.im/">Conversations</a>, since they both support the needed XEPs for JMP's complete feature set (which includes XEP-0184, XEP-0234, and XEP-0261, among others).</p>
			<p>The XMPP clients mentioned above are known to work correctly with JMP.  If you choose to use a different XMPP client, it may not receive messages from phone numbers that are new to you by default.  Please see <a href="#blocking">the section on message blocking</a> for more details.</p>
		</section>

		<section id="servers">
			<h1>Which XMPP servers are supported?</h1>

			<p>You can use any federated XMPP server, though we specifically recommend those on <a href="../suggested_servers.html">our suggested servers list</a> since we have confirmed that those servers generally support the features that JMP requires.</p>
			<p>The XMPP servers mentioned in <a href="../suggested_servers.html">our suggested servers list</a> are known to work correctly with JMP.  If you choose to use a different XMPP server, it may not receive messages from phone numbers that are new to you by default.  Please see <a href="#blocking">the section on message blocking</a> for more details.</p>
		</section>

		<section id="blocking">
			<h1>Why might I not be receiving certain messages?</h1>

			<p>If you are not using one of the <a href="../suggested_servers.html">suggested servers</a> or clients listed above, then it is possible your client or server silently blocks message from numbers/contacts not in your contact list (roster). We have most often seen this problem with servers (rather than clients), so check with your server operator first if you are not receiving text messages from phone numbers you haven't <a href="#sending">added to your contact list</a> yet.</p>
			<p>If that does not resolve the problem, please feel free to <a href="https://gitlab.com/ossguy/sgx-catapult/issues">file a ticket</a> or <a href="#support">discuss it with the team</a> (we can help determine where the issue might be and, if you like, switch your JMP number to a different Jabber ID if want to switch XMPP servers).  Be sure to note which XMPP client you are using, and ideally which server as well.  We want to make sure that JMP works with as many XMPP clients and servers as possible!</p>
		</section>

		<section id="usage">
			<h1>How many minutes and messages have I used this month?</h1>

			<p>You can see how many minutes and outgoing messages you've used for each of the past several days or months, including a total, by sending 'u' to <a href="xmpp:cheogram.com">cheogram.com</a> which should already be in your contacts.</p>
			<p>Note that usage is reported for <a href="https://en.wikipedia.org/wiki/Gregorian_calendar">Gregorian calendar</a> days using <a href="https://en.wikipedia.org/wiki/International_Atomic_Time">International Atomic Time (TAI)</a>.</p>
			<p>Since JMP does not charge for nor count incoming message usage, only minutes and outgoing message usage are shown.</p>
		</section>

		<section id="bot">
			<h1>How do I see my JMP number and change other settings?</h1>

			<p><a href="xmpp:cheogram.com">cheogram.com</a> should already be in your contacts, and can be used to view and change a number of settings for your JMP account.  You can find a comple list of options by typing "help" in a new conversation with the bot, or get a wizard in supporting clients by looking for an "Execute Command" or "Actions" option.</p>
			<p>If you do not have cheogram.com in your contacts and try to add it, it is just "cheogram.com" with no "@". Some clients will give a warning about adding such an address, please press "Add Anyway".</p>
		</section>

		<?php require dirname(__FILE__).'/../nav.php'; ?>
	</body>
</html>
