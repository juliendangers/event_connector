<?php
/**
 * Import an iCal
 *
 * @package ElggEventConnector
 */

gatekeeper();

$container_guid = (int) get_input('guid');
$container = get_entity($container_guid);
if (!$container) {
	forward(REFERER);
}

if (elgg_instanceof($container, 'group') && $container->event_calendar_enable == "no") {
	register_error(elgg_echo('event_connector:error:group_calendar'));
	forward(REFERER);
}

elgg_set_page_owner_guid($container->getGUID());

$title = elgg_echo('event_connector:upload');
elgg_push_breadcrumb($title);

$vars = array('container_guid' => $container_guid);
$content = elgg_view_form('event_connector/import', array(), $vars);

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);
