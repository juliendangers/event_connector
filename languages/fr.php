<?php

$french = array(
	'event_connector:menu:one' => 'Exporter vers iCal, Outlook, G Calendar',
	'event_connector:menu:all' => 'Exporter tous les évènements vers iCal, Outlook, G Calendar',
	
	'event_connector:upload:file' => "Fichier iCal (.ics)",
	'event_connector:upload' => "Importer un ou des évènements",
	
	'event_connector:events:saved' => "Evènements ajoutés",
	'event_connector:event:saved' => "Evènement ajouté",
	
	'event_connector:import:title' => 'Importer un fichier iCal',
	'event_connector:import' => "Importer des évènements",
	
	'event_connector:timezone' => "Choisissez votre fuseau horaire",
	'event_connector:no_event' => 'Aucun évènement trouvé',
	
	'event_connector:activation:failed' => "Vous devez activer le plugin elgg_calendar",
	'event_connector:enabled' => "Vous devez activer le plugin event_connector",

	'event_connector:no_such_event' => "Aucun évènement correspondant",
	'event_connector:error:noevent' => 'Aucun évènement dans le fichier',
	'event_connector:error:notactivated' => "Vous devez activer le plugin event_connector",
	'event_connector:error:group_calendar' => "Le calendrier n'est pas activé pour ce groupe",
	'event_connector:error:failed' => "Une erreur est survenue, impossible d'importer les évènements",
	'event_connector:error:error_upload' => "Impossible d'uploader le fichier",
	'event_connector:error:format' => "Fichier invalide",
);
				
add_translation("fr", $french);
