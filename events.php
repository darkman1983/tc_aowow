<?php
require_once('includes/allevents.php');

$smarty->config_load($conf_file, 'event');

@list($category) = extract_values($podrazdel);

/// Warning: Cache is disabled here because dates are recalculated every time.
//$cache_key = cache_key($category);
//if(!$events = load_cache(EVENTS_LISTING, $cache_key))
{
	$events = array();
	$events['data'] = event_infoline(event_find());
	//save_cache(EVENTS_LISTING, $cache_key, $events);
}
global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => ($events['category']?($events['category'].' - '):'').$smarty->get_config_vars('Events'),
	'tab' => 0,
	'type' => 11,
	'typeid' => 0,
	'path' => path(0, 11)
);
$smarty->assign('page', $page);

// Статистика выполнения mysql запросов
$smarty->assign('mysql', $DB->getStatistics());
$smarty->assign('events', $events);
// Загружаем страницу
$smarty->display('events.tpl');
?>
