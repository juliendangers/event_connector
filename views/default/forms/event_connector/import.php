<?php
/**
 * iCal import form body
 *
 * @package ElggEventConnector
 */

?>
<div>
	<label><?php echo elgg_echo("event_connector:upload:file"); ?></label>
	<br />
	<?php echo elgg_view("input/file", array('name' => 'upload')); ?>
</div>
<div>
	<label><?php echo elgg_echo('access'); ?></label>
	<br />
	<?php echo elgg_view('input/access', array('name' => 'access_id', 'value' => ACCESS_DEFAULT)); ?>
</div>
	
<div class="elgg-foot">
<?php
echo elgg_view('input/hidden', array('name' => "container_guid", 'value' => $vars['container_guid']));
echo elgg_view('input/submit', array('value' => elgg_echo("save")));
?>
</div>
