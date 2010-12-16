<?php
require_once('includes/alllocales.php');

// Для списка creatureinfo()
$npc_cols[0] = array('name', 'subname', 'minlevel', 'maxlevel', 'type', 'rank', 'A','H');
$npc_cols[1] = array('subname', 'minlevel', 'maxlevel', 'type', 'rank', /*'minhealth', 'maxhealth', 'minmana', 'maxmana',*/ 'mingold', 'maxgold', 'lootid', 'skinloot', 'pickpocketloot', 'spell1', 'spell2', 'spell3', 'spell4', 'A', 'H', 'mindmg', 'maxdmg', 'attackpower', 'dmg_multiplier', /*'armor',*/ 'difficulty_entry_1');

// Функция информации о создании
function creatureinfo2($Row)
{
	$creature = array(
		'entry'				=> $Row['entry'],
		'name'				=> str_replace(' (1)', LOCALE_HEROIC, localizedName($Row)), // FIXME
		'subname'			=> localizedName($Row, 'subname'),
		'minlevel'			=> $Row['minlevel'],
		'maxlevel'			=> $Row['maxlevel'],
		'react'				=> $Row['A'].','.$Row['H'],
		'type'				=> $Row['type'],
		'classification'	=> $Row['rank']
	);

	return $creature;
}

// Функция информации о создании
function creatureinfo($id)
{
	global $DB;
	global $npc_cols;
	$row = $DB->selectRow('
			SELECT ?#, c.entry
			{
				, l.name_loc'.$_SESSION['locale'].' as `name_loc`
				, l.subname_loc'.$_SESSION['locale'].' as `subname_loc`
				, ?
			}
			FROM ?_factiontemplate, creature_template c
			{
				LEFT JOIN (locales_creature l)
				ON l.entry=c.entry AND ?
			}
			WHERE
				c.entry = ?d
				AND factiontemplateID = faction_A
			LIMIT 1
		',
		$npc_cols[0],
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$id
	);
	return creatureinfo2($row);
}

?>