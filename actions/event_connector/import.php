<?php

global $_FILES;

if (!empty($_FILES['upload']['name']) && $_FILES['upload']['error'] != 0) {
	register_error(elgg_echo('file:cannotload'));
	forward(REFERER);
}

$user = elgg_get_logged_in_user_entity();

if (!empty($_FILES['upload']['name'])) {
	$filehandler = new ElggFile();
	$filehandler->owner_guid = elgg_get_logged_in_user_guid();
	$filehandler->access_id = ACCESS_PRIVATE;

	$prefix = "tmp_calendar/";

	$filestorename = elgg_strtolower($_FILES['upload']['name']);

	$filehandler->setFilename($prefix . $filestorename);
	$filehandler->setMimeType($_FILES['upload']['type']);
	$filehandler->originalfilename = $_FILES['upload']['name'];
	$filehandler->simpletype = file_get_simple_type($_FILES['upload']['type']);

	$filehandler->open("write");
	$filehandler->close();
	
	move_uploaded_file($_FILES['upload']['tmp_name'], $filehandler->getFilenameOnFilestore());

	$cal_to_parse = $filehandler->grabFile();
	$filehandler->delete();
	$cal_to_parse = explode('\n', $cal_to_parse);
	
} /*elseif ($url = get_input('url')) {
	$cal_to_parse = explode('\n', @file_get_contents($url));
	if (get_input('check_updates')) {
		$count = elgg_get_metadata(array(
			'guid' => $user->guid,
			'metadata_names' => 'ical_cron',
			'metadata_values' => $url,
			'count' => true
		));
		if ($count <= 0) {
			create_metadata($user->guid, 'ical_cron', $url, '', $user->guid, ACCESS_PUBLIC, true);
		}
	}
}*/

if (empty($cal_to_parse)) {
	register_error(elgg_echo('event_connector:nourltoparse'));
	forward(REFERER);
}

elgg_load_library('elgg:event_calendar');
elgg_load_library('vendors:icalcreator');

$config = array('unique_id' => elgg_get_site_url());
$v = new vcalendar($config);
$v->parse($cal_to_parse);
$v->sort();

//select next month events
$eventArray = $v->selectComponents(date('Y'),date('m'),date('d'), date('Y'),date('m')+1);
foreach ($eventArray as $year => $yearArray) {
	foreach ($yearArray as $month => $monthArray) {
		foreach ($monthArray as $day => $dailyEventsArray) {
			foreach ($dailyEventsArray as $vevent) {
				$dtstart = $vevent->getProperty( 'dtstart' );
				$dtend = $vevent->getProperty( 'dtend' );
				$summary = $vevent->getProperty( 'summary' );
				$description = $vevent->getProperty( 'description' );
				$organizer = $vevent->getProperty( 'organizer' );
				$venue = $vevent->getProperty( 'location' ) ? $vevent->getProperty( 'location' ) : "default";
				$www = $vevent->getProperty( 'url' );
				//cross platform exchange
				$region = $fees = $type = $tags = $long_description = "";
				$region = $vevent->getProperty( 'X-PROP-REGION' );
				$fees = $vevent->getProperty( 'X-PROP-FEES' );
				$type = $vevent->getProperty( 'X-PROP-TYPE' );
				$tags = $vevent->getProperty( 'X-PROP-TAGS' );
				set_input('event_action', 'add_event');
				set_input('event_id', 0);
				if($group_guid) {
					set_input('group_guid', $group_guid);
				}
				set_input('title', $summary);
				set_input('venue', $venue);
				if ($event_calendar_times == 'yes') {
					set_input('start_time_h', $dtstart['hour']);
					set_input('start_time_m', $dtstart['min']);
				}
				$strdate = mktime(0, 0, 0, $dtstart['month'], $dtstart['day'], $dtstart['year']);
				set_input('start_date', $strdate);					
				if ($event_calendar_times == 'yes') {
					set_input('end_time_h', $dtend['hour']);
					set_input('end_time_m', $dtend['min']);
				}
				$enddate = mktime(0, 0, 0, $dtend['month'], $dtend['day'], $dtend['year']);
				set_input('end_date', $enddate);
				set_input('description', $summary);
				
				if ($event_calendar_region_display == 'yes') {
					set_input('region', $region);
				}
				
				if ($event_calendar_type_display == 'yes') {
					set_input('event_type', $event_type);
				}
				
				set_input('fees', $fees);
				set_input('contact', $contact);
				set_input('organiser', $organiser);
				set_input('event_tags', $event_tags);
				set_input('long_description', $description);
				set_input('www', $www);
				set_input('access', $access_id);
				$result = event_calendar_set_event_from_form();

				if ($result->guid) {
					$event_calendar_autopersonal = get_plugin_setting('autopersonal', 'event_calendar');
					if (!$event_calendar_autopersonal || ($event_calendar_autopersonal == 'yes')) {
						event_calendar_add_personal_event($result->guid, elgg_get_logged_in_user_guid());
					}
					add_to_river('river/object/event_calendar/create', 'create', elgg_get_logged_in_user_guid(), $result->guid);
					system_message(elgg_echo('event_calendar:add_event_response'));
				
					$count++;
				} else {
					register_error(elgg_echo('event_connector:error:failed'));
					forward(REFERER);
				}
			}
		}
	}
}
if($count == 0){
	register_error(elgg_echo('event_connector:error:noevent'));
	forward(REFERER);
}
forward(elgg_get_site_url() . "event_calendar/list");
