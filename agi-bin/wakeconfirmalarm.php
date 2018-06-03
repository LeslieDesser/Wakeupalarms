#!/usr/bin/env php
<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
# Called by Asterisk when an alarm callfile is executed.

// Bootstrap FreePBX but don't include any modules (so you won't get anything
// from the functions.inc.php files of all the modules.)
//
	$restrict_mods = true;
	include '/etc/freepbx.conf';
	set_time_limit(0);
	error_reporting(0);

// Connect to AGI:
//
	require_once "phpagi.php";
	$AGI = new AGI();
	$AGI->answer();
	$lang = $AGI->request['agi_language'];
# Destination number
	$number = $AGI->request['agi_extension'];
# Save time alarm went off as default snooze start time.
	$time_wakeup = time();
# Present the options to the caller and process response
# If the called function returns then there was a problem or an invalid entry
# as a valid entry results in an action and then always followed by Hangup.
# So just repeat a few times.
	for ($i = 1; $i <= 3; $i++) {wakeupMenu($AGI, $number, $lang, $time_wakeup);}
# Still a problem after a few tries
# Maybe the person has dropped off to sleep after picking up
# Snooze for 5 minutes as the default.
# An alternative could be to just hang up but probably we should still try and do the wake up job.
	$time_wakeup += 300;
	FreePBX::Wakeupalarms()->addWakeup($number,$time_wakeup,$lang);
	sim_playback($AGI, "rqsted-wakeup-for&digits/5&minutes&vm-from&now");
	$AGI->hangup();
#============================================================================
/**
 * The WakeUp Administration Menu
 * @param  object $AGI    AGI Object
 * @param  string $number The "number" to work with
 */
function wakeupMenu($AGI, $number, $lang, $time_wakeup) {
# In earlier versions there was a reference to language 'ja' for which slightly different wording was used.
# This has been removed.  Why does Japanese need different wording?
	sim_playback($AGI, "hello&this-is-yr-wakeup-call");
# Play the menu. Valid options are single digits 1, 2, 3 or 4
# Return response in $digit
	$wtext = "to-cancel-wakeup&press-1&to-snooze-for&digits/5&minutes&press-2&to-snooze-for&digits/10&minutes&press-3&to-snooze-for&digits/15&minutes&press-4";
	$digit = sim_background($AGI, $wtext,"1234",1);
# Test the responses
	switch($digit) {
		case 1:
		  # Asterisk will automatically delete the current alarm. Nothing to do.
			sim_playback($AGI, "wakeup-call-cancelled");
			sim_playback($AGI, "goodbye");
			$AGI->hangup();
		break;
		case 2:
		  # Snooze 5 mins.
			$time_wakeup += 300;
		  # Create a new alarm callfile
			FreePBX::Wakeupalarms()->addWakeup($number,$time_wakeup,$lang);
			sim_playback($AGI, "rqsted-wakeup-for&digits/5&minutes&vm-from&now");
			sim_playback($AGI, "goodbye");
			$AGI->hangup();
		break;
		case 3:
		  # 10 mins
			$time_wakeup += 600;
			FreePBX::Wakeupalarms()->addWakeup($number,$time_wakeup,$lang);
			sim_playback($AGI, "rqsted-wakeup-for&digits/10&minutes&vm-from&now");
			sim_playback($AGI, "goodbye");
			$AGI->hangup();
		break;
		case 4:
		  # 15 mins
			$time_wakeup += 900;
			FreePBX::Wakeupalarms()->addWakeup($number,$time_wakeup,$lang);
			sim_playback($AGI, "rqsted-wakeup-for&digits/15&minutes&vm-from&now");
			sim_playback($AGI, "goodbye");
			$AGI->hangup();
		break;
	}
}
#============================================================================
# Play back the message string stored in $file
function sim_playback($AGI, $file) {
	$files = explode('&',$file);
	foreach($files as $f) {$AGI->stream_file($f);}
}
#============================================================================
/**
 * Simulate background playback with added functionality
 * @param  object  $AGI      The AGI Object
 * @param  string  $file     Audio files combined by/with '&'
 * @param  string  $digits   Allowed digits (if we are prompting for them)
 * @param  string  $length   Length of allowed digits (if we are prompting for them)
 * @param  string  $escape   Escape character to exit
 * @param  integer $timeout  Timeout
 * @param  integer $maxLoops Max timeout loops
 * @param  integer $loops    Total loops
 */
function sim_background($AGI, $file,$digits='',$length='1',$escape='#',$timeout=15000, $maxLoops=1, $loops=0) {
# Split the string containing file names separated by &
	$files = explode('&',$file);
	$response = '';
	$lang = $AGI->request['agi_language'];
# Play back all the files one after the other and get response into $response
	foreach($files as $f) {
		$ret = $AGI->stream_file($f,$digits);
		if($ret['code'] == 200 && $ret['result'] != 0) $response .= chr($ret['result']);
	  # Exit loop if we already have allowed number of digits
		if(strlen($response) >= $length) break;
	}
# Inspect the response in $response
# If it is too short then wait for more digits till max length is reached.
# (Not tested. Works in our case where we have only a single digit to enter.)
	if(trim($digits) != '' && strlen($response) < $length) {
		while(strlen($response) < $length && $loops < $maxLoops) {
			$ret = $AGI->wait_for_digit($timeout);
			if($loops > 0) sim_playback($AGI, "please-try-again");
			if($ret['code'] == 200 && $ret['result'] == 0) sim_playback($AGI, "you-entered&bad&digits");
			elseif($ret['code'] == 200) {
				$digit = chr($ret['result']);
				if($digit == $escape) break;
				if(strpos($digits,$digit) !== false) {
					$response .= $digit;
					continue; //don't count loops as we are good
				}
				else sim_playback($AGI,"you-entered&bad&digits");
			} else sim_playback($AGI,"an-error-has-occurred");
			$loops++;
		}
	}
	$response = trim($response);
	return $response;
}
