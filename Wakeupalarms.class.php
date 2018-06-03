<?php
namespace FreePBX\modules;
include_once 'constants.inc.php';
class Wakeupalarms implements \BMO {
	public function __construct($freepbx = null) {
		if ($freepbx == null) throw new Exception("Not given a FreePBX Object");
		$this->FreePBX = $freepbx;
		$this->db = $freepbx->Database;
		$this->astman = $this->FreePBX->astman;
	}
#--------------------------------------------------------------
	public function install() {}
	public function uninstall() {}
	public function backup() {}
	public function restore($backup) {}
#--------------------------------------------------------------
#-----------------------------------------------------------
	public function writelog($log) {
		$x=file_put_contents(K_LOG_FILE, date('Y-m-d H:i:s')." UT: ".$log."\n", FILE_APPEND);
	}
#--------------------------------------------------------------
# Debug tool: route var_dump output to debug log
# Example of use: vardumptolog('xx $_REQUEST=',$_REQUEST);
	public function vardumptolog($txt,$var) {
	  # Turn on output-buffering
		ob_start();
	  # Dump to buffer
		var_dump($var);
	  # Get dumped string
		$result =  ob_get_contents();
		ob_end_clean();
		$this->writelog($txt."=".$result);
	}
#--------------------------------------------------------------
# Called when Submit clicked (how called?)
public function doConfigPageInit($page) {
	$request = $_REQUEST;
	$action = (isset($request['action']) ? $request['action'] : '');
	$extdisplay = (isset($request['extdisplay']) ? $request['extdisplay'] : '');
# Save record ID if it is present
	if(!empty($extdisplay)) {
	  # 1st character of key - C or D dependent on key type
		$idType = substr($extdisplay,0,1);
	  # The actual ID part of the key
		$id = substr($extdisplay,1);			
	}
# $request['account'] is the Record ID if record is being edited or deleted
# It comes from a hidden field on the form and it is basically equivalent to $recordKey
# In some circumstances $request['extdisplay'] is empty but $request['account'] is set instead.
# Sometimes both are set.
# The code should really be investigated and cleaned up if necessary. (??)
	if(!empty($request['account'])) {
		$idType = substr($request['account'],0,1);
		$id = substr($request['account'],1);
	}

# Set Language: Default to English if empty
	if(empty($request['language'])) $request['language'] = 'en';
#-------
# ADD a new Alarm
	if ($action == 'AddAlarm') {
		$ok = $this->validateAlarmInput($request,'D');	# D = database input (more fields)
		if($ok === true) {
		  # Add the record. Record ID returned
			$newRecordKey = $this->dbAlarmAdd($request);
			if($newRecordKey === 'E') {
				echo "<script>javascript:alert('". _("Error: Failed to add record to database for unknown reason").".');</script>";
				return false;
			}
			elseif($newRecordKey === 'R') {} # R means just redisplay
			else {
			  # Record added.
			  # Run the generate-callfile function for this new record in case the alarm is due soon.
				$this->genAllDueAlarms($newRecordKey);
			  # Confirm that record has been added
				echo "<script>javascript:alert('". _("Alarm added OK - ID is ").$newRecordKey.".');</script>";
				# Most convenient to just leave the screen as is so more alarms can be added by the user
				# The 2 lines  below would open the new record in Edit mode.
				#$_REQUEST['extdisplay'] = $newRecordKey;	# Record key of added record
				#$_REQUEST['action'] = 'EditAlarm';
				return true;
			}
		}
	  # Validation failed. Error message has already been displayed inside fn validateAlarmInput
		else return false;
	}
#-------
# DELETE an existing Alarm.
	if($action == 'delAlarm' && !empty($id)) {
	  # If 1st character of key field is a 'D' then we have a db key else it is a callfile id
		if($idType == 'D') {
			$this->dbAlarmDel($id);
			unset($_REQUEST['view']);
			unset($_REQUEST['extdisplay']);
			return true;
		}
		else{
		  # We have an Asterisk callfile to delete, like:
		  #		/var/spool/asterisk/outgoing/wuc.1524790800.call
		  # $id contains the ID which is the 1524790800 in the above example
			$file = $this->callfileName($id);
			if(file_exists($file)) unlink($file);
		}
	}
#-------
# EDIT an existing alarm.
# If 1st character of key field is a 'D' then we have a db key (all fields editable) else it is a callfile id (limited fields editable)
	if ($action == 'EditAlarm' && !empty($id)) {
	  # Validate input
		$ok = $this->validateAlarmInput($request, $idType);
		if($ok) {
		  # Type D is database alarm
			if($idType == 'D') {
			  # Update the details
				$newRecordKey = $this->dbAlarmUpdate($request, $id);
			  # Redisplay the list
				$_REQUEST['view'] = 'grid';
				return true;
			}
			else{
			  # We have an Asterisk callfile to update
				$file = $this->callfileName($id);
				$this->setNewTime($id, strtotime($request['day']." ".$request['time']));
				$_REQUEST['view'] = 'grid';
				return true;
			}
		}
	  # Validation error
		else return false;
	}
}
#--------------------------------------------------------------
# Add a new alarm to the database
	public function dbAlarmAdd($request){
		$sql = "INSERT INTO wakeupalarms_calls (time,ext,per,id_cfg,lang,suspend) VALUES (:time, :ext, :per, :id_cfg, :lang, :suspend)";
		$vars = array(
					":time" => date('Y-m-d H:i:s',strtotime($request['day']." ".$request['time'])),
					":ext" => $request['ext'],
					":per" => $request['per'],
					":suspend" => $request['suspend'],
					":id_cfg" => K_WUC,
					":lang" => $request['language']
				);
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($vars);
		  # Return record ID of new record
			return $this->db->lastInsertId();
		}
		catch (\PDOException $e) {
		  # Duplicate key?
			if($e->getCode() === '23000') {
				echo "<script>javascript:alert('". _("Cannot add this Alarm as one already exists for this destination at this same time. Use the Edit option instead.").".');</script>";
			  # Redisplay form
				return 'R';
			}
		  # Unknown error. Display details followed by generic error message
			\out($e->getMessage());
			return 'E';
		}
}
#============================================================================
# Delete an alarm from database
	public function dbAlarmDel($recordID) {
		$sql="DELETE FROM wakeupalarms_calls WHERE id_schedule = '".$recordID."'";
		try {
			$sth = $this->db->prepare($sql);
			$sth->execute();
		}
		catch (\PDOException $e) {\out($e->getMessage());}
	}
#--------------------------------------------------------------
# Update a database alarm.
	public function dbAlarmUpdate($request, $id){
		$sql = "UPDATE wakeupalarms_calls SET time = :time, ext = :ext, per = :per, suspend = :suspend WHERE id_schedule = :id_schedule";
		$vars = array(
					":time" => date('Y-m-d H:i:s',strtotime($request['day']." ".$request['time'])),
					":ext" => $request['ext'],
					":per" => $request['per'],
					":suspend" => $request['suspend'],
					"id_schedule" => $id
				);
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute($vars);
		  # Check if, due to the changes, a callfile needs to be generated
			$this->genAllDueAlarms($id);
			return true;
		}
		catch (\PDOException $e) {\out($e->getMessage()); return false;}
}
#--------------------------------------------------------------
public function validateAlarmInput($request) {
# Validate all input fields
# Check Time & Date
# Date and time must be specified
	if(empty($request['day']) || empty($request['time'])) $errMsg .= '\n\n- Date and time must be specified';
# If date & time are present then check it is in the future
	if(!isset($errMsg)) {
		$time_wakeup = strtotime($request['day']." ".$request['time']);
		$time_now = time();
		if ($time_wakeup <= $time_now)
			$errMsg .= sprintf(_('\n\n- Cannot schedule the call as the scheduled time is in the past: \n Time now: %s \n Wakeup Time: %s'),
							   date(DATE_RFC2822,$time_now),date(DATE_RFC2822,$time_wakeup));
	}
# The following is only input if this is a database alarm
	if($request['extdisplay'] == 'D' || $request['requesttype'] == 'add') {
	  # Destination must be specified
		if(empty($request['ext'])) $errMsg .= '\n\n- Destination must be specified';
	  # Repeating 'per' must be specified
		if(empty($request['per'])) $errMsg .= '\n\n- "Repeating?" value must be selected';
	}
# Error?
	if(isset($errMsg)) {
	  # Drop leading newline characters (\n\n) and add prefix
		$errMsg = 'Errors!\n\n '.substr($errMsg,4);
		echo "<script>javascript:alert('".$errMsg."');</script>";
		return false;
	}
# All OK
	else return true;
}
#--------------------------------------------------------------
# Returns complete list of alarms.
# First list from database and then from callfile folder
	public function listAllWakeupalarms() {
	  # From database:
	  # Convert 'per' codes to text.
	  # The 'per' value in the db is a single character - any one of those in $codes.
	  # The matching positional text to be displayed in field 'per' is in array $perText
		$codes = 'XDWZ';
		$perText = ['Once-Only','Daily','Weekly','Once'];
	  # Get all db records into array
		$sql = "SELECT time, ext, per, id_cfg, id_schedule, suspend  FROM wakeupalarms_calls ORDER BY ext, time";
		try {$this->db->query($sql);
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$results = $stmt->fetchall(\PDO::FETCH_ASSOC);
		}
		catch (\PDOException $e) {\out($e->getMessage()); return array();}
	  # Save values from database as associative
	  # Record key is given a prefix 'D' (Database) to differentiate it from a callfile ID.
		foreach ($results as $result) {
			$alarms[] = array(
				'time' => date('Y/m/d H:i',strtotime($result['time'])),
				'ext' => $result['ext'],
				'per' => $perText[strpos($codes, $result['per'])],
				'id_cfg' => $result['id_cfg'],
				'id_schedule' => 'D'.$result['id_schedule'],
				'suspend' => ($result['suspend'] ? 'Yes' : ''),
			);
		}
	  # Now from Asterisk callfile folder:
	  # Sample file name:
	  #		/var/spool/asterisk/outgoing/wuc.152.call
	  # File name is made up of:
	  #	'wuc' = a constant to identify callfiles from this module
	  # '152 = unique serial number to create a unique file name
	  # 'call' = constant (asterisk file type)
	  # For such records 'per' and 'type' are set to '' and 'id_cfg' is the unix date/time concatenated with the destination 
	  # to allow rebuilding of the file name if 'delete' action is requested.
		foreach(glob($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/wuc*.call") as $file) {
		  # Get unix time (time), destination(ext) and  from file name
			$id = $this->CheckWakeUpProp($file);
			if(!empty($id)) {
				$alarms[] = array(
					"time" => date('Y-m-d H:i',filemtime($file)),
					"ext" => $this->getDestinationFromCallFile($id),
					"per" => $perText[strpos($codes, 'Z')],
					"id_schedule" => 'C'.$id
				);
			}
		}
		if (isset($alarms)) return $alarms;
		else return array();
	}
#--------------------------------------------------------------
# Extract time, destination and type code from file name
# Sample file name: /var/spool/asterisk/outgoing/wuc.127.call
	public function CheckWakeUpProp($file) {
	  # Drop path to get just file name
		$file = basename($file);
	  # Brake into into parts separated by '.'
		$WakeUpTmp = explode(".", $file);
		$myresult = null;
		if(!empty($WakeUpTmp[1])) $myresult = $WakeUpTmp[1];
		return $myresult;
	}
#============================================================================
# Called from settings_form.php and from agi file: wakeup
	public function getAlarmConfig() {
# WARNING:	We assume there is only one config record.
#			The db design allows for more. NEEDS Reviewing when the design is extended
		$sql = "SELECT * FROM wakeupalarms LIMIT 1";
		try {
			$sth = $this->db->prepare($sql);
			$sth->execute();
			$cfg = $sth->fetch(\PDO::FETCH_ASSOC);
		}
		catch (\PDOException $e) {\out($e->getMessage()); return array();}
		$cfg['operator_extensions'] = explode(",",$cfg['operator_extensions']);
#		$cfg['operator_extensions'] = explode(",",$cfg['operator_extensions']);
		return $cfg;
	}
#============================================================================
# Update Config table with new values - called from form.php
# WARNING:	Code assumes there is only a single record but the design of the table allows for
#			multiple records.  Will need changing later.
	public function updateAlarmConfig($fields) {
		$ok = $this->validateConfigInput($fields);
		if($ok <> true) return false;
# $fields is the $_REQUEST array and contains more fields than are relevant here.  Others are ignored.
# Replace CR with nothing
		$fields['operator_extensions'] = str_replace("\r",'',$fields['operator_extensions']);
# Replace spaces with nothing
		$fields['operator_extensions'] = str_replace(' ','',$fields['operator_extensions']);
# Replace new-lines with commas
		$fields['operator_extensions'] = str_replace("\n",",",$fields['operator_extensions']);
		if(empty($fields)) return false;
		try {
			$sql = "UPDATE wakeupalarms SET maxretries = ?, waittime = ?, retrytime = ?, extensionlength = ?, cnam = ?, cid = ?, operator_mode = ?, operator_extensions = ? LIMIT 1";
			$sth = $this->db->prepare($sql);
			$ret = $sth->execute(array($fields['maxretries'], $fields['waittime'], $fields['retrytime'], $fields['extensionlength'], $fields['cnam'], $fields['cid'], $fields['operator_mode'], $fields['operator_extensions']));
		}
		catch (\PDOException $e) {\out($e->getMessage()); return false;}
		return true;
	}
#============================================================================
public function validateConfigInput($fields) {
# Validate all input fields
	if(empty($fields['extensionlength'])) $errMsg .= '\n\n- Destination length must be specified';
	if(!empty($fields['extensionlength']) && ($fields['extensionlength'] < 2 || $fields['extensionlength'] > 20))
		$errMsg .= '\n\n- Destination length must be between 2 and 20';
	if($fields['operator_mode'] == 1 && empty($fields['operator_extensions'] ))
		$errMsg .= '\n\n- Operator Extensions must be specified if Operator Mode is "Yes"';
	if(empty($fields['waittime'])) $errMsg .= '\n\n- Ring time must be specified';
	if(!empty($fields['waittime']) && ($fields['waittime'] < 2 || $fields['waittime'] > 300))
		$errMsg .= '\n\n- Ring time must be between 2 and 300 seconds';
	if(empty($fields['retrytime'])) $errMsg .= '\n\n- Retry time must be specified';
	if(!empty($fields['retrytime']) && ($fields['retrytime'] < 10 || $fields['retrytime'] > 300))
		$errMsg .= '\n\n- Retry time must be between 10 and 300 seconds';
	if(empty($fields['maxretries'])) $errMsg .= '\n\n- Max Retries must be specified';
	if(!empty($fields['maxretries']) && ($fields['maxretries'] > 100))
		$errMsg .= '\n\n- Max Retries must be less than 101';
# Error?
	if(isset($errMsg)) {
	  # Drop leading newline characters (\n\n) and add prefix
		$errMsg = 'Errors!\n\n '.substr($errMsg,4);
		echo "<script>javascript:alert('".$errMsg."');</script>";
	  # Save input fields so they can be redisplayed
		$_SESSION['configFields'] = $fields;
		return false;
	}
	else return true;	# OK
}
#============================================================================
# Update alarm config serial number
	public function updateAlarmConfigSerial($serial) {
# Update config file with new serial number - updates all records if more than one
		$sql = "UPDATE wakeupalarms SET serial = $serial";
		try {$this->db->query($sql);}
		catch (\PDOException $e) {$this->writelog("SQL \n\n  $sql \n\nfailed with error message \n\n".$e->getMessage());}
	}
#============================================================================
# Called from agi files: wakeup67 and wakeconfirmalarm.php
	public function addWakeup($destination, $time, $lang) {
		$cfg_data = $this->getAlarmConfig();	
	  # Note: $time is Unix time
	  # in MySQL DateTime string format: yyyy-mm-dd hh:mm:ss
	  # Add schedule data to array
		$cfg_data['time'] = date('Y-m-d H:i:s' ,$time);
		$cfg_data['timeUnix'] = $time;
		$cfg_data['ext'] = $destination;
		$cfg_data['callerid'] = $cfg_data['cnam']." <".$cfg_data['cid'].">";
		$cfg_data['lang'] = $lang;
	  # Increment serial number for use in unique file name
		$serial = $cfg_data['serial'] + 1;	
	  # Update config file with new serial number
		$this->updateAlarmConfigSerial($serial);
	  # Generate the callfile
		$this->genCallfile($cfg_data, $serial);
	}
#============================================================================
# Called from wakeup 
	public function removeWakeup($file) {
		$file = $this->getCallfilePath($id).basename($file);
		if(file_exists($file)) unlink($file);
		return true;
	}
#============================================================================
# Called from addWakeup and genAllDueAlarms
# Generate the wakeup callfile based on the array provided
# $serial is the unique part of the new file name
# (This has changed from the old naming convention that included the alarm time and the extension)
	public function genCallfile($cfg, $serial) {
	  # Do not generate if the time is in the past by more than 60 seconds
		If($cfg['timeUnix'] < time() - 60) return false;
	  # Find or create /tmp folder
		$ast_path = $this->FreePBX->Config->get('ASTSPOOLDIR');
		$ast_tmp_path = $ast_path."/tmp/";
		if(!file_exists($ast_tmp_path)) mkdir($ast_tmp_path,0777,true);
	  # and set values if default not set
		if (empty($cfg['tempdir'])) $cfg['tempdir'] = $ast_tmp_path;
		if (empty($cfg['outdir'])) $cfg['outdir'] = $ast_path."/outgoing/";
		if (empty($cfg['context'])) $cfg['context'] = K_CONTEXT;
		$cfg['filename'] = "wuc.".$serial.".call";
	# We use a temp directory to first create the file and only when it has been saved there
	# do we move it into the Asterisk processing directory - to ensure that a partial file is not attempted by Asterisk
		$tempfile = $cfg['tempdir'].$cfg['filename'];
		$outfile = $cfg['outdir'].$cfg['filename'];
	# Clean up extension
		$cfg['ext'] = preg_replace("/[^\d@\+\#]/","",$cfg['ext']);
	#  Delete any old .callfile with the same name as the one we are creating.
		if(file_exists($outfile)) unlink($outfile);
	# Create a .callfile in the temp directory, write and close
	# The format of the file is defined at 
	# <https://www.voip-info.org/wiki/view/Asterisk+Documentation+1.6.1+callfiles.txt>
	# <https://github.com/yclee/astlite/blob/master/doc/callfiles.txt>
		$wuc = fopen( $tempfile, 'w');
		fputs( $wuc, "channel: Local/".$cfg['ext']."@".$cfg['context']."\n" );
		fputs( $wuc, "maxretries: ".$cfg['maxretries']."\n");
		fputs( $wuc, "retrytime: ".$cfg['retrytime']."\n");
		fputs( $wuc, "waittime: ".$cfg['waittime']."\n");
		fputs( $wuc, "callerid: ".$cfg['callerid']."\n");
		fputs( $wuc, 'set: CHANNEL(language)='.$cfg['lang']."\n");
		fputs( $wuc, "application: ".$cfg['application']."\n");
		fputs( $wuc, "data: ".$cfg['data']."\n");
		fclose( $wuc );
	# Set time of temp file and move to outgoing
		touch( $tempfile, $cfg['timeUnix'], $cfg['timeUnix'] );
		rename( $tempfile, $outfile );
		return true;
	}
#============================================================================
# Update callfile with new time
	public function setNewTime($id, $newUnixTime) {
		$outfile = $this->callfileName($id);
	  # Set time of file to new time
		if(file_exists($outfile)) touch( $outfile, $newUnixTime, $newUnixTime);
	}
#============================================================================
# Called from agi file: wakeup
# Returns all callfiles in an array
	public function getAllCalls() {
		$calls = array();
		foreach(glob($this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/wuc*.call") as $file) {
			$id = $this->CheckWakeUpProp($file);
			if(!empty($id)) {
			  # File timestamp
				$ft = filemtime($file);
			  # Date string from timestamp
				$filedate = date('M d Y',$ft); 
			  # Time string from timestamp
				$filetime = date('H:i',$ft);
				$calls[] = array(
					"filename" => basename($file),
					"timestamp" => filemtime($file),
					"time" => $filetime,
					"date" => $filedate,
					"destination" => $this->getDestinationFromCallFile($id),
				);
			}
		}
		return $calls;
	}
#============================================================================
# Extract the Destination from a callfile
	public function getDestinationFromCallFile($id) {
	  # File name to read in
		$file = $this->callfileName($id);
		if(file_exists($file)) {
		  # Read the file - each line into an array
			$arr = explode("\n", file_get_contents($file));
		  # Extract Destination from record formatted as: channel: Local/210@from-internal
			$x = strpos($arr[0] , '/');		# Find /
			$y = strpos($arr[0] , '@', $x);	# Find @
			return substr($arr[0], $x+1, $y-$x-1);
		}
		else return false;
	}
#============================================================================
# Get the file modification time from a callfile
	public function getTimeFromCallFile($id) {
	  # File name
		$file = $this->callfileName($id);
		if(file_exists($file)) return date('Y-m-d H:i',filemtime($file));
		else return false;
	}
#============================================================================
# Extract UnixTime from a callfile
	public function getUnixTimeFromCallFile($id) {
		$file = $this->callfileName($id);
		if(file_exists($file)) return filemtime($file);
		else return false;
	}
#============================================================================
# Get callfile path
	public function getCallfilePath($id) {
		return $this->FreePBX->Config->get('ASTSPOOLDIR')."/outgoing/";
	}
#============================================================================
# Build callfile name
	public function callfileName($id) {
		return $this->getCallfilePath($id).'wuc.'.$id.'.call';
	}
#============================================================================
# Generate .call files for all schedules that are now due
# $limit may be set to a specific record key to generate just for that records, even if not due.
public function genAllDueAlarms($limit='') {
# Initialize loop-count to stop looping more than once - in case of bugs
	$loopcount = 0;
doAgain:
	$loopcount += 1;
	if($loopcount > 2) return;
#-----------------------------
# Generate alarms due within (Now + K_GEN_CALL_IN_ADVANCE) hours.
# Typically K_GEN_CALL_IN_ADVANCE is set to 25 so that hourly cron jobs will generated alarm calfiles at least 24 hours ahead.
	$wTime = new \DateTime();
	$wTime->modify('+'.K_GEN_CALL_IN_ADVANCE.' hours');
	$timebefore = "'".$wTime->format('Y-m-d H:i:s')."'";
# Basic where clause: Select those due to be generated and not suspended.
	$where = "time < $timebefore AND suspend <> '1'";
# Add limit to a single ID, if supplied
	if ($limit != '') $where .= " AND id_schedule = $limit";
	$sql = "SELECT * FROM wakeupalarms_calls WHERE ($where) ORDER BY id_cfg";
# Save values from database as associative array
	try {
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$res = $stmt->fetchall(\PDO::FETCH_ASSOC);
	}
	catch (\PDOException $e) {
		$this->writelog("SQL \n\n  $sql \n\nfailed with error message \n\n".$e->getMessage());
		return null;
	}
# Return if nothing is due
	if (empty($res)) return null;
# Process each returned row - order is by 'id_cfg'
	$id_cfg="";		# To save the last 'id_cfg' code retrieved
# Initialize Rerun flag
	$rerun = false;
#----------
	foreach ($res as $schedule) {
	  # Get config data for this schedule - if not already done
		if($id_cfg <> $schedule['id_cfg']) {
		  # Save key so no need to retrieve each time
			$id_cfg = $schedule['id_cfg'];
			$cfg_data = \FreePBX::Wakeupalarms()->getAlarmConfig($id_cfg);
		  # Save serial number at the start of the batch - to be updated at the end
			$serial = $cfg_data['serial'];
		}
	  # Note: $schedule['time'] is in MySQL DateTime string format: yyyy-mm-dd hh:mm:ss
	  # Add schedule data to array
		$cfg_data['time'] = $schedule['time'];
		$cfg_data['timeUnix'] = strtotime($schedule['time']);
		$cfg_data['ext'] = $schedule['ext'];
		$cfg_data['callerid'] = $cfg_data['cnam']." <".$cfg_data['cid'].">";
		$cfg_data['lang'] = $schedule['lang'];
	  # Increment serial number
		$serial += 1;
	  # Generate call file for this schedule
		$ok = $this->genCallfile($cfg_data, $serial);
	  # Returns false if nothing generated as the date/time is in the past (a suspended alarm that has now been released)
	  # We will need a rerun once the moveForward fn below has run and brought old alarms up to date
	  # Set flag for rerun if any alarms needs to be re-inspected.
		if(!$ok) $rerun = true;
	  # Move schedule forward (keeps going till time is in the future)
		$this->moveForward($schedule);
	} # End of foreach
#----------
# Update config file with new serial number
	$this->updateAlarmConfigSerial($serial);
# If rerun needed then do it
	if($rerun) goto doAgain;
}
#============================================================================
	public function moveForward($schedule) {
# If the record is for a one-off alarm, it is deleted otherwise it is updated to the next date/time in the cycle
# As a scheduled alarm could be suspended, when it is released there could be one or more elapsed cycles.
# So we need to loop through till a future time is reached.
# Schedule time as unix date
	$wdate = \DateTime::createFromFormat('Y-m-d H:i:s', $schedule['time']);
	switch ($schedule['per']) {
	  # If schedule is not repeating then delete it from DB
		case "X":
			$ok = $this->dbAlarmDel($schedule['id_schedule']);
			break;
		# If schedule is repeating daily then update record to next day
		case "D":
		  # Add 1 day in MySQL datetime format - till result is greater than now
			do {
				$wdate->modify('+1 day');
			} while ($wdate < new \DateTime('now'));
		  # Back as date string
			$time = $wdate->format('Y-m-d H:i:s');
			$ok = $this->update_schedule($schedule['id_schedule'], $time); 
			break;
		# If schedule is repeating weekly then update record to next week
		case "W":
		  # Add 1 week in MySQL datetime format - till result is greater than now
			do {
				$wdate->modify('+1 week');
			} while ($wdate < new \DateTime('now'));
		  # Back as date string
			$time = $wdate->format('Y-m-d H:i:s');
			$ok = $this->update_schedule($schedule['id_schedule'], $time); 
			break;
	}
}
#============================================================================
	public function update_schedule($recordID, $time) {
		$sql = "UPDATE wakeupalarms_calls SET time = '$time' WHERE id_schedule = $recordID";
		try {$this->db->prepare($sql)->execute();}
		catch (\PDOException $e) {
			$this->writelog("SQL \n\n  $sql \n\nfailed with error message \n\n".$e->getMessage());
			return false;
		}
		return true;
	}
#============================================================================
	public function ajaxRequest($req, &$setting) {
		switch ($req) {
			case 'getJSON':
				return true;
			break;
			default:
				return false;
			break;
		}
	}
#--------------------------------------------------------------
	public function ajaxHandler(){
		switch ($_REQUEST['command']) {
			case 'getJSON':
				switch ($_REQUEST['jdata']) {
					case 'grid':
						return array_values($this->listAllWakeupalarms());
					break;

					default:
						return false;
					break;
				}
			break;

			default:
				return false;
			break;
		}
	}
#--------------------------------------------------------------
	public function getRightNav($request) {
		if(isset($request['view']) && $request['view'] == 'form'){
			return load_view(__DIR__."/views/bootnav.php",array());
		}
	}
#--------------------------------------------------------------
# Displays the Submit, Reset, Delete bar at the bottom of the Edit page
	public function getActionBar($request){
		switch($request['display']){
			case 'wakeupalarms':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					)
				);
			break;
		}
		if (empty($request['extdisplay'])) unset($buttons['delete']);
		if($request['view'] != 'form') unset($buttons);
		if($request['requesttype'] == 'info') unset($buttons);
		return $buttons;
	}
}