<?php
	include_once 'constants.inc.php';
	out(_("Wakeupalarms is being uninstalled.<br>"));
	$dbh = \FreePBX::Database();
# drop the hotelwakup table
	$sql = "DROP TABLE IF EXISTS wakeupalarms";
	try {$check = $dbh->query($sql);}
	catch (\PDOException $e) {\out($e->getMessage());}
# drop the wakeupalarms_calls table
	$sql = "DROP TABLE IF EXISTS wakeupalarms_calls";
	try {$check = $dbh->query($sql);}
	catch (\PDOException $e) {\out($e->getMessage());}

# Delete the cron job associated with this application
	wuc_delete_cron();

# Consider adding code here to scan thru the spool/asterisk/outgoing directory and removing 
# already wakeup calls that have been scheduled.
# On the other hand the user can delete them before uninstalling.
# No serious harm will come if they are left.

#=============================================================================== 
function wuc_delete_cron() {
# Remove cron job previously added by the install script.
	$temp_file = sys_get_temp_dir()."/wuc_install";
# Delete temp work file in case it already exists
	if (file_exists($temp_file)) unlink($temp_file);
	$foo = shell_exec('crontab -l');

	$pos = strpos($foo, K_CRON_ID); 
#?? There is a bug here: It replaces all occurrences of the given string but leaves the final newline
#?? so a blank line is left each time install is run
#?? See similar note in install.php
	if ($pos) {
		$regex = "~.*?".K_CRON_ID."~";
		$foo = preg_replace($regex ,"",$foo);
	  # Update cron file after removing our job
		file_put_contents($temp_file, $foo);
		echo exec('crontab '.$temp_file);
	}
}