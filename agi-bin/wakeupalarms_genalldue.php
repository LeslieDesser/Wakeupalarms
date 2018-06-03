#!/usr/bin/php
<?php
# Script is invoked by CRON job to generate 'soon' due alarms in the database and create callfile files.
# See install.php for cron job details.

//include bootstrap
$bootstrap_settings['freepbx_auth'] = false;
	if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) 
		include_once('/etc/asterisk/freepbx.conf');
# Do the job!
		FreePBX::Wakeupalarms()->genAllDueAlarms();
?>