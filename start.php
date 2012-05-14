<?php
/**
 * Event Connector
 *
 * @package ElggEventConnector
 */

elgg_register_event_handler('init', 'system', 'event_connector_init');

function event_connector_init() {
	
	elgg_register_library('vendors:icalcreator', elgg_get_plugins_path() . 'event_connector/vendors/iCalcreator.class.php');
	
	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('event_connector', 'event_connector_page_handler');
	
	elgg_register_event_handler('pagesetup', 'system', 'event_connector_pagesetup');

	// Register actions
	elgg_register_action("event_connector/import", elgg_get_plugins_path() . "event_connector/actions/event_connector/import.php");

}

/**
 * Dispatcher for ical.
 * URLs take the form of
 *  Import events:    event_connector/import/<guid>
 * 
 * @param array $page
 * @return bool
 */
function event_connector_page_handler($page) {
	if (!isset($page[0])) {
		$page[0] = 'import';
	}

	elgg_push_breadcrumb(elgg_echo('event_calendar:show_events_title'), 'event_calendar/list');

	$base_dir = elgg_get_plugins_path() . 'event_connector/pages/event_connector';

	$page_type = $page[0];
	switch ($page_type) {
		case 'import':
			set_input('guid', $page[1]);
			include "$base_dir/import.php";
			break;
		default:
			return false;
	}
	return true;
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
	if (elgg_get_context() == 'event_calendar' && !(strpos($_SERVER['REQUEST_URI'], '/manage_event')) && !(strpos($_SERVER['REQUEST_URI'], '/delete_confirm')) && !(strpos($_SERVER['REQUEST_URI'], '/event_connector'))) {
		if($event_id ) {
			add_submenu_item(elgg_echo('event_connector:menu:one'), elgg_get_site_url() . "mod/event_connector/export_event.php?event_id={$event_id}");
		}
		else {
			add_submenu_item(elgg_echo('event_connector:menu:all'), elgg_get_site_url() . "mod/event_connector/export_events.php?{$param}");
		}
		elgg_register_title_button('event_connector', 'import');
	} else if(elgg_get_context() == 'groups' && strpos($_SERVER['REQUEST_URI'], 'event_calendar/') && !(strpos($_SERVER['REQUEST_URI'], '/manage_event')) && !(strpos($_SERVER['REQUEST_URI'], '/delete_confirm')) && !(strpos($_SERVER['REQUEST_URI'], '/event_connector'))) {
		if($event_id) {
			add_submenu_item(elgg_echo('event_connector:menu:title'), elgg_get_site_url() . "mod/event_connector/export_event.php?event_id={$event_id}");
		}
		else {
			add_submenu_item(elgg_echo('event_connector:menu:title'), elgg_get_site_url() . "mod/event_connector/export_events.php?{$param}");
		}
		elgg_register_title_button('event_connector', 'import');
	}
}
