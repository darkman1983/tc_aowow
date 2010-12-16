<?php

require_once('includes/allitemsets.php');

$smarty->config_load($conf_file, 'itemset');

$cache_key = cache_key();

if(!$itemsets = load_cache(ITEMSET_LISTING, $cache_key))
{
	unset($itemsets);

	$rows = $DB->select('
		SELECT ?#
		FROM ?_itemset
		ORDER by name_loc'.$_SESSION['locale'].'
		{LIMIT ?d}',
		$itemset_col[0],
		($AoWoWconf['limit']!=0)? $AoWoWconf['limit']: DBSIMPLE_SKIP
	);

	$itemsets = array();
	foreach($rows as $row)
		$itemsets[] = itemsetinfo2($row);

	save_cache(ITEMSET_LISTING, $cache_key, $itemsets);
}
$smarty->assign('itemsets', $itemsets);

global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $smarty->get_config_vars('Item_Sets'),
	'tab' => 0,
	'type' => 0,
	'typeid' => 0,
	'path' => '[0, 2]'
);
$smarty->assign('page', $page);

// --Передаем данные шаблонизатору--
// Количество MySQL запросов
$smarty->assign('mysql', $DB->getStatistics());
// Запускаем шаблонизатор
$smarty->display('itemsets.tpl');

?>