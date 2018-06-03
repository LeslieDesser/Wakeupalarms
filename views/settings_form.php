<?php
# This file is included from form.php
# Get current Settings values or saved input values if any.
# $_SESSION['configFields'] used to pass over new input values but not used for long term storage as values can change.
	$config = (isset($_SESSION['configFields']) ? $_SESSION['configFields'] : \FreePBX::Wakeupalarms()->getAlarmConfig());
	unset($_SESSION['configFields']);
?>
<form class="fpbx-submit" action="?display=hotelwakeup2" method="post">
<!--Field: Operator mode -->
	<br>
	<input type="hidden" name="action" value="updated">
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="operator_mode"><?php echo _('Operator Mode')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="operator_mode"></i>
						</div>
						<div class="col-md-9">
							<span class="radioset">
								<input id="operator_mode_yes" type="radio" value="1" name="operator_mode" <?php echo $config['operator_mode'] == "1" ? "checked" : ""?>>
								<label for="operator_mode_yes"><?php echo _('Yes')?></label>
								<input id="operator_mode_no" type="radio" value="0" name="operator_mode" <?php echo $config['operator_mode'] == "0" ? "checked" : ""?>>
								<label for="operator_mode_no"><?php echo _('No')?></label>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="operator_mode-help" class="help-block fpbx-help-block">
					<?php echo _('When set to Yes it will allow the designated operator extensions to create alarm calls (using '.K_FEATURE_CODE.') for all valid destinations. If set to No, calls can only be placed back to the extension of the user scheduling the alarm call (Extension xxxx can only place alarm calls to call extension xxxx.')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Operator Extensions -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="operator_extensions"><?php echo _('Operator Extensions')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="operator_extensions"></i>
						</div>
						<div class="col-md-9">
							<!-- Important: the '?php' opening tag at the end of the next line MUST not have any spaces b/4 it
											else there will be space/tab characters in the generated html.
											Similarly no spaces after the closing tag-->
							<textarea class="form-control autosize" name="operator_extensions" id="operator_extensions"><?php 
								if(!is_array ($config['operator_extensions'])) echo $config['operator_extensions'];
								else echo implode("\n",$config['operator_extensions'])
							?></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="operator_extensions-help" class="help-block fpbx-help-block">
					<?php echo _('Enter the extension numbers of each telephone you wish to be recognized as an "Operator".  Operator extensions are allowed to create alarm calls for any valid destination. Numbers can be extension numbers, full caller ID numbers or Asterisk dialling patterns')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Max Destination Length -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="extensionlength"><?php echo _('Max Destination Length')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="extensionlength"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="extensionlength" id="extensionlength" value="<?php echo $config['extensionlength']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="extensionlength-help" class="help-block fpbx-help-block">
					<?php echo _('This controls the maximum number of digits an operator can send a wakeup call to. Set to 10 or 11 to allow wake up calls to outside numbers')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Ring Time -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="waittime"><?php echo _('Ring Time')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="waittime"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="waittime" id="waittime" value="<?php echo $config['waittime']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="waittime-help" class="help-block fpbx-help-block">
					<?php echo _('The number of seconds for the phone to ring. Consider setting lower than the voicemail threshold or the wakeup call can end up going to voicemail')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Retry Time -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="retrytime"><?php echo _('Retry Time')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="retrytime"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="retrytime" id="retrytime" value="<?php echo $config['retrytime']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="retrytime-help" class="help-block fpbx-help-block">
					<?php echo _('The number of seconds to wait between retries.  A "retry" happens if the wakeup call is not answered')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Max Retries -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="maxretries"><?php echo _('Max Retries')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="maxretries"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="maxretries" id="maxretries" value="<?php echo $config['maxretries']?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="maxretries-help" class="help-block fpbx-help-block">
					<?php echo _('The maximum number of times the system should retry the alarm call when there is no answer. Zero retries means only one call will be placed')?>
				</span>
			</div>
		</div>
	</div>
<!--Field: Caller ID Name -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="cnam"><?php echo _('Caller ID Name')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="cnam"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="cnam" id="cnam" value="<?php echo htmlentities($config['cnam'])?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="cnam-help" class="help-block fpbx-help-block">
					<?php echo _('Caller ID Name for Wake Up Calls: Descriptive text')?></span>
			</div>
		</div>
	</div>
<!--Field: Caller ID Code -->
	<div class="element-container">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="form-group">
						<div class="col-md-3">
							<label class="control-label" for="cid"><?php echo _('Caller ID Code')?></label>
							<i class="fa fa-question-circle fpbx-help-icon" data-for="cid"></i>
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" name="cid" id="cid" value="<?php echo htmlentities($config['cid'])?>">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<span id="cid-help" class="help-block fpbx-help-block">
					<?php echo _('CallerID for Wake Up Calls: Text.<br>You can also use : "hidden" to hide the CallerID sent out over Digital lines if supported (E1/T1/J1/BRI/SIP/IAX)')?></span>
			</div>
		</div>
	</div>
</form>
