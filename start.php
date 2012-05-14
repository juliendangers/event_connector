<?php
/**
 * Event Connector
 *
 * @package ElggEventConnector
 */

register_elgg_event_handler('init', 'system', 'event_connector_init');

function event_connector_init() {
	register_elgg_event_handler('pagesetup', 'system', 'event_connector_pagesetup');

	// Register actions
	register_action("import_ical", elgg_get_plugins_path() . "event_connector/actions/event_connector/import.php");

}

function event_connector_pagesetup() {
	$event_id = get_input('event_id', 0);
	$filter = get_input('filter', 'all');
	$original_start_date = get_input('start_date',0);
	$mode = trim(get_input('mode',''));
	$group_guid = (int) get_input('group_guid',0);
	$offset = (int) get_input('offset',0);
	$region = get_input('region',0);
	$param = "filter={$filter}";
	if($original_start_date)
		$param .= "&start_date={$original_start_date}";
	if($mode != '')
		$param .= "&mode={$mode}";
	if($group_guid)
		$param .= "&group_guid={$group_guid}";
	if($offset)
		$param .= "&offset={$offset}";
	if($region)
		$param .= "&region={$region}";
	if (get_context() == 'event_calendar' && !(strpos($_SERVER['REQUEST_URI'], '/manage_event')) && !(strpos($_SERVER['REQUEST_URI'], '/delete_confirm')) && !(strpos($_SERVER['REQUEST_URI'], '/event_connector'))) {
		if($event_id ) {
			add_submenu_item(elgg_echo('event_connector:menu:one'), elgg_get_site_url() . "mod/event_connector/export_event.php?event_id={$event_id}");
		}
		else {
			add_submenu_item(elgg_echo('event_connector:menu:all'), elgg_get_site_url() . "mod/event_connector/export_events.php?{$param}");
		}
		add_submenu_item(elgg_echo('event_connector:import:title'), elgg_get_site_url() . "mod/event_connector/import.php");
	} else if(get_context() == 'groups' && strpos($_SERVER['REQUEST_URI'], 'event_calendar/') && !(strpos($_SERVER['REQUEST_URI'], '/manage_event')) && !(strpos($_SERVER['REQUEST_URI'], '/delete_confirm')) && !(strpos($_SERVER['REQUEST_URI'], '/event_connector'))) {
		if($event_id) {
			add_submenu_item(elgg_echo('event_connector:menu:title'), elgg_get_site_url() . "mod/event_connector/export_event.php?event_id={$event_id}");
		}
		else {
			add_submenu_item(elgg_echo('event_connector:menu:title'), elgg_get_site_url() . "mod/event_connector/export_events.php?{$param}");
		}
		$group = (int)get_input('group_guid');
		add_submenu_item(elgg_echo('event_connector:import:title'), elgg_get_site_url() . "mod/event_connector/import.php?group_guid={$group}");
	}
}
