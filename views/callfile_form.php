<!--Used to change callfile time/date -->
<br>
<!--Field: time -->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="time"><?php echo _("Time") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="time"></i>
					</div>
					<div class="col-md-9"><input type="time" class="form-control" id="time" name="time" value="<?php echo $time; ?>"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="time-help" class="help-block fpbx-help-block"><?php echo _("Time to call.")?></span>
		</div>
	</div>
</div>
<!--END Field: time -->

<!--Field: day (date) -->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="day"><?php echo _('Date')?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="day"></i>
					</div>
					<div class="col-md-9">
						<input type="date" class="form-control" id="day" name="day" value="<?php
							if(!empty($day)) echo $day; else echo date('Y-m-d'); 
						?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="day-help" class="help-block fpbx-help-block"><?php echo _('Date of the call')?></span>
		</div>
	</div>
</div>

<?php
// implementation of module hook
$module_hook = \moduleHook::create();
echo $module_hook->hookHtml;
?>
