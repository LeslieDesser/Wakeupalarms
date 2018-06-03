<!--1 What does this module do-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="description"><?php echo _("What does this module do?") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="description"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="description-help" class="help-block fpbx-help-block"><?php
				echo _('Wake-up Alarms provides several ways whereby alarm (wake-up) phone calls can be scheduled to ring a specific destination.  A destination is either an internal destination or an external phone number.');
				echo _('<br>1. By phone: An extension can call feature code '.K_FEATURE_CODE.' and set up an alarm for a specific time for his own extension.');
				echo _('<br>2. By phone: A designated Operator extension can call feature code '.K_FEATURE_CODE.' and set up an alarm for any extension or any other internal or external destination.');
				echo _('<br>3. Via the web interface, an authorized user can schedule an alarm for any extension to any destination. Such alarm can be set up as either a one-off or as a Daily or Weekly repeating alarm.');
				echo _('<br>4. Existing alarms can be amended or deleted.');
			?></span>
		</div>
	</div>
</div>
<!--2 Main Screen-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="main"><?php echo _("The Main Screen") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="main"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="main-help" class="help-block fpbx-help-block"><?php
				echo _('The main screen shows the list of existing alarms.  Columns are as follows:');
				echo _('<br><br>1. Destination: The extension, ring-group or similar, or an external phone number that needs to be called at the due time.');
				echo _('<br><br>3. Repeating?: Options are: Daily (same time each day); Weekly (same time each week); Once-only (not yet due within '.(K_GEN_CALL_IN_ADVANCE - 1).' hours or so); Once (due within the next '.(K_GEN_CALL_IN_ADVANCE - 1).' hours or so).');
				echo _('<br><br>4. Type: Which `Settings` type is applicable to the alarm.  At the moment there is only one global type: '.K_WUC.'.');
				echo _('<br><br>5. Suspended?: Yes or No. If Yes, the alarm will not go off until set to No - at which point it will skip over overdue alarms.');
				echo _('<br><br>6. ID: This is an internal field which identifies either the database key for the record (D prefix) or the unique part of the call-file name (C prefix). (This field could be hidden if I knew how)');
				echo _('<br><br>5. Actions: Edit or delete the alarm.');
			?></span>
		</div>
	</div>
</div>
<!--3 Settings-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="settings"><?php echo _("Settings") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="settings"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="settings-help" class="help-block fpbx-help-block"><?php
				echo _('The screen currently shows only the Global settings (Type '.K_WUC.'):');
				echo _('<br><br>The first two fields relate only to alarms set up by extensions using the '.K_FEATURE_CODE.' feature code. The rest are relevant to all alarms.');
				echo _('<br><br>1. Operator Mode: When set to Yes it will allow the designated operator extensions to create alarm calls (using '.K_FEATURE_CODE.') for all valid destinations. If set to No, calls can only be placed back to the extension of the user scheduling the alarm call (Extension xxxx can only place alarm calls to call extension xxxx.');
				echo _('<br><br>2 Operator Extensions: Extension numbers designated as Operators.');
				echo _('<br><br>3. Max Destination Length: This controls the maximum number of digits allowed in a destination number when being set up by an operator. Set to 10 or 11 or so to allow alarm calls to outside numbers');
				echo _('<br><br>4. Ring Time: The number of seconds for the phone to ring. Consider setting lower than the voicemail threshold or the call could end up going to voicemail');
				echo _('<br><br>5. Retry Time: The number of seconds to wait between retries. A "retry" happens if the alarm call is not answered');
				echo _('<br><br>6. Max Retries: The maximum number of times the system should retry to deliver the alarm call when there is no answer. Value of 0 means only one call will be placed. Value of 1 means up to 2 calls and so on.');
				echo _('<br><br>7. Caller ID Name: CID name to appear.  Something like `Alarm Call`');
				echo _('<br><br>8. Caller ID Code: CID code to appear. Usually the feature code '.K_FEATURE_CODE.'. You can also use : "hidden" to hide the CallerID sent out over Digital lines if supported (E1/T1/J1/BRI/SIP/IAX)');
			?></span>
		</div>
	</div>
</div>
<!--4 How it works-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="how"><?php echo _("How it works") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="how"></i>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="how-help" class="help-block fpbx-help-block"><?php
				echo _('Alarms can be set up either via a phone extension or via the web interface.');
				echo _('<br><br>Alarms set up via the phone system are always one-off alarms.  Those input via the web interface can be set up as repeating alarms or as one-off.');
				echo _('<br><br>Alarms set up via an extension are immediately created as Call files which automatically trigger the alarm call at the designated time.');
				echo _('<br><br>Alarms set up via the web interface are stored in a database.');
				echo _('<br><br>At every '.K_CRON_SCHEDULE.' minutes past the hour, a Cron job runs and scans the database for alarms that are due within the next '.K_GEN_CALL_IN_ADVANCE.' hours.  For each such alarm a Call file is created to run at the designated time, and the database record is either incremented to the next due date in the cycle(if repeating) or deleted (if one-off)');
				echo _('<br><br>A Call file is triggered when the call time is reached and the designated number is called. When the alarm is answered, the user can either just hang up to cancel the alarm or he can select a 5, 10 or 15 minute snooze. If the call is not answered then it will ring for the designated time and will then be retried, all as per the details entered on the Settings screen. Once the  number of retries are exhausted, the alarm Call file is deleted from the system.');
			?></span>
		</div>
	</div>
</div>
