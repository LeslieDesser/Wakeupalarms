<!--Used to change database alarm record -->
<br><br>
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

<!--Field: ext -->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">		
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="ext"><?php echo _("Destination") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="ext"></i>
					</div>
					<div class="col-md-9">
						<div class="input-group">
							<input type="text" class="form-control" id="ext" name="ext" value="<?php echo $ext; ?>">
							<span class="input-group">
								<select id="qsagents1" class="form-control" data-for="ext" style="width:170px;">
									<option value="" SELECTED><?php echo("Extension Select")?></option>
									<?php echo $extnlist ?>
								</select>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="ext-help" class="help-block fpbx-help-block"><?php echo _("Enter extension number, ring group, etc or use the Select Extension button.")?></span>
		</div>
	</div>
</div>

<!--Field: per-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="per"><?php echo _("Repeating?") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="per"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" id="per_once" name="per" value="X" <?php echo ($per=='X'?'checked':'');?>>
						<label for="per_once"><?php echo _('Once-Only'); ?></label>
						<input type="radio" id="per_daily" name="per" value="D" <?php echo ($per=='D'?'checked':'');?>>
						<label for="per_daily"><?php echo _("Daily")?></label>
						<input type="radio" id="per_weekly" name="per" value="W" <?php echo ($per=='W'?'checked':'');?>>
						<label for="per_weekly"><?php echo _('Weekly'); ?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="per-help" class="help-block fpbx-help-block"><?php echo _('Select whether the alarm is once-only or repeating.')?></span>
		</div>
	</div>
</div>
<!--END Field: per -->

<!--Field: suspend-->
<div class="element-container">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="form-group">
					<div class="col-md-3">
						<label class="control-label" for="suspend"><?php echo _("Suspended?") ?></label>
						<i class="fa fa-question-circle fpbx-help-icon" data-for="suspend"></i>
					</div>
					<div class="col-md-9 radioset">
						<input type="radio" id="sus_yes" name="suspend" value="1" <?php echo ($suspend=='1'?'checked':'');?>>
						<label for="sus_yes"><?php echo _('Yes'); ?></label>
						<input type="radio" id="sus_no" name="suspend" value="0" <?php echo ($suspend=='0'?'checked':'');?>>
						<label for="sus_no"><?php echo _("No")?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<span id="suspend-help" class="help-block fpbx-help-block"><?php echo _('Select whether the alarm is suspended or not.')?></span>
		</div>
	</div>
</div>
<!--END Field: per -->


<?php
// implementation of module hook
$module_hook = \moduleHook::create();
echo $module_hook->hookHtml;
?>
