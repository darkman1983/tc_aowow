<?php

// Необходима функция iteminfo
require_once('includes/game.php');
require_once('includes/allspells.php');
require_once('includes/allquests.php');
require_once('includes/allitems.php');
require_once('includes/allnpcs.php');
require_once('includes/allobjects.php');

// Настраиваем Smarty ;)
$smarty->config_load($conf_file, 'search');

// Строка поиска:
$search = urldecode($podrazdel);
$nsearch = '%'.$search.'%';
$smarty->assign('search', $search);

// Подключаемся к ДБ
global $DB;
global $allitems;
global $allspells;

global $npc_cols;
global $spell_cols;

// Массив всего найденного
$found = array();

if (!empty($podrazdel))
{
// Ищем вещи:
if($_SESSION['locale']>0)
{
	$m = $DB->selectCol('
			SELECT entry
			FROM locales_item
			WHERE name_loc?d LIKE ?
		',
		$_SESSION['locale'],
		$nsearch
	);
}

$rows = $DB->select('
		SELECT i.?#
			{, l.name_loc?d AS `name_loc`}
		FROM ?_icons a, item_template i
			{LEFT JOIN (locales_item l) ON l.entry=i.entry AND ?d}
		WHERE
			(i.name LIKE ? {OR i.entry IN (?a)})
			AND a.id = i.displayid;
	',
	$item_cols[3],
	($m)? $_SESSION['locale']: DBSIMPLE_SKIP,
	($m)? 1: DBSIMPLE_SKIP,
	$nsearch,
	($m)? $m: DBSIMPLE_SKIP
);
unset($m);
foreach($rows as $row)
	$found['item'][] = iteminfo2($row);

// Ищем NPC:
if($_SESSION['locale']>0)
{
	$m = $DB->selectCol('
			SELECT entry
			FROM locales_creature
			WHERE
				name_loc?d LIKE ?
				OR subname_loc?d LIKE ?
		',
		$_SESSION['locale'], $nsearch,
		$_SESSION['locale'], $nsearch
	);
}
$rows = $DB->select('
		SELECT ?#, c.entry
			{, l.name_loc?d AS `name_loc`,
			l.subname_loc'.$_SESSION['locale'].' AS `subname_loc`}
		FROM ?_factiontemplate, creature_template c
			{LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ?d}
		WHERE
			(name LIKE ?
			OR subname LIKE ?
			{OR c.entry IN (?a)})
			AND factiontemplateID=faction_A
	',
	$npc_cols[0],
	($m)? $_SESSION['locale']: DBSIMPLE_SKIP,
	($m)? 1: DBSIMPLE_SKIP,
	$nsearch, $nsearch,
	($m)? $m: DBSIMPLE_SKIP
);
unset($m);
foreach($rows as $row)
	$found['npc'][] = creatureinfo2($row);

// Ищем объекты
if($_SESSION['locale']>0)
{
	$m = $DB->selectCol('
			SELECT entry
			FROM locales_gameobject
			WHERE
				name_loc?d LIKE ?
		',
		$_SESSION['locale'], $nsearch
	);
}
$rows = $DB->select('
		SELECT g.?#
			{, l.name_loc?d AS `name_loc`}
		FROM gameobject_template g
			{LEFT JOIN (locales_gameobject l) ON l.entry=g.entry AND ?d}
		WHERE name LIKE ? {OR g.entry IN (?a)}
	',
	$object_cols[0],
	($m)? $_SESSION['locale']: DBSIMPLE_SKIP,
	($m)? 1: DBSIMPLE_SKIP,
	$nsearch,
	($m)? $m: DBSIMPLE_SKIP
);
unset($m);
foreach($rows as $row)
	$found['object'][] = objectinfo2($row);

// Ищем квесты
if($_SESSION['locale']>0)
{
	$m = $DB->selectCol('
			SELECT Id
			FROM locales_quest
			WHERE
				Title_loc?d LIKE ?
		',
		$_SESSION['locale'], $nsearch
	);
}
$rows = $DB->select("
		SELECT *
			{, l.Title_loc?d AS `Title_loc`}
		FROM v_quest_template q
			{LEFT JOIN (locales_quest l) ON l.Id=q.entry AND ?d}
		WHERE Title LIKE ? {OR q.entry IN (?a)}
		AND q.Title NOT IN ('','----','?????')
		AND q.Title NOT LIKE '<DEPRECATED>%'
		AND q.Title NOT LIKE '<NYI>%'
		AND q.Title NOT LIKE '<nyi>%'
		AND q.Title NOT LIKE '<TEST>%'
		AND q.Title NOT LIKE '<TXT>%'
		AND q.Title NOT LIKE '<UNUSED%'
	",
	($m)? $_SESSION['locale']: DBSIMPLE_SKIP,
	($m)? 1: DBSIMPLE_SKIP,
	$nsearch,
	($m)? $m: DBSIMPLE_SKIP
);
unset($m);
foreach($rows as $row)
	$found['quest'][] = GetQuestInfo($row, 0xFFFFFF);

// Ищем наборы вещей
$rows = $DB->select('
		SELECT *
		FROM ?_itemset
		WHERE name_loc'.$_SESSION['locale'].' LIKE ?
	',
	$nsearch
);
foreach($rows as $row)
	$found['itemset'][] = itemsetinfo2($row);

// Ищем спеллы
$rows = $DB->select('
		SELECT ?#, spellID
		FROM ?_spell s, ?_spellicons i
		WHERE
			s.spellname_loc'.$_SESSION['locale'].' like ?
			AND i.id = s.spellicon
	',
	$spell_cols[2],
	$nsearch
);
foreach($rows as $row)
	$found['spell'][] = spellinfo2($row);
}

$keys = array_keys($found);

// Если найден один элемент - перенаправляем на него
if(count($found) == 1 && count($found[$keys[0]]) == 1)
{
	header("Location: ?".$keys[0].'='.$found[$keys[0]][0]['entry']);
}
else
{
	$smarty->assign('found', $found);

	// Параметры страницы
	$page = array();
	// Номер вкладки меню
	$page['tab'] = 0;
	// Заголовок страницы
	$page['Title'] = $search.' - '.$smarty->get_config_vars('Search');
	$smarty->assign('page', $page);

	$smarty->assign('mysql', $DB->getStatistics());
	$smarty->assign('search', $search);

	$smarty->display('search.tpl');
}

?>