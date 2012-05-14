<?php
	/***
	*	Elgg event_connector plugin. Easily import or export iCal calendar
	*	Copyright (C) 2010-2011 Crestin julien
	*	
	*	This program is free software; you can redistribute it and/or
	*	modify it under the terms of the GNU General Public License
	*	as published by the Free Software Foundation; either version 2
	*	of the License, or (at your option) any later version.
	*	
	*	This program is distributed in the hope that it will be useful,
	*	but WITHOUT ANY WARRANTY; without even the implied warranty of
	*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*	GNU General Public License for more details.
	*	
	*	You should have received a copy of the GNU General Public License
	*	along with this program; If not, see <http://www.gnu.org/licenses/>
	*
	* 	@author Julien Crestin / Human Connect <jcrestin@human-connect.com>
	*	@version 0.5
	*	@link author http://blog.juliencrestin.com
	*	@license http://www.gnu.org/licenses/gpl-2.0.html
	*
	***/

	function event_connector_init()
	{
		global $CONFIG;
		if(!is_plugin_enabled("event_calendar"))
		{	
			register_error(elgg_echo('event_connector:activation:failed'));
			disable_plugin("event_connector");
		}
	}
	
	function event_connector_pagesetup() {
		global $CONFIG;
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
				add_submenu_item(elgg_echo('event_connector:menu:one'), $CONFIG->wwwroot . "mod/event_connector/export_event.php?event_id={$event_id}");
			}
			else {
				add_submenu_item(elgg_echo('event_connector:menu:all'), $CONFIG->wwwroot . "mod/event_connector/export_events.php?{$param}");
			}
			add_submenu_item(elgg_echo('event_connector:import:title'), $CONFIG->wwwroot . "mod/event_connector/import.php");
		} else if(get_context() == 'groups' && strpos($_SERVER['REQUEST_URI'], 'event_calendar/') && !(strpos($_SERVER['REQUEST_URI'], '/manage_event')) && !(strpos($_SERVER['REQUEST_URI'], '/delete_confirm')) && !(strpos($_SERVER['REQUEST_URI'], '/event_connector'))) {
			if($event_id) {
				add_submenu_item(elgg_echo('event_connector:menu:title'), $CONFIG->wwwroot . "mod/event_connector/export_event.php?event_id={$event_id}");
			}
			else {
				add_submenu_item(elgg_echo('event_connector:menu:title'), $CONFIG->wwwroot . "mod/event_connector/export_events.php?{$param}");
			}
			$group = (int)get_input('group_guid');
			add_submenu_item(elgg_echo('event_connector:import:title'), $CONFIG->wwwroot . "mod/event_connector/import.php?group_guid={$group}");
		}
	}

	register_elgg_event_handler('init','system','event_connector_init');
	register_elgg_event_handler('pagesetup', 'system', 'event_connector_pagesetup');
	
	// Register actions
	register_action("import_ical", false, $CONFIG->pluginspath . "event_connector/actions/import_ical.php");
?>