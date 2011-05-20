<?php

// Необходима функция creatureinfo
require_once('includes/allnpcs.php');

$smarty->config_load($conf_file, 'npc');

@list($type) = extract_values($podrazdel);

$cache_key = cache_key($type);

if(!$npcs = load_cache(NPC_LISTING, $cache_key))
{
	unset($npcs);

	$rows = $DB->select('
		SELECT c.?#, c.entry
		{
			, l.name_loc?d 
			, l.subname_loc?d 
		}
		FROM ?_factiontemplate, creature_template c
		{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
		WHERE
			factiontemplateID=faction_A
			{AND type=?}
		ORDER BY minlevel DESC, name
		{LIMIT ?d}
		',
		$npc_cols[0],
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		($type!='')? $type: DBSIMPLE_SKIP,
		($AoWoWconf['limit']!=0)? $AoWoWconf['limit']: DBSIMPLE_SKIP
	);

	$npcs = array();
	foreach($rows as $row)
		$npcs[] = creatureinfo2($row);
	save_cache(NPC_LISTING, $cache_key, $npcs);
}

if(!$npc_tot = load_cache(NPC_TOT, 'npc_tot'))
{
	unset($npc_tot);

	$npc_tot = $DB->select('
		SELECT COUNT(entry) as npc_tot
		FROM creature_template c
		'
	);
	
	save_cache(NPC_TOT, 'npc_tot', $npc_tot);
}

global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $smarty->get_config_vars('NPCs'),
	'tab' => 0,
	'type' => 0,
	'typeid' => 0,
	'path' => path(0, 4, $type)
);
$smarty->assign('page', $page);

$smarty->assign('npcs', $npcs);
$smarty->assign('npc_tot',(is_array($npc_tot) ? $npc_tot[0]['npc_tot'] : $npc_tot));
// Количество MySQL запросов
$smarty->assign('mysql', $DB->getStatistics());
// Загружаем страницу
$smarty->display('npcs.tpl');

?>