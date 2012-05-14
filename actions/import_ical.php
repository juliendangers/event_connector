<?php

	global $CONFIG;
	
	gatekeeper();
		
	// Load Elgg engine
	require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/engine/start.php");
	//Load iCal class library
	require_once($CONFIG->pluginspath . "event_connector/vendors/iCalcreator.class.php");
	require_once($CONFIG->pluginspath . "event_connector/vendors/iCalUtilityFunctions.class.php");

	
	$access_id = (int) get_input("access_id");
	set_input("access_id", $access_id);
	$container_guid = (int) get_input('container_guid', 0);
	$group_guid = 0;
	if ($container_guid != 0) {
		$group_guid = $container_guid;
	}
	
	// must have a file
	if (empty($_FILES['upload']['name'])) {
		register_error(elgg_echo('event_connector:nofile'));
		forward($_SERVER['HTTP_REFERER']);
	}
		
	$name = "";
	if (isset($_FILES['upload']) && $_FILES['upload']['error'] == 0) {
		//$content = file_get_contents($_FILES['upload']['tmp_name']);
        $tmp_name = $_FILES["upload"]["tmp_name"];
        $name = uniqid(time()).$_FILES["upload"]["name"];
        if(!move_uploaded_file($tmp_name, $CONFIG->pluginspath . "event_connector/temp_files/$name")){
			unlink($_FILES['upload']['tmp_name']);
			register_error(elgg_echo('event_connector:error:move'));
			forward($_SERVER['HTTP_REFERER']);
		}
	} else {
		unlink($CONFIG->pluginspath . "event_connector/temp_files/".$name);
		register_error(elgg_echo('event_connector:error:error_upload'));
		forward($_SERVER['HTTP_REFERER']);			
	}
	
	$count = 0;
	$config = array( 'unique_id' => 'human-connect.com', 'delimiter' => '/', 'directory' => $CONFIG->pluginspath . "event_connector/temp_files", 'filename' => $name );
	
	$v = new vcalendar($config);
	$v->parse();
	
	//select next month events
	$eventArray = $v->selectComponents(date('Y'),date('m'),date('d'), date('Y'),date('m')+1);
	foreach( $eventArray as $year => $yearArray) {
		foreach( $yearArray as $month => $monthArray ) {
			foreach( $monthArray as $day => $dailyEventsArray ) {
				foreach( $dailyEventsArray as $vevent ) {
					$dtstart = $vevent->getProperty( 'dtstart' );
					$dtend = $vevent->getProperty( 'dtend' );
					$summary = $vevent->getProperty( 'summary' );
					$description = $vevent->getProperty( 'description' );
					$organizer = $vevent->getProperty( 'organizer' );
					$venue = $vevent->getProperty( 'location' ) ? $vevent->getProperty( 'location' ) : "default";
					//cross plateform exchange
					$region = $fees = $type = $tags = $long_description = "";
					$region = $vevent->getProperty( 'X-PROP-REGION' );
					$fees = $vevent->getProperty( 'X-PROP-FEES' );
					$type = $vevent->getProperty( 'X-PROP-TYPE' );
					$tags = $vevent->getProperty( 'X-PROP-TAGS' );
					set_input('event_action', 'add_event');
					set_input('event_id', 0);
					if($group_guid)
						set_input('group_guid', $group_guid);
					set_input('title',$summary);
					set_input('venue',$venue);
					if ($event_calendar_times == 'yes') {
						set_input('start_time_h',$dtstart['hour']);
						set_input('start_time_m',$dtstart['min']);
					}
					$vo = array("/January/", "/February/", "/March/", "/April/", "/May/", "/June/", "/July/", "/August/", "/September/", "/October/", "/November/", "/December/");
					$vf = array("janvier", "fevrier", "mars", "avril", "mai", "juin", "juillet", "aout", "septembre", "octobre", "novembre", "decembre");
					$strdate = preg_replace($vo,$vf,date('d F Y', mktime(0,0,0,$dtstart['month'],$dtstart['day'],$dtstart['year'])));
					set_input('start_date',$strdate);					
					if ($event_calendar_times == 'yes') {
						set_input('end_time_h',$dtend['hour']);
						set_input('end_time_m',$dtend['min']);
					}
					$enddate = preg_replace($vo,$vf,date('d F Y', mktime(0,0,0,$dtend['month'],$dtend['day'],$dtend['year'])));
					set_input('end_date',$enddate);
					set_input('brief_description',$description);
					
					if ($event_calendar_region_display == 'yes') {
						set_input('region',$region);
					}
					
					if ($event_calendar_type_display == 'yes') {
						set_input('event_type',$event_type);
					}
					
					set_input('fees',$fees);
					set_input('contact',$contact);
					set_input('organiser',$organiser);
					set_input('event_tags',$event_tags);
					set_input('long_description',$long_description);
					set_input('access',$access_id);
					$result = event_calendar_set_event_from_form();
					//print_r($result);exit;
					if ($result->success) {
						$event_calendar_autopersonal = get_plugin_setting('autopersonal', 'event_calendar');
						if (!$event_calendar_autopersonal || ($event_calendar_autopersonal == 'yes')) {
							event_calendar_add_personal_event($result->event->guid,$_SESSION['user']->guid);
						}
						add_to_river('river/object/event_calendar/create','create',$_SESSION['user']->guid,$result->event->guid);
						system_message(elgg_echo('event_calendar:add_event_response'));
					
						$count++;
					} else {
						register_error(elgg_echo('event_connector:error:failed'));
						unlink($CONFIG->pluginspath . "event_connector/temp_files/".$name);
						
						forward($_SERVER['HTTP_REFERER']);
					}
				}
			}
		}
	}
	unlink($CONFIG->pluginspath . "event_connector/temp_files/".$name);
	
	if($count == 0){
		register_error(elgg_echo('event_connector:error:noevent'));
		forward($_SERVER['HTTP_REFERER']);
	}
	forward($CONFIG->wwwroot . "pg/event_calendar/");
	