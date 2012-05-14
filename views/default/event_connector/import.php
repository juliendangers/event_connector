<?php
	if (defined('ACCESS_DEFAULT')) {
		$access_id = ACCESS_DEFAULT;
	} else {
		$access_id = 0;
	}

?>
<div class="contentWrapper">
<form action="<?php echo $vars['url']; ?>action/import_ical/" enctype="multipart/form-data" method="post">
<p>
	<label>
<?php
	echo elgg_view('input/securitytoken');
	echo elgg_echo("event_connector:upload:file");
?>
<br />
<?php

	echo elgg_view("input/file",array('internalname' => 'upload'));
			
?>
	</label>
</p>
<p>
	<label>
		<?php echo elgg_echo('access'); ?><br />
		<?php echo elgg_view('input/access', array('internalname' => 'access_id','value' => $access_id)); ?>
	</label>
</p>
	
<p>
<?php

	echo "<input type=\"hidden\" name=\"container_guid\" value=\"{$vars['container_guid']}\" />";
	
?>
	<input type="submit" value="<?php echo elgg_echo("save"); ?>" />
</p>

</form>
</div>
