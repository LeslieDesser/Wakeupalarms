<?php
# For simplicity, where previous code ends in 'return' we do not use else
	include_once 'constants.inc.php';
	global $amp_conf;
	out(_("Installing WakeUp Alarms"));
# -------------------- Review and change code in this section only
$cTable1 = 'wakeupalarms';			# Name of Config table
$cKey1 = array('id_cfg','id_user');	# Unique keys for table 1

$cTable2 = 'wakeupalarms_calls';	# Name of Scheduled Calls table
$cKey2 = array('ext', 'time', 'per');	# Unique key for table 2

$cModule = 'wakeupalarms';			# Module name
$cDescription = 'Wake-Up Alarms';	# FeatureCode name
$cFeatureCode = K_FEATURE_CODE;		# FeatureCode
$cDefaultKey = 'WUC';				# Default value for primary config key
$cDefaultDesc = 'Default Configuration - always used by the phone based call setup';

# List of the columns for the Config table - $cTable1.
# Do not define primary index below - use section above - unless it is auto increment.
$cols['id_cfg'] = "VARCHAR(6) NOT NULL DEFAULT '".$cDefaultKey."'";
$cols['id_user'] = "INT NOT NULL DEFAULT 0";
$cols['description'] = "VARCHAR(150)DEFAULT '".$cDefaultDesc."'";
$cols['maxretries'] = "INT NOT NULL DEFAULT '3'";
$cols['waittime'] = "INT NOT NULL DEFAULT '60'";
$cols['retrytime'] = "INT NOT NULL  DEFAULT '60'";
$cols['extensionlength'] = "INT NOT NULL DEFAULT '4'";
$cols['cid'] = "VARCHAR(30) DEFAULT '".K_FEATURE_CODE."'";
$cols['cnam'] = "VARCHAR(30) DEFAULT 'Wake Up Calls'";
$cols['operator_mode'] = "INT NOT NULL DEFAULT '0'";
$cols['operator_extensions'] = "VARCHAR(30)";
$cols['application'] = "VARCHAR(30) DEFAULT 'AGI'";
$cols['data'] = "VARCHAR(30) DEFAULT 'wakeconfirmalarm.php'";
$cols['serial'] = "INT UNSIGNED NOT NULL DEFAULT '0'";

# List of the columns for the Scheduled Calls table - $cTable2.
$sc_cols['id_schedule'] = "INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY";
$sc_cols['time'] = "DATETIME NOT NULL";
$sc_cols['ext'] = "VARCHAR(20) NOT NULL";
$sc_cols['per'] = "CHAR(1)";
$sc_cols['id_cfg'] = "CHAR(3) NOT NULL";
$sc_cols['suspend'] = "CHAR(1) NOT NULL DEFAULT '0'";
$sc_cols['lang'] = "CHAR(5) NOT NULL";
# -------------------- END Change code -----------------------------

# Create/Update Config table
	$ok = process_table($cTable1,$cols,$cKey1);
# Create/Update default record in Config file
	if ($ok) $ok = create_default_config_record($cTable1,$cDefaultKey,$cDefaultDesc);
# Create/Update Calls table
	if ($ok) $ok = process_table($cTable2,$sc_cols,$cKey2);
# Register FeatureCode - Wakeup Alarms;
	if ($ok) {
		$fcc = new featurecode($cModule, $cModule); # 1st parm is <rawmodulename> and 2nd is <uniquefeaturecodename>
		$fcc->setDescription($cDescription);
		$fcc->setDefault($cFeatureCode);
		$fcc->setHelpText('Create/Delete Wake-up Alarms');
		$fcc->setProvideDest(TRUE);
		$fcc->update();
		unset($fcc);
	  # -  -  -  -  -  -  -  -  -
	  # Create the cron job
	  # Scheduled calls are stored in the database. A cron job script is run periodically to generate .call files.
	  # Set to run once an hour per the value of constant K_CRON_SCHEDULE
		$wuc_cron_string = K_CRON_SCHEDULE.' * * * * '.__DIR__."/agi-bin/wakeupalarms_genalldue.php";
		wuc_add_cron($wuc_cron_string);
	  # -  -  -  -  -  -  -  -  -
		out(_("Install completed OK"));
	}
	else out(_('Error: Install failed')); 

# ========================================================
function process_table($TableName,$Fields,$KeyNames) {
	$dbh = \FreePBX::Database();
# Returns true if all done else false if something fails
# 1. Create the Config table if it does not exist
  # Check if it exists
	$sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'asterisk' AND table_name = '$TableName'";
	try {$check = $dbh->query($sql)->fetch(\PDO::FETCH_ASSOC);}
	catch (\PDOException $e) {
		\out($e->getMessage());
		throw $e;
		return false;
	}
# If not exist then create it
	if ($check['COUNT(*)'] == 0) {
	  # Build SQL string ...
		$sql = "CREATE TABLE IF NOT EXISTS ".$TableName." (";
	  # Append each field to SQL string
		foreach($Fields as $key=>$val) $sql .= $key.' '.$val.', ';
	  # Drop off trailing comma+space and add trailing bracket
		$sql = substr($sql,0,strlen($sql)-2).")";
	  # Run SQL to create table
		try {$check = $dbh->query($sql);}
		catch (\PDOException $e) {
			\out($e->getMessage());
			throw $e;
			return false;
		}
		out(_("1.2 OK: Created table: $TableName"));
	  # Add unique index
		$ok = add_unique_index($TableName,$KeyNames);
		if ($ok) return true;
		else return false;
	}
# -  -  -  -  -  -  -  -  -
# Old table exists. Update it instead.

# 2. Check all columns in $TableName and remove auto-increment attribute which interfere with dropping primary key.
#	 The auto-increment attribute will be added back when the field is updated.
# List fields and their attributes
	$sql = "DESCRIBE `$TableName`";
	try {$res = $dbh->query($sql);}
	catch (\PDOException $e) {
		\out($e->getMessage());
		throw $e;
		return false;
	}
 # Check each field for auto_increment and drop it
	while($row = $res->fetch())  {
		if(array_key_exists($row[0],$Fields)) {
			if ($row[5] == "auto_increment") {
				$sql ="ALTER TABLE $TableName MODIFY ".$row[0]." INT";
				try {$check = $dbh->query($sql);}
				catch (\PDOException $e) {
					\out($e->getMessage());
					throw $e;
					return false;
				}
			}
		}
	}
# Attribute auto_increment removed
# -  -  -  -  -  -  -  -  -
# 3a. Drop Primary Index in case now changed
	$sql = "ALTER TABLE ".$TableName." DROP PRIMARY KEY;";
  # Run SQL - Ignore any error as primary index may be missing
	try {$res = $dbh->query($sql);}
	catch(\PDOException $e) {}
# 3b. Drop Unique Index in case now changed
	$sql = "ALTER TABLE ".$TableName." DROP INDEX Constr1;";
  # Run SQL - Ignore any error as index may be missing
	try {$res = $dbh->query($sql);}
	catch(\PDOException $e) {}
# -  -  -  -  -  -  -  -  -
# 4. Update existing columns in the table with current definition in case changed
#    or delete existing columns if not in current field list
	$curret_cols = array();
	$sql = "DESCRIBE `$TableName`";
	try {$res = $dbh->query($sql);}
	catch (\PDOException $e) {
		\out($e->getMessage());
		throw $e;
		return false;
	}
	while($row = $res->fetch()) {
		if(array_key_exists($row[0],$Fields)) {
			$curret_cols[] = $row[0];
		  # Update with latest definition
			$sql = "ALTER TABLE $TableName MODIFY $row[0] ".$Fields[$row[0]];
			try {$check = $dbh->query($sql);}
			catch (\PDOException $e) {
				\out($e->getMessage());
				throw $e;
				return false;
			}
		}
		else {
		# Field needs to be removed as not in list
			$sql = "ALTER TABLE $TableName DROP COLUMN $row[0]";
			try {$check = $dbh->query($sql);}
			catch (\PDOException $e) {
				\out($e->getMessage());
				throw $e;
				return false;
			}
			out(_("4.3 Removed old column $row[0] from $TableName table."));
		}
	}
# -  -  -  -  -  -  -  -  -
# 5. Add any missing columns to the table
	foreach($Fields as $key=>$val) {
		if(!in_array($key,$curret_cols)) {
			$sql = "ALTER TABLE $TableName ADD $key $val";
			try {$check = $dbh->query($sql);}
			catch (\PDOException $e) {
				\out($e->getMessage());
				throw $e;
				return false;
			}
			out(_("5.2 Added column $key type $val to table $TableName"));
		}
	}
# -  -  -  -  -  -  -  -  -
# 6. Add unique index
	$ok = add_unique_index($TableName,$KeyNames);
	if ($ok) return true;
	else return false;
}
# ========================================================
function add_unique_index($TableName,$KeyNames) {
# Add unique index - if specified
	$dbh = \FreePBX::Database();
	if(!empty($KeyNames)) {
		$sql = "ALTER TABLE ".$TableName." ADD CONSTRAINT Constr1 UNIQUE (";
	  # Append each field
		foreach($KeyNames as $key) $sql .= $key.', ';
	  # Drop off trailing comma+space and add trailing bracket
		$sql = substr($sql,0,strlen($sql)-2).")";
		# Run SQL to create unique index
		try {$check = $dbh->query($sql);}
		catch (\PDOException $e) {
			\out($e->getMessage());
			throw $e;
			return false;
		}
		out(_("7.2 Unique key(s) added to table: $TableName"));
		return true;  # Done OK
		}
	else return true;	# Nothing to do
}
# ========================================================
function create_default_config_record($TableName,$cDefaultKey,$cDefaultDesc) {
# Populate table with default values if this is a new install (zero records)
# else update existing with unique key, if key missing.
# (The db design allows for multiple config records - for later development)
	$dbh = \FreePBX::Database();
# Count total existing records
	# $sql = "SELECT COUNT(*) FROM $TableName WHERE `id_cfg` = '$cDefaultKey'";
	$sql = "SELECT COUNT(*) FROM $TableName";
	try {$res = $dbh->query($sql);}
	catch (\PDOException $e) {
		\out($e->getMessage());
		throw $e;
		return false;
	}
	$check = $res->fetch();
# If there are no existing record then create one
	if ($check['0'] == 0) {
	  # Specify primary key and leave other fields as default
	  # 'WUC' is the default config record but the design allows for multiple configs
		$sql ="INSERT INTO `$TableName` (`id_cfg`, `id_user`) VALUES ('$cDefaultKey', 0)";
		try{$check = $dbh->query($sql);}
		catch (\PDOException $e) {
			\out($e->getMessage());
			throw $e;
			return false;
		}
		out(_("8.3 Default row has been added to $TableName"));
		return true;
	}
# -  -  -  -  -  -  -  -  -
# If there is exactly 1 record then it must be the default.
	if ($check['0'] == 1) {
  # There is 1 record. It must have keys of 'WUC' + 0
# If record already has correct key then do nothing
		$sql = "SELECT COUNT(*) FROM `$TableName` WHERE `id_cfg` = '$cDefaultKey' AND `id_user` = 0";
		try{$res = $dbh->query($sql);}
		catch (\PDOException $e) {
			\out($e->getMessage());
			throw $e;
			return false;
		}
		$check = $res->fetch();
	  # If no WUC+0 keys then update the existing one with those keys
		if ($check['0'] == 0) {
		  # Update all records (no WHERE clause) - there is only 1!
			$sql ="UPDATE `$TableName` SET `id_cfg` = '$cDefaultKey', `description` = '$cDefaultDesc', `id_user` = 0";
			try {$check = $dbh->query($sql);}
			catch (\PDOException $e) {
				\out($e->getMessage());
				throw $e;
				return false;
			}
			out(_("Primary keys of default record in $TableName updated to $cDefaultKey + 0"));
			return true;
		}
		else {
			out(_("Primary keys of default record in $TableName already exist - all is OK"));
			return true;			
		}
	} 
# -  -  -  -  -  -  -  -  -
# There are more than 1 records - we assume all is well as it has been already upgraded and unique keys are used.
	out(_("Existing multiple records already exist in $TableName - nothing changed<br>"));
	return true;
}
# ========================================================
# Add the cron job
function wuc_add_cron($cron) {
	$temp_file = sys_get_temp_dir()."/wuc_install";
# Delete temp work file in case it already exists
	if (file_exists($temp_file)) unlink($temp_file);
# List all cron jobs
	$foo = shell_exec('crontab -l');
# Backup cron entries if this is a first time install. Can then be used to manually restore if issues
	$wuc_cron_backup = sys_get_temp_dir()."/wuc_cron_backup.txt";
	if (!file_exists($wuc_cron_backup)) file_put_contents($wuc_cron_backup, $foo);
# Find and remove past cron entry created by wuc as identified by the constant comment string K_CRON_ID
	$pos = strpos($foo, K_CRON_ID); 
#?? There is a bug here: It replaces all occurrences of the given string but leaves the final newline
#?? so a blank line is left each time install is run.
#?? Similar problem when uninstall is run - it leaves a blank line.
#?? Fix how??
	if ($pos) {
		$regex = "~.*?".K_CRON_ID."~";
		$foo = preg_replace($regex ,"",$foo);
	} 
# Add cron job
	file_put_contents($temp_file, $foo.$cron.' #'.K_CRON_ID.PHP_EOL);
	echo exec('crontab '.$temp_file);
}
#===============================================================================
?>
