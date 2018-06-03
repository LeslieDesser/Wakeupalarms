<?php
# Set to true to let debug code run
define('LD_DEBUG',true);
# Feature code used to setup alarms on the phone
define('K_FEATURE_CODE','*67');
# Schedule for the periodic cron job to generate alarms
# every hour at xx minutes after the hour
define('K_CRON_SCHEDULE','58');	# '58 * * * * '
# Hours in advance when to generate the actual call file. 
define('K_GEN_CALL_IN_ADVANCE','25'); 
# Call file name prefix
define('K_WUC','WUC');
# Default Context for call file
define('K_CONTEXT',"from-internal");
# Path to module folder
define('K_MODULE_PATH',__DIR__);
# Raw module name = folder name
define('K_MODULE',basename(K_MODULE_PATH));
# Path to log
define('K_LOG_FILE',K_MODULE_PATH.'/debug.log');
# WARNING:	Never change the following string, as comment is stored with cron job so we can 
#			identify 'our' cron job and automate cron install/un-install 
#			Called from install.php and uninstall.php
define('K_CRON_ID',"Required for POSSA Wakeup Calls Module");
?>