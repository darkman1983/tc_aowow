<?php
require_once('includes/allevents.php');
require_once('includes/allcomments.php');
require_once('includes/allnpcs.php');
require_once('includes/allobjects.php');
require_once('includes/allquests.php');

$smarty->config_load($conf_file, 'event');

$id = intval($podrazdel);

/// Warning: Cache is disabled here because dates are recalculated every time.
//$cache_key = cache_key($id);
//if(!$event = load_cache(EVENT_PAGE, $cache_key))
{
	$event = event_description($id);
	if ($event)
	{
		if ($event['npcs_guid'] &&
		    ($ids = $DB->selectCol('SELECT id FROM creature WHERE guid IN (?a) GROUP BY id', $event['npcs_guid'])))
		{
			$event['npcs'] = array();
			foreach ($ids as $crid)
				$event['npcs'][] = creatureinfo($crid);
		}

		if ($event['objects_guid'] &&
		    ($ids = $DB->selectCol('SELECT id FROM gameobject WHERE guid IN (?a) GROUP BY id', $event['objects_guid'])))
		{
			$event['objects'] = array();
			foreach ($ids as $goid)
				$event['objects'][] = objectinfo($goid);
		}

		if ($event['creatures_quests_id'])
		{
			$event['quests'] = array();
			foreach ($event['creatures_quests_id'] as $qid)
				$event['quests'][] = GetDBQuestInfo($qid['quest'], 0xFFFFFF);
		}

//		save_cache(EVENT_PAGE, $cache_key, $achievement);
	}
}
global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $event['name'].' - '.$smarty->get_config_vars('Event'),
	'tab' => 0,
	'type' => 11,
	'typeid' => $event['id'],
	'path' => path(0, 11)
);
$smarty->assign('page', $page);

// Комментарии
$smarty->assign('comments', getcomments($page['type'], $page['typeid']));
// Статистика выполнения mysql запросов
$smarty->assign('mysql', $DB->getStatistics());
$smarty->assign('event', $event);
// Загружаем страницу
$smarty->display('event.tpl');
?>
