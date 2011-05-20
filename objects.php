<?php

$smarty->config_load($conf_file, 'object');

@list($type) = extract_values($podrazdel);

$cache_key = cache_key($type);

if(!$data = load_cache(OBJECT_LISTING, $cache_key))
{
	unset($data);

	// Получаем данные по этому типу объектов
	$rows = $DB->select("
			SELECT g.* {, a.requiredskill1 as ?#} {, a.requiredskill2 as ?#}
				{, l.name_loc?d AS `name_loc`}
			FROM {gameobject_questrelation ?#, } {?_lock ?#, } gameobject_template g
				{LEFT JOIN (locales_gameobject l) ON l.entry=g.entry AND ?d}
			WHERE 
				g.name <> ''
				{ AND g.type = ? } 
				{ AND g.data0=a.lockID AND g.type=3 AND a.type1=2 AND 1=?} 
				{ AND g.data0=a.lockID AND g.type=3 AND a.type2=2 AND 1=?} 
				{ AND a.lockproperties1=2 AND 1=?}
				{ AND a.lockproperties1=3 AND 1=?}
				{ AND a.lockproperties2=1 AND 1=?}
				{ AND g.entry = q.?#}
			ORDER by name
			{LIMIT ?d}
		",
		in_array($type, array(-3, -4)) ? 'skill' : DBSIMPLE_SKIP,
		$type == -5 ? 'skill' : DBSIMPLE_SKIP,
		$_SESSION['locale'] > 0 ? $_SESSION['locale'] : DBSIMPLE_SKIP,
		$type == -2 ? 'q': DBSIMPLE_SKIP,
		in_array($type, array(-3, -4, -5)) ? 'a' : DBSIMPLE_SKIP,
		$_SESSION['locale'] > 0 ? 1: DBSIMPLE_SKIP,
		$type > 0 ? $type : DBSIMPLE_SKIP,
		in_array($type, array(-3, -4)) ? 1 : DBSIMPLE_SKIP,
		$type == -5 ? 1 : DBSIMPLE_SKIP,
		$type == -3 ? 1 : DBSIMPLE_SKIP,
		$type == -4 ? 1 : DBSIMPLE_SKIP,
		$type == -5 ? 1 : DBSIMPLE_SKIP,
		$type == -2 ? 'id' : DBSIMPLE_SKIP,
		$AoWoWconf['limit'] <> 0? $AoWoWconf['limit'] : DBSIMPLE_SKIP
	);


	$i = 0;
	$data = array();
	foreach($rows as $row)
	{
		$data[$i] = array();
		$data[$i]['entry'] = $row['entry'];

		if(isset($row['skill']))
			$data[$i]['skill'] = $row['skill'];

		$data[$i]['name'] = preg_replace('/[\r\n]+/', " ", localizedName($row));
		// TODO: Расположение
		$data[$i]['location'] = "[-1]";
		// Тип объекта
		$data[$i]['type'] = isset($type) ? $type : $row['type'];
		$t_name = trim($data[$i]['name']);
		
		$i++;
	}
	save_cache(OBJECT_LISTING, $cache_key, $data);
}


if(!$object_tot = load_cache(OBJECT_TOT, 'object_tot'))
{
	unset($object_tot);

	// Получаем данные по этому типу объектов
	$object_tot = $DB->select('
			SELECT COUNT(g.entry) as num_objects
			FROM gameobject_template g
			WHERE 
				g.name <> ""
		'
	);
	save_cache(OBJECT_TOT, 'object_tot', $object_tot[0]['num_objects']);
}

global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $smarty->get_config_vars('Objects'),
	'tab' => 0,
	'type' => 0,
	'typeid' => 0,
	'path' => path(0, 5, $type)
);
$smarty->assign('page', $page);

// Передаем массив данных шаблонизатору
$smarty->assign('data', $data);
$smarty->assign('objects_tot',(is_array($object_tot) ? $object_tot[0]['num_objects'] : $object_tot));
// Статистика выполнения mysql запросов
$smarty->assign('mysql', $DB->getStatistics());

$smarty->display('objects.tpl');

?>