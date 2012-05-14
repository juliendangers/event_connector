<?php

// Load Elgg engine
require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");

if(!is_plugin_enabled("event_connector"))
{	
	register_error(elgg_echo('event_connector:activation:failed'));
	forward();
}

//Load iCal class library
require_once("vendors/iCalcreator.class.php");
require_once("vendors/iCalUtilityFunctions.class.php");


$event_id = get_input('event_id', 0);
if($event_id == 0) {
	register_error(elgg_echo('event_connector:no_event'));
	forward($_SERVER['HTTP_REFERER']);
}
	
$timezone = get_plugin_setting('timezone', 'event_connector');

$config = array( 'UNIQUE_ID' => 'human-connect.com', 'FILENAME'=> 'ElggCalendar.ics', 'TZID' => $timezone );

$v = new vcalendar($config);


$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( "X-WR-TIMEZONE", "Europe/Paris" );
$v->setProperty( "calscale", "GREGORIAN" );
$v->setProperty( "version", "2.0" );

if(isloggedin())
	$v->setProperty( "X-WR-CALNAME", get_loggedin_user()->username. "Calendar" );
else
	$v->setProperty( "X-WR-CALNAME", "Elgg Calendar" );

iCalUtilityFunctions::createTimezone(&$v, $timezone);

if($event = get_entity($event_id)) {
	//set default beginning and ending time
	$hb = 8; $he = 18;
	$mb = $me = 0;
	if($event->start_time) {
		$hb= (int)($event->start_time/60);
		$mb = $event->start_time%60;
	}
	
	if($event->end_time) {
		$he = (int)($event->end_time/60);
		$me = $event->end_time%60;
	}
	
	$vevent = $v->newComponent('vevent');
	
	if (isloggedin()) {
		if (event_calendar_has_personal_event($event_id,$_SESSION['user']->getGUID()))
			$confirmed = true;
	}
	if(!isset($event->end_date)) $event->end_date = $event->start_date;
	$start = array( 'year'=>date('Y', $event->start_date), 'month'=>date('m', $event->start_date), 'day'=>date('d', $event->start_date), 'hour'=>$hb, 'min'=>$mb, 'sec'=>0);
	$vevent->setProperty( 'dtstart', $start );
	$end = array( 'year'=>date('Y', $event->end_date), 'month'=>date('m', $event->end_date), 'day'=>date('d', $event->end_date), 'hour'=>$he, 'min'=>$me, 'sec'=>0 );
	$vevent->setProperty( 'dtend', $end );
	$vevent->setProperty( 'LOCATION', $event->venue );
	$vevent->setProperty( 'LAST_MODIFIED', $event->time_updated );
	$vevent->setProperty( 'summary', $event->title );
	$description = isset($event->description) && $event->description != "" ? $event->description : null;
	if(!$description  && $event->long_description)
		$description = $event->long_description;
	$vevent->setProperty( 'description',  $description);
	//doesn't display corectly in iCal
	//$vevent->setOrganizer(get_entity($event->owner_guid)->username.'MAILTO:'.get_entity($event->owner_guid)->email);
	
	$v->returnCalendar();
	
} else {
	register_error(elgg_echo('event_connector:no_such_event'));
	forward($_SERVER['HTTP_REFERER']);	
}