<div id="toolbar-rgnav">
	<a href="config.php?display=wakeupalarms&view=form&requesttype=add" class="btn btn-default" ><i class="fa fa-plus"></i>&nbsp; <?php echo _("Add Wakeup Alarms") ?></a>
	<a href="config.php?display=wakeupalarms" class="btn btn-default" ><i class="fa fa-list"></i>&nbsp; <?php echo _("List Wakeup Alarms") ?></a>
</div>
<table id= "table-all-side"
				data-url="ajax.php?module=wakeupalarms&command=getJSON&jdata=grid"
				data-toolbar="#toolbar-rgnav"
        data-toggle="table"
        data-search="true"
        class="table">
<thead>
	<tr>
		<th data-sortable="true" data-field="ext"><?php echo _("Destination")?></th>
		<th data-sortable="true" data-field="time"><?php echo _("Date/Time")?></th>
	</tr>
</thead>
</table>
<script type="text/javascript">
	$("#table-all-side").on('click-row.bs.table',function(e,row,elem){
		window.location = '?display=wakeupalarms&view=form&extdisplay='+row['id_schedule'];
	})
</script>
