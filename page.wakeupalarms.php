<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
## removing this does no harm:	$rg = \FreePBX::create()->Wakeupalarms;

	$request = $_REQUEST;
	$heading = _("Wakeup Alarms");
	$border = 'no';
# Save record ID if it is present
	if(!empty($extdisplay)) {
		$recordKey = $extdisplay;
		$idType = substr($recordKey,0,1);	# 1st character of key
		$id = substr($recordKey,1);			# The rest of the key
	}
	switch($request['view']){
		case "form":
			$border = 'full';
			if(!empty($recordKey)) $heading .= ": Edit Alarm ID: ".$recordKey;
			elseif($request['requesttype'] == 'settings') $heading .= ": Edit Settings";
			elseif($request['requesttype'] == 'info') $heading .= ": General Information & FAQ";
			else $heading .= ": Add New Alarm";
			$content = load_view(__DIR__.'/views/form.php', array('request' => $request));
		break;
		default:
			$content = load_view(__DIR__.'/views/grid.php');
		break;
	}
?>

<div class="container-fluid">
	<h1><?php echo $heading ?></h1>
	<div class="row">
		<div class="col-sm-12">
			<div class="fpbx-container">
				<div class="display <?php echo $border?>-border">
					<?php echo $content ?>
				</div>
			</div>
		</div>
	</div>
</div>
