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

$event = '';

$event_calendar_first_date = trim(get_plugin_setting('first_date', 'event_calendar'));
$event_calendar_last_date = trim(get_plugin_setting('last_date', 'event_calendar'));

$original_start_date = get_input('start_date',date('Y-m-d'));
if ( $event_calendar_first_date && ($original_start_date < $event_calendar_first_date) ) {
	$original_start_date = $event_calendar_first_date;
}
if ( $event_calendar_last_date && ($original_start_date > $event_calendar_last_date) ) {
	$original_start_date = $event_calendar_first_date;
}

// the default interval is one month
$day = 60*60*24;
$week = 7*$day;
$month = 31*$day;

$mode = trim(get_input('mode',''));

if ($mode == "day") {
	$start_date = $original_start_date;
	$end_date = $start_date;
	$start_ts = strtotime($start_date);
	$end_ts = strtotime($end_date)+$day-1;
} else if ($mode == "week") {
	$start_ts = strtotime($original_start_date);
	$start_ts -= date("w",$start_ts)*$day;
	$end_ts = $start_ts + 6*$day;
	$start_date = date('Y-m-d',$start_ts);
	$end_date = date('Y-m-d',$end_ts);
} else {
	$start_ts = strtotime($original_start_date);
	$month = date('m',$start_ts);
	$year = date('Y',$start_ts);
	$start_date = $year.'-'.$month.'-1';
	$end_date = $year.'-'.$month.'-'.getLastDayOfMonth($month,$year);
}

if ($event_calendar_first_date && ($start_date < $event_calendar_first_date))
	$start_date = $event_calendar_first_date;

if ($event_calendar_last_date && ($end_date > $event_calendar_last_date))
	$end_date = $event_calendar_last_date;

$start_ts = strtotime($start_date);

if ($mode == "day") {
	$end_ts = strtotime($end_date)+$day-1;
} else if ($mode == "week") {
	$end_ts = $start_ts + 6*$day;
} else {
	$end_ts = strtotime($end_date);
}

$group_guid = (int) get_input('group_guid',0);


$offset = (int) get_input('offset',0);
$limit = 10;
$filter = get_input('filter','all');
$region = get_input('region','-');
$events = array();
if ($filter == 'all') {
	$count = event_calendar_get_events_between($start_ts,$end_ts,true,$limit,$offset,$group_guid,$region);
	$events = event_calendar_get_events_between($start_ts,$end_ts,false,$limit,$offset,$group_guid,$region);
} else if ($filter == 'friends') {
	$user_guid = get_loggedin_userid();
	$count = event_calendar_get_events_for_friends_between($start_ts,$end_ts,true,$limit,$offset,$user_guid,$group_guid,$region);
	$events = event_calendar_get_events_for_friends_between($start_ts,$end_ts,false,$limit,$offset,$user_guid,$group_guid,$region);	
} else if ($filter == 'mine') {
	$user_guid = get_loggedin_userid();
	$count = event_calendar_get_events_for_user_between($start_ts,$end_ts,true,$limit,$offset,$user_guid,$group_guid,$region);
	$events = event_calendar_get_events_for_user_between($start_ts,$end_ts,false,$limit,$offset,$user_guid,$group_guid,$region);	
}

if(!$events) {		
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

foreach($events as $event){
	//set default beginning and ending time
	$hb = 8; $he = 18;
	$mb = $me = $sb = $se = 0;
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
	$start = array( 'year'=>date('Y', $event->start_date), 'month'=>date('m', $event->start_date), 'day'=>date('d', $event->start_date), 'hour'=>$hb, 'min'=>$mb, 'sec'=>$sb);
	$vevent->setProperty( 'dtstart', $start );
	$end = array( 'year'=>date('Y', $event->end_date), 'month'=>date('m', $event->end_date), 'day'=>date('d', $event->end_date), 'hour'=>$he, 'min'=>$me, 'sec'=>$se );
	$vevent->setProperty( 'dtend', $end );
	$vevent->setProperty( 'LOCATION', $event->venue );
	$vevent->setProperty( 'LAST_MODIFIED', $event->time_updated );
	$vevent->setProperty( 'summary', $event->title );
	$description = isset($event->description) && $event->description != "" ? $event->description : null;
	if(!$description  && $event->long_description)
		$description = $event->long_description;
	$vevent->setProperty( 'description',  $description);
	//doesn't display correctly in iCal
	//$vevent->setOrganizer(get_entity($event->owner_guid)->username.':MAILTO:'.get_entity($event->owner_guid)->email);
		
}

	$v->returnCalendar();
	
	
function getLastDayOfMonth($month,$year) {
	return idate('d', mktime(0, 0, 0, ($month + 1), 0, $year));
}