<?php
# $request has been passed through from the load_view fn that loaded this page
# $request contains the $_REQUEST array.
#	["display"]=>
#	["view"]=>
#	["requesttype"]=>
#	["extdisplay"]=>
# and possibly other values.
# Break up $request array into individual fields
	extract($request);
	if(!isset($requesttype)) $requesttype = '';
# --------
# Are we returning from the Settings form?
	if($requesttype == "settings" && $action == "updated") {
		$ok = FreePBX::Wakeupalarms()->updateAlarmConfig($request);
		if($ok)	echo "<script>javascript:alert('Details update OK');</script>";
	  # Reload the Settings form - either after error or to display changes
		$requesttype = 'settings';
		goto endall;
	}
# --------
# Are we returning from the Info form?
	if($requesttype == "info") goto endall;
# --------
# Do other things
  # Save record ID if it is present
	if(!empty($extdisplay)) {
		$recordKey = $extdisplay;
		$idType = substr($recordKey,0,1);	# 1st character of key
		$id = substr($recordKey,1);			# The rest of the key
	}
# --------
# EDIT
  # If we have a record Key then we are in Edit mode
	if (!empty($recordKey)) {
	  # If 1st character of key field is D then we have a db key else it is a call file id
		if($idType == 'D') {
		  # Get data record
			$sql = "SELECT time, ext, per, suspend FROM wakeupalarms_calls WHERE id_schedule = $id";
			$dbh = \FreePBX::Database();
			try {$record = $dbh->query($sql)->fetch(\PDO::FETCH_ASSOC);}
			catch (\PDOException $e) {\out($e->getMessage()); return;}
		  # Populate screen fields with the existing values
			$ext = $record['ext'];
			$per = $record['per'];
			$suspend = $record['suspend'];
		  # Split date+time
			$pieces = explode(" ", $record['time']);
			$day = $pieces[0];
			$time = $pieces[1];
		  # Set flag as to which form to load
			$recordtype = 'D';
		  # We need to populate screen fields with the existing details.
			$account = $recordKey;		# Record key (id_schedule)
		}
		else {
		  # The recordKey key supplied is for an Asterisk call file and not a database record.
		  # Get file change-time (as Unix time) and split it into date and time
		  # (Now we only allow change of date and time for such a file.)
			$unixTime = \FreePBX::Wakeupalarms()->getTimeFromCallFile($id);
			if($unixTime !== false) {
				$pieces = explode(" ", $unixTime);
			  # Populate screen field with the existing value
				$day = $pieces[0];
				$time = $pieces[1];
			  # Set flag as to which form to load
				$recordtype = 'C';
			}
		}
	}
# --------
	else {
# ADD NEW
	  # Record key not supplied - mode is Add New
		$recordtype = 'D';
	  # Default 'suspend' to No
		$suspend = '0';
	}
# --------
# BOTH
# Build Extension Select list
	$results = core_users_list();
	$extnlist = '';
	foreach($results as $result){$extnlist .= "<option value='".$result[0]."'>".$result[0]." (".$result[1].")</option>\n";}
#============================================================================
endall:
?>
<!-- <form class="popover-form fpbx-submit" name="editGRP" action="" method="post" onsubmit="return checkGRP(editGRP);" data-fpbx-delete="config.php?display=wakeupalarms&action=delAlarm&account=<?php echo $account ?>"> --> 
<form class="popover-form fpbx-submit" name="editAlarm" action="" method="post" onsubmit="" data-fpbx-delete="config.php?display=wakeupalarms&action=delAlarm&account=<?php echo $account ?>">
	<a href="config.php?display=wakeupalarms" class="btn"><?php echo _("Go Back to Main List") ?></a>
	<?php
		if(LD_DEBUG) {
			echo '<a href="config.php?display=wakeupalarms&view=form&requesttype=genalldue" class="btn">';
			echo _("Temp: Run GenAllDue").'</a>';
			echo '<a href="config.php?display=wakeupalarms&view=form&requesttype=install" class="btn">';
			echo _("Temp: Run Install").'</a>';
		}
	 ?>
	<input type="hidden" name="display" value="wakeupalarms">
	<input type="hidden" name="view" value="form">
	<?php
		if($recordKey) {
		  # $recordKey is the unique key to the record being edited (or deleted).
		  # If specified then we are in 'Edit' mode else in 'Add' mode --> 
			 echo '<input type="hidden" name="action" value="EditAlarm">';
			 echo '<input type="hidden" name="account" id="account" value="'.$recordKey.'">';
		}
		else echo '<input type="hidden" name="action" value="AddAlarm">';
	  # Load next form - either for Settings edit or to Add or Edit an alarm
		if(!isset($requesttype)) $requesttype = '';
		if(!isset($recordtype)) $recordtype = '';
		if($requesttype == 'settings') include(__DIR__."/settings_form.php");
		elseif($requesttype == 'info') include(__DIR__."/info_form.php");
		elseif($requesttype == 'genalldue') include(dirname(__DIR__)."/agi-bin/wakeupalarms_genalldue.php");
		elseif($requesttype == 'install') include(dirname(__DIR__)."/install.php");
		elseif($requesttype == 'grid') include(__DIR__."/grid.php");
		elseif($recordtype == 'D') include(__DIR__."/db_alarm_form.php");
		elseif($recordtype == 'C') include(__DIR__."/callfile_form.php");
	 ?>
</form>
