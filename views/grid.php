<?php
# Display any error message
	if(isset($_SESSION['ErrorText'])) {
		echo "<script>javascript:alert('". $_SESSION['ErrorText'].".');</script>";
		unset($_SESSION['ErrorText']);
	} 
?>
<div id="toolbar-rg">
<!-- Remove Info panel as it takes up too much room. An Info button below has been added instead.
<div class="panel panel-info">
	<div class="panel-heading">
		<div class="panel-title">
			<a href="#" data-toggle="collapse" data-target="#moreinfo" class="collapsed" aria-expanded="false"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _('What is Hotel Style Wakeup Calls?')?></div>
	</div>
	<div class="panel-body collapse" id="moreinfo" aria-expanded="false" style="height: 30px;">
		<p><?php echo sprintf(_('Wake Up Alarm calls can be used to schedule a reminder or wakeup call to any valid destination. To schedule a call, dial %s or use the form below'),$code)?></p>
	</div>
</div> -->

<form name="refresh" action="" method="POST"><input name="RefreshTable" type="submit" value="Refresh">
	<a href="config.php?display=wakeupalarms&view=form&requesttype=add" class="btn btn-default" >
		<i class="fa fa-plus"></i>&nbsp; <?php echo _("Add Wakeup Alarms") ?>
	</a>
	<a href="config.php?display=wakeupalarms&view=form&requesttype=settings" class="btn btn-default" ><?php echo _("Settings") ?></a>
	<a href="config.php?display=wakeupalarms&view=form&requesttype=info" class="btn btn-default" ><i class="glyphicon glyphicon-info-sign"></i><?php echo _(" Info") ?></a>
	&nbsp;&nbsp;<span class=" btn-time disabled"><b><?php echo _("Details displayed at: ").date('j M Y H:i:s')?></b></span>
</form>
</div>
<table id= "wakeupalarmid"
				data-url="ajax.php?module=wakeupalarms&command=getJSON&jdata=grid"
        data-cookie="true"
				data-toolbar="#toolbar-rg"
        data-cookie-id-table="wakeupalarmid"
        data-maintain-selected="true"
        data-toggle="table"
        data-pagination="true"
        data-search="true"
        class="table table-striped">
<thead>
	<tr>
		<th data-sortable="true" data-field="ext"><?php echo _("Destination")?></th>
		<th data-sortable="true" data-field="time"><?php echo _("Date/Time")?></th>
		<th data-sortable="true" data-field="per"><?php echo _("Repeating?")?></th>
		<th data-sortable="true" data-field="id_cfg"><?php echo _("Type")?></th>
		<th data-sortable="true" data-field="suspend"><?php echo _("Suspended?")?></th>
		<th data-sortable="true" data-field="id_schedule"><?php echo _("ID")?></th>
		<th data-field="id_schedule" data-formatter="linkFormatter"><?php echo _("Actions")?></th>
	</tr>
</thead>
</table>
<script type="text/javascript">
function linkFormatter(value, row, index){
    var html = '<a href="?display=wakeupalarms&view=form&extdisplay='+value+'"><i class="fa fa-edit"></i></a>';
    html += '&nbsp;<a href="?display=wakeupalarms&action=delAlarm&account='+value+'" class="delAction"><i class="fa fa-trash"></i></a>';
    return html;
}
</script>
