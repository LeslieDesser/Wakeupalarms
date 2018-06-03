<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
#============================================================================
# This function and the next are required to make the feature-code work - Do NOT remove
# The FreePBX 'Reload' calls the functions - fn must be called <rawmodulename>_get_config
function wakeupalarms_get_config($engine) {
	$modulename = 'wakeupalarms';	# <rawmodulename>
  # This generates the dialplan
	global $ext;
	global $asterisk_conf;
	switch($engine) {
		case "asterisk":
			if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
				foreach($featurelist as $item) {
					$featurename = $item['featurename'];
					$fname = $modulename.'_'.$featurename;
					if (function_exists($fname)) {
						$fcc = new featurecode($modulename, $featurename);
						$fc = $fcc->getCodeActive();
						unset($fcc);
						if ($fc != '') $fname($fc);
					}
					else $ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
				}
			}
		break;
	} # End switch
}
# This function required to make the feature code work - Do NOT remove
# The function name must consist of: $modulename.'_'.$featurename 
# (See previous function from where it is called and the line 
# 		$fcc = new featurecode($cModule, $cModule)
# in install.php
function wakeupalarms_wakeupalarms($c) {
	global $ext;
	global $asterisk_conf;

	$id = "app-wakeupalarms"; // The context to be included
	$ext->addInclude('from-internal-additional', $id); // Add the include from from-internal
	$ext->add($id, $c, '', new ext_Macro('user-callerid'));
	$ext->add($id, $c, '', new ext_answer(''));
	$ext->add($id, $c, '', new ext_wait(1));
	$ext->add($id, $c, '', new ext_AGI('wakeup67'));
	$ext->add($id, $c, '', new ext_Hangup);
}
#============================================================================
function wakeupalarms_hook_core($viewing_itemid, $request) {
// This is empty. Need to be here for the wakeupalarms_hookProcess_core function to work
}

function _wakeupalarms_backtrace() {
	$trace = debug_backtrace();
	$function = $trace[1]['function'];
	$line = $trace[1]['line'];
	$file = $trace[1]['file'];
	freepbx_log(FPBX_LOG_WARNING,'Depreciated Function '.$function.' detected in '.$file.' on line '.$line);
}
?>
