<?php

require_once('includes/game.php');
require_once('includes/allspells.php');
require_once('includes/allquests.php');
require_once('includes/allitems.php');
require_once('includes/allnpcs.php');
require_once('includes/allobjects.php');
require_once('includes/allcomments.php');
require_once('includes/allachievements.php');

// Загружаем файл перевода для smarty
$smarty->config_load($conf_file, 'item');

$id = intval($podrazdel);

$cache_key = cache_key($id);

if(!$item = load_cache(ITEM_PAGE, $cache_key))
{
	unset($item);

	// Информация о вещи...
	$item = iteminfo($id, 1);

	// Поиск мобов с которых эта вещь лутится
	$drops_cr = drop('creature_loot_template', $item['entry']);
	if($drops_cr)
	{
		$item['droppedby'] = array();
		foreach($drops_cr as $lootid => $drop)
		{
			$rows = $DB->select('
				SELECT c.?#, c.entry
				{
					, l.name_loc?d AS name_loc
					, l.subname_loc?d AS subname_loc
				}
				FROM ?_factiontemplate, creature_template c
				{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
				WHERE
					lootid=?d
					AND factiontemplateID=faction_A
				',
				$npc_cols[0],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['droppedby'][] = @array_merge(creatureinfo2($row), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_cr);

	// Поиск объектов, из которых лутится эта вещь
	$drops_go = drop('gameobject_loot_template', $item['entry']);
	if($drops_go)
	{
		$item['containedinobject'] = array();
		$item['minedfromobject'] = array();
		$item['gatheredfromobject'] = array();
		foreach($drops_go as $lootid => $drop)
		{
			// Сундуки
			$rows = $DB->select('
					SELECT g.entry, g.name, g.type, a.lockproperties1 {, l.name_loc?d AS name_loc}
					FROM gameobject_template g LEFT JOIN ?_lock a ON a.lockID=g.data0
					{ LEFT JOIN (locales_gameobject l) ON l.entry=g.entry AND ? }
					WHERE
						g.data1=?d
						AND g.type IN (?d, ?d)
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid,
				GAMEOBJECT_TYPE_CHEST,
				GAMEOBJECT_TYPE_FISHINGHOLE
			);
			foreach($rows as $row)
			{
				// Залежи руды
				if($row['lockproperties1'] == LOCK_PROPERTIES_MINING)
					$item['minedfromobject'][] = @array_merge(objectinfo2($row), $drop);
				// Собирается с трав
				elseif($row['lockproperties1'] == LOCK_PROPERTIES_HERBALISM)
					$item['gatheredfromobject'][] = @array_merge(objectinfo2($row), $drop);
				// Сундуки
				else
					$item['containedinobject'][] = @array_merge(objectinfo2($row), $drop);
			}
		}

		if(!$item['containedinobject'])
			unset($item['containedinobject']);
		if(!$item['minedfromobject'])
			unset($item['minedfromobject']);
		if(!$item['gatheredfromobject'])
			unset($item['gatheredfromobject']);

		unset($rows);
	}
	unset($drops_go);

	// Поиск вендеров, которые эту вещь продают
	$rows_soldby = $DB->select('
			SELECT ?#, c.entry, v.ExtendedCost, v.maxcount AS stock
			{
				, l.name_loc?d AS name_loc
				, l.subname_loc?d AS subname_loc
			}
			FROM npc_vendor v, ?_factiontemplate, creature_template c
			{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
			WHERE
				v.item=?d
				AND c.entry=v.entry
				AND factiontemplateID=faction_A
			ORDER BY 1 DESC, 2 DESC
		',
		$npc_cols['0'],
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$item['entry']
	);
	if($rows_soldby)
	{
		$item['soldby'] = array();
		foreach($rows_soldby as $i => $row)
		{
			$item['soldby'][$i] = array();
			$item['soldby'][$i] = creatureinfo2($row);
			$item['soldby'][$i]['stock'] = ($row['stock'] == 0 ? -1 : $row['stock']);
			if($row['ExtendedCost'])
			{
				$item['soldby'][$i]['cost'] = array();
				$extcost = $DB->selectRow('SELECT * FROM ?_item_extended_cost WHERE extendedcostID=?d LIMIT 1', abs($row['ExtendedCost']));
				if($extcost['reqhonorpoints'])
					$item['soldby'][$i]['cost']['honor'] = ($row['A'] == 1 ? 1 : -1) * $extcost['reqhonorpoints'];
				if($extcost['reqarenapoints'])
					$item['soldby'][$i]['cost']['arena'] = $extcost['reqarenapoints'];
				$item['soldby'][$i]['cost']['items'] = array();
				for ($j=1;$j<=5;$j++)
					if(($extcost['reqitem'.$j]>0) and ($extcost['reqitemcount'.$j]>0))
					{
						allitemsinfo($extcost['reqitem'.$j], 0);
						$item['soldby'][$i]['cost']['items'][] = array('item' => $extcost['reqitem'.$j], 'count' => $extcost['reqitemcount'.$j]);
					}
			}
			else
				$item['soldby'][$i]['cost']['money'] = $item['BuyPrice'];
		}
		unset($extcost);
	}
	unset($rows_soldby);

	// Поиск квестов, для выполнения которых нужен этот предмет
	$rows_qr = $DB->select('
			SELECT q.?# {, l.Title_loc?d AS Title_loc}
			FROM v_quest_template q
			{ LEFT JOIN (locales_quest l) ON l.Id=q.entry AND ? }
			WHERE
				RequiredItemId1=?d
				OR RequiredItemId2=?d
				OR RequiredItemId3=?d
				OR RequiredItemId4=?d
		',
		$quest_cols[2],
		$_SESSION['locale'] > 0 ? $_SESSION['locale'] : DBSIMPLE_SKIP,
		$_SESSION['locale'] > 0 ? 1 : DBSIMPLE_SKIP,
		$item['entry'], $item['entry'], $item['entry'], $item['entry']
	);
	if($rows_qr)
	{
		$item['objectiveof'] = array();
		foreach($rows_qr as $row)
			$item['objectiveof'][] = GetQuestInfo($row, 0xFFFFFF);
	}
	unset($rows_qr);

	// Поиск квестов, при взятии которых выдается этот предмет
	$rows_qp = $DB->select('
			SELECT q.?# {, l.Title_loc?d AS Title_loc}
			FROM v_quest_template q
			{ LEFT JOIN (locales_quest l) ON l.Id=q.entry AND ? }
			WHERE SourceItemId=?d
		',
		$quest_cols[2],
		$_SESSION['locale'] > 0 ? $_SESSION['locale'] : DBSIMPLE_SKIP,
		$_SESSION['locale'] > 0 ? 1 : DBSIMPLE_SKIP,
		$item['entry']
	);
	if($rows_qp)
	{
		$item['providedfor'] = array();
		foreach($rows_qp as $row)
			$item['providedfor'][] = GetQuestInfo($row, 0xFFFFFF);
	}
	unset($rows_qp);

	// Поиск квестов, наградой за выполнение которых, является этот предмет
	$rows_qrw = $DB->select('
			SELECT q.?# {, l.Title_loc?d AS Title_loc}
			FROM v_quest_template q 
			{ LEFT JOIN (locales_quest l) ON l.Id=q.entry AND ? }
			WHERE
				RewardItemId1=?d
				OR RewardItemId2=?d
				OR RewardItemId3=?d
				OR RewardItemId4=?d
				OR RewardChoiceItemId1=?d
				OR RewardChoiceItemId2=?d
				OR RewardChoiceItemId3=?d
				OR RewardChoiceItemId4=?d
				OR RewardChoiceItemId5=?d
				OR RewardChoiceItemId6=?d
		',
		$quest_cols[2],
		($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
		$item['entry'], $item['entry'], $item['entry'], $item['entry'], $item['entry'],
		$item['entry'], $item['entry'], $item['entry'], $item['entry'], $item['entry']
	);
	if($rows_qrw)
	{
		$item['rewardof'] = array();
		foreach($rows_qrw as $row)
			$item['rewardof'][] = GetQuestInfo($row, 0xFFFFFF);
	}
	unset($rows_qrw);

	// Поиск квестов, в награду за выполнение которых итем присылают почтой
	$drops_qm = drop('mail_loot_template', $item['entry']);
	if($drops_qm)
	{
		foreach($drops_qm as $lootid => $row)
		{
			$rows_qm = $DB->select('
					SELECT q.?# {, l.Title_loc?d AS Title_loc}
					FROM v_quest_template q
					{ LEFT JOIN (locales_quest l) ON l.Id=q.entry AND ? }
					WHERE RewardMailTemplateId=?d
				',
				$quest_cols[2],
				$_SESSION['locale'] > 0 ? $_SESSION['locale'] : DBSIMPLE_SKIP,
				$_SESSION['locale'] > 0 ? 1 : DBSIMPLE_SKIP,
				$lootid
			);
			if($rows_qm)
			{
				if (!isset($item['rewardof']))
					$item['rewardof'] = array();
				foreach($rows_qm as $row)
					$item['rewardof'][] = GetQuestInfo($row, 0xFFFFFF);
			}
			unset($rows_qm);
		}
	}
	unset($drops_qm);

	// Поиск вещей, в которых находятся эти вещи
	$drops_cii = drop('item_loot_template', $item['entry']);
	if($drops_cii)
	{
		$item['containedinitem'] = array();
		foreach($drops_cii as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry, maxcount
					{ , l.name_loc?d AS name_loc }
					FROM ?_icons, item_template c
					{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
					WHERE
						c.entry=?d
						AND id=displayid
				',
				$item_cols[2],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['containedinitem'][] = @array_merge(iteminfo2($row, 0), $drop);
		}
		unset($drops_cii);
		unset($rows);
		unset($lootid);
		unset($drop);
	}

	// Какие вещи содержатся в этой вещи
	if(!($item['contains'] = loot('item_loot_template', $item['entry'])))
		unset($item['contains']);

	// Поиск созданий, у которых воруется вещь
	$drops_pp = drop('pickpocketing_loot_template', $item['entry']);
	if($drops_pp)
	{
		$item['pickpocketingloot'] = array();
		foreach($drops_pp as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry
					{
						, l.name_loc?d AS name_loc
						, l.subname_loc?d AS subname_loc
					}
					FROM ?_factiontemplate, creature_template c
					{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
					WHERE
						pickpocketloot=?d
						AND factiontemplateID=faction_A
				',
				$npc_cols[0],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['pickpocketingloot'][] = @array_merge(creatureinfo2($row), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_pp);

	// Поиск созданий, с которых сдираеццо эта шкура
	$drops_sk = drop('skinning_loot_template', $item['entry']);
	if($drops_sk)
	{
		$item['skinnedfrom'] = array();
		foreach($drops_sk as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry
					{
						, l.name_loc?d AS name_loc
						, l.subname_loc?d AS subname_loc
					}
					FROM ?_factiontemplate, creature_template c
					{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
					WHERE
						skinloot=?d
						AND factiontemplateID=faction_A
				',
				$npc_cols[0],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['skinnedfrom'][] = @array_merge(creatureinfo2($row), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_sk);

	// Перерабатывается в:
	if(!($item['prospecting'] = loot('prospecting_loot_template', $item['entry'])))
		unset($item['prospecting']);

	// Поиск вещей, из которых перерабатывается эта вещь
	$drops_pr = drop('prospecting_loot_template', $item['entry']);
	if($drops_pr)
	{
		$item['prospectingloot'] = array();
		foreach($drops_pr as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry, maxcount
					{
						, l.name_loc?d AS name_loc
					}
					FROM ?_icons, item_template c
					{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
					WHERE
						c.entry = ?d
						AND id = displayid
				',
				$item_cols[2],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['prospectingloot'][] = @array_merge(iteminfo2($row, 0), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_pr);

	// Дизенчантитcя в:
	if(!($item['disenchanting'] = loot('disenchant_loot_template', $item['DisenchantID'])))
		unset($item['disenchanting']);

	// Получается дизэнчантом из..
	$drops_de = drop('disenchant_loot_template', $item['entry']);
	if($drops_de)
	{
		$item['disenchantedfrom'] = array();
		foreach($drops_de as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry, maxcount
					{
						, l.name_loc?d AS name_loc
					}
					FROM ?_icons, item_template c
					{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
					WHERE
						DisenchantID=?d
						AND id=displayid
				',
				$item_cols[2],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['disenchantedfrom'][] = @array_merge(iteminfo2($row, 0), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_de);

	// Поиск сумок в которые эту вещь можно положить
	if($item['BagFamily'] == 256)
	{
		// Если это ключ
		$item['key'] = true;
	}
	elseif($item['BagFamily'] > 0 and $item['ContainerSlots'] == 0)
	{
		$rows_cpi = $DB->select('
				SELECT c.?#, c.entry, maxcount
				{
					, l.name_loc?d AS name_loc
				}
				FROM ?_icons, item_template c
				{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
				WHERE
					BagFamily=?d
					AND ContainerSlots>0
					AND id=displayid
			',
			$item_cols[2],
			($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
			($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
			$item['BagFamily']
		);
		if($rows_cpi)
		{
			$item['canbeplacedin'] = array();
			foreach($rows_cpi as $row)
				$item['canbeplacedin'][] = iteminfo2($row, 0);
		}
		unset($rows_cpi);
	}

	// Реагент для...
	$rows_r = $DB->select('
			SELECT ?#, spellID
			FROM ?_spell s, ?_spellicons i
			WHERE
				(( reagent1=?d
				OR reagent2=?d
				OR reagent3=?d
				OR reagent4=?d
				OR reagent5=?d
				OR reagent6=?d
				OR reagent7=?d
				OR reagent8=?d
				) AND ( i.id=s.spellicon))
		',
		$spell_cols[2],
		$item['entry'], $item['entry'], $item['entry'], $item['entry'],
		$item['entry'], $item['entry'], $item['entry'], $item['entry']
	);
	if($rows_r)
	{
		$item['reagentfor'] = array();
		$quality = 1;
		foreach($rows_r as $i=>$row)
		{
			$item['reagentfor'][$i] = array();
			$item['reagentfor'][$i]['entry'] = $row['spellID'];
			$item['reagentfor'][$i]['name'] = $row['spellname_loc'.$_SESSION['locale']];
			$item['reagentfor'][$i]['school'] = $row['resistancesID'];
			$item['reagentfor'][$i]['level'] = $row['levelspell'];
			$item['reagentfor'][$i]['quality'] = '@';
			for ($j=1;$j<=8;$j++)
				if($row['reagent'.$j])
				{
					$item['reagentfor'][$i]['reagents'][]['entry'] = $row['reagent'.$j];
					$item['reagentfor'][$i]['reagents'][count($item['reagentfor'][$i]['reagents'])-1]['count'] = $row['reagentcount'.$j];
					allitemsinfo($row['reagent'.$j], 0);
				}
			for ($j=1;$j<=3;$j++)
				if($row['effect'.$j.'itemtype'])
				{
					$item['reagentfor'][$i]['creates'][]['entry'] = $row['effect'.$j.'itemtype'];
					$item['reagentfor'][$i]['creates'][count($item['reagentfor'][$i]['creates'])-1]['count'] = 1 + $row['effect'.$j.'BasePoints'];
					allitemsinfo($row['effect'.$j.'itemtype'], 0);
					@$item['reagentfor'][$i]['quality'] = 7 - $allitems[$row['effect'.$j.'itemtype']]['quality'];
				}
			// Добавляем в таблицу спеллов
			allspellsinfo2($row);
		}
		unset($quality);
	}
	unset($rows_r);

	// Создается из...
	$rows_cf = $DB->select('
			SELECT ?#, s.spellID
			FROM ?_spell s, ?_spellicons i
			WHERE
				((s.effect1itemtype=?d
				OR s.effect2itemtype=?d
				OR s.effect3itemtype=?)
				AND (i.id = s.spellicon))
		',
		$spell_cols[2],
		$item['entry'], $item['entry'], $item['entry']
	);
	if($rows_cf)
	{
		$item['createdfrom'] = array();
		foreach($rows_cf as $row)
		{
			$skillrow = $DB->selectRow('
					SELECT skillID, min_value, max_value
					FROM ?_skill_line_ability
					WHERE spellID=?d
					LIMIT 1
				',
				$row['spellID']
			);
			$item['createdfrom'][] = spellinfo2(@array_merge($row, $skillrow));
		}
		unset($skillrow);
	}
	unset($rows_cf);

	// Ловится в ...
	$drops_fi = drop('fishing_loot_template', $item['entry']);
	if($drops_fi)
	{
		$item['fishedin'] = array();
		foreach($drops_fi as $lootid => $drop)
		{
			// Обычные локации
			$row = $DB->selectRow('
					SELECT name_loc'.$_SESSION['locale'].' AS name, areatableID as id
					FROM ?_zones
					WHERE
						areatableID=?d
						/*AND (x_min!=0 AND x_max!=0 AND y_min!=0 AND y_max!=0)*/
					LIMIT 1
				',
				$lootid
			);
			if($row)
			{
				$item['fishedin'][] = @array_merge($row, $drop);
			}
			else
			{
				// Инсты
				$row = $DB->selectRow('
						SELECT name_loc'.$_SESSION['locale'].' AS name, mapID as id
						FROM ?_zones
						WHERE
							areatableID=?d
						LIMIT 1
					',
					$lootid
				);
				if($row)
					$item['fishedin'][] = @array_merge($row, $drop);
			}
		}
		unset($row);
		unset($num);
	}
	unset($drops_fi);

	// Размалывается в
	if(!$item['milling'] = loot('milling_loot_template', $item['entry']))
		unset($item['milling']);

	// Получается размалыванием из
	$drops_mi = drop('milling_loot_template', $item['entry']);
	if($drops_mi)
	{
		$item['milledfrom'] = array();
		foreach($drops_mi as $lootid => $drop)
		{
			$rows = $DB->select('
					SELECT c.?#, c.entry, maxcount
					{
						, l.name_loc?d AS name_loc
					}
					FROM ?_icons, item_template c
					{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
					WHERE
						c.entry=?d
						AND id=displayid
				',
				$item_cols[2],
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$lootid
			);
			foreach($rows as $row)
				$item['milledfrom'][] = @array_merge(iteminfo2($row, 0), $drop);
		}
		unset($rows);
		unset($lootid);
		unset($drop);
	}
	unset($drops_mi);

	// Валюта для...
	$rows_cf = $DB->select('
		SELECT ?#, i.entry, i.maxcount, n.`maxcount` as `drop-maxcount`, n.ExtendedCost,
			{l.name_loc?d AS `name_loc`,}
			reqitem1, reqitem2, reqitem3, reqitem4, reqitem5,
			reqitemcount1, reqitemcount2, reqitemcount3, reqitemcount4, reqitemcount5,
			reqhonorpoints, reqarenapoints
		FROM npc_vendor n, ?_icons, ?_item_extended_cost iec, item_template i
			{LEFT JOIN (locales_item l) ON l.entry=i.entry AND ?d}
		WHERE (iec.reqitem1=?
		   OR iec.reqitem2=?
		   OR iec.reqitem3=?
		   OR iec.reqitem4=?
		   OR iec.reqitem5=?)
		  AND iec.extendedcostID=ABS(n.ExtendedCost)
		  AND i.entry=n.item
		  AND id=i.displayid
		',
		$item_cols[2],
		($_SESSION['locale'])? $_SESSION['locale']: DBSIMPLE_SKIP,
		($_SESSION['locale'])? 1: DBSIMPLE_SKIP,
		$item['entry'], $item['entry'], $item['entry'], $item['entry'], $item['entry']
	);
	if($rows_cf)
	{
		$item['currencyfor'] = array();
		foreach($rows_cf as $row)
		{
			$id=$row['entry'];
			$item['currencyfor'][$id] = array();
			$item['currencyfor'][$id] = iteminfo2($row);
			$item['currencyfor'][$id]['maxcount'] = $row['drop-maxcount'];
			$item['currencyfor'][$id]['cost'] = array();
			if($row['BuyPrice']>0)
				$npc['sells'][$id]['cost']['money'] = $row['BuyPrice'];

			if($row['reqhonorpoints']>0)
				$item['currencyfor'][$id]['cost']['honor'] =/* ($row['A']==1?1:-1)* */$row['reqhonorpoints']; //FIXME_BUG
			if($row['reqarenapoints']>0)
				$item['currencyfor'][$id]['cost']['arena'] = $row['reqarenapoints'];
			$item['currencyfor'][$id]['cost']['items'] = array();
			for($j=1; $j<=5; $j++)
			if(($row['reqitem'.$j]>0) and ($row['reqitemcount'.$j]>0))
			{
				allitemsinfo($row['reqitem'.$j], 0);
				$item['currencyfor'][$id]['cost']['items'][] = array(
					'item' => $row['reqitem'.$j],
					'count' => $row['reqitemcount'.$j]
				);
			}
		}
	}
	unset($rows_cf);

	// Цель критерии
	$rows = $DB->select('
			SELECT a.id, a.faction, a.name_loc?d AS name, a.description_loc?d AS description, a.category, a.points, s.iconname, z.areatableID
			FROM ?_spellicons s, ?_achievementcriteria c, ?_achievement a
			LEFT JOIN (?_zones z) ON a.map != -1 AND a.map = z.mapID
			WHERE
				a.icon = s.id
				AND a.id = c.refAchievement
				AND c.type IN (?a)
				AND c.value1 = ?d
			GROUP BY a.id
			ORDER BY a.name_loc?d
		',
		$_SESSION['locale'],
		$_SESSION['locale'],
		array(ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM, ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM, ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM, ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM),
		$item['entry'],
		$_SESSION['locale']
	);
	if($rows)
	{
		$item['criteria_of'] = array();
		foreach($rows as $row)
		{
			allachievementsinfo2($row['id']);
			$item['criteria_of'][] = achievementinfo2($row);
		}
	}

	save_cache(ITEM_PAGE, $cache_key, $item);
}
global $page;
$page = array(
	'Mapper' => false,
	'Book' => false,
	'Title' => $item['name'].' - '.$smarty->get_config_vars('Items'),
	'tab' => 0,
	'type' => 3,
	'typeid' => $item['entry'],
	'path' => path(0, 0, $item['classs'], $item['subclass'], $item['type'])
);
$smarty->assign('page', $page);

// Комментарии
$smarty->assign('comments', getcomments($page['type'], $page['typeid']));

// Количество MySQL запросов
$smarty->assign('mysql', $DB->getStatistics());
$smarty->assign('item', $item);
$smarty->display('item.tpl');
?>