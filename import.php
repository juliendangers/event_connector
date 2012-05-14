<?php

	require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");

	gatekeeper();
	if (is_callable('group_gatekeeper')) {
		group_gatekeeper();
	}
	
	if(!is_plugin_enabled("event_connector")) {
		register_error(elgg_echo('event_connector:error:notactivated'));
		forward($_SERVER['HTTP_REFERER']);				
	}
	
	// Add event_calendar menu
	set_context('event_calendar');
	
	
	$group_guid = (int) get_input('group_guid',0);
	if ($group_guid && $group = get_entity($group_guid)) {
		// redefine context
		set_context('groups');
		set_page_owner($group_guid);
		//check if user manually force the import from a group
		$group_calendar = event_calendar_activated_for_group($group);
		if (!$group_calendar) {
			register_error(elgg_echo('event_connector:error:group_calendar'));
			forward($_SERVER['HTTP_REFERER']);	
		}
	}
	// Render the ical import page
	$container_guid = page_owner();
	
	$area2 = elgg_view_title($title = elgg_echo('event_connector:upload'));
	$area2 .= elgg_view("event_connector/import", array('container_guid' => $container_guid));
	$body = elgg_view_layout('two_column_left_sidebar', '', $area2);
	
	set_context('event_connector');
	page_draw(elgg_echo("event_connector:import"), $body);
	
?>