<?php

require_once('includes/alllocales.php');

// Функция информации о фракции
function factioninfo($id)
{
	global $DB;
	$faction['name'] = $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_factions WHERE factionID = ?d LIMIT 1', $id);
	$faction['entry'] = $id;
	return $faction;
}

// Что дропает
function loot($table, $lootid, $group = 0)
{
	global $DB, $item_cols;
	// Мего запрос :)
	$rows = $DB->select('
		SELECT l.ChanceOrQuestChance, l.mincountOrRef, l.maxcount as `d-max`, l.groupid, ?#, i.entry, i.maxcount
			{, loc.name_loc?d AS name_loc}
		FROM ?# l
			LEFT JOIN (?_icons a, item_template i) ON l.item=i.entry AND a.id=i.displayid
			{LEFT JOIN (locales_item loc) ON loc.entry=i.entry AND ?d}
		WHERE
			l.entry=?d
			AND l.entry <> 0
			{ AND l.groupid = ?d }
		{LIMIT ?d}
		',
		$item_cols[2],
		($_SESSION['locale'])? $_SESSION['locale']: DBSIMPLE_SKIP,
		$table,
		($_SESSION['locale'])? 1: DBSIMPLE_SKIP,
		$lootid,
		$group ? $group : DBSIMPLE_SKIP,
		($AoWoWconf['limit']!=0)? $AoWoWconf['limit']: DBSIMPLE_SKIP
	);

	// Подсчитываем нужную информацию о группах
	$groupchance = array();
	$groupzero = array();
	foreach ($rows as $row)
		if ($row['mincountOrRef'] >= 0)
		{
			$gid = $row['groupid'];
			if (!isset($groupchance[$gid])) $groupchance[$gid] = 0;
			if (!isset($groupzero[$gid])) $groupzero[$gid] = 0;
			if ($row['ChanceOrQuestChance'] == 0)
				$groupzero[$gid] ++;
			else
				$groupchance[$gid] += abs($row['ChanceOrQuestChance']);
 		}

	// Присваиваем каждой группе номер от 1
	$maxgroup = 0;
	$groupnum = array();
	foreach ($groupchance as $id => $group)
		if ($id)
			$groupnum[$id] = ++$maxgroup;
		else
			$groupnum[$id] = "";

	// Cохраняем весь нессылочный лут
	$loot = array();
	foreach ($rows as $row)
		if ($row['mincountOrRef'] > 0)
		{
			$chance = $row['ChanceOrQuestChance'];
			if($chance == 0) // Запись из группы с равным шансом дропа, считаем реальную вероятность
			{
				$chance = (100 - $groupchance[$row['groupid']]) / $groupzero[$row['groupid']];
				if ($chance < 0) $chance = 0;
				if ($chance > 100) $chance = 100;
			}

			$item = $row['entry'] . '.' . $row['groupid']; // Это чтобы отделить предметы в разных группах
			if (isset($loot[$item]))
			{
				$loot[$item]['mincount'] = min($row['mincountOrRef'], $loot[$item]['mincount']);
				if ($row['groupid'])
					$loot[$item]['maxcount'] = max($loot[$item]['maxcount'], $row['d-max']);
				else
					$loot[$item]['maxcount'] = $loot[$item]['maxcount'] + $row['d-max'];
				$loot[$item]['percent'] = 1 - (1-abs($chance))*(1-abs($loot[$item]['percent']));
			}
			else
			{
				$loot[$item] = iteminfo2($row, 0);
				$loot[$item]['mincount'] = $row['mincountOrRef'];
				$loot[$item]['maxcount'] = $row['d-max'];
				$loot[$item]['percent'] = $chance;
				$loot[$item]['group'] = $groupnum[$row['groupid']];
				$loot[$item]['groupcount'] = 1;
			}
		}

	// И наконец, добавляем весь лут со ссылок
	foreach ($rows as $row)
		if ($row['mincountOrRef'] < 0)
		{
			$newmax = $maxgroup;
			$tmploots = loot('reference_loot_template', -$row['mincountOrRef'], $row['groupid']);
			foreach ($tmploots as $tmploot)
			{
				if ($tmploot['group'])
				{
					$tmploot['group'] += $maxgroup;
					if ($newmax < $tmploot['group']) $newmax = $tmploot['group'];
					$tmploot['groupcount'] = $tmploot['groupcount'] * $row['d-max'];
				}
				else
				{
					$tmploot['maxcount'] *= $row['d-max'];
				}
				$tmploot['percent'] *= abs($row['ChanceOrQuestChance'])/100;
				$loot[] = $tmploot;
			}
			$maxgroup = $newmax;
		}

	return $loot;
}

// Кто дропает
function drop($table, $item)
{
	global $DB, $AoWoWconf;

	$total = 0;

	// Реверсный поиск лута начиная с референсной таблицы
	// Ищем в группах
	$drop = array();
	$curtable = 'reference_loot_template';
	$rows = $DB->select('
			SELECT entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount
			FROM ?#
			WHERE
				item = ?
				AND mincountOrRef > 0
		',
		$curtable,
		$item
	);
	while(true)
	{
		foreach($rows as $i => $row)
		{
			$chance = abs($row['ChanceOrQuestChance']);
			if($chance == 0)
			{
				// Запись из группы с равным шансом дропа, считаем реальную вероятность
				$zerocount = 0;
				$chancesum = 0;
				$subrows = $DB->select('
						SELECT ChanceOrQuestChance, mincountOrRef, maxcount
						FROM ?#
						WHERE entry = ? AND groupid = ? AND mincountOrRef >= 0
					',
					$curtable,
					$row['entry'],
					$row['groupid']
				);
				foreach($subrows as $i => $subrow)
				{
					if($subrow['ChanceOrQuestChance'] == 0)
						$zerocount++;
					else
						$chancesum += abs($subrow['ChanceOrQuestChance']);
				}
				$chance = (100 - $chancesum) / $zerocount;
			}
			$chance = max($chance, 0);
			$chance = min($chance, 100);
			$mincount = $row['mincountOrRef'];
			$maxcount = $row['maxcount'];

			if($mincount < 0)
			{
				// Референсная ссылка. Вероятность основывается на уже подсчитанной.
				$num = $mincount;
				$mincount = $drop[$num]['mincount'];
				$chance = $chance * (1 - pow(1 - $drop[$num]['percent']/100, $maxcount));
				$maxcount = $drop[$num]['maxcount']*$maxcount;
			}

			// Сохраняем подсчитанные для этих групп вероятности
			//(референсные записи хранятся с отрицательными номерами)
			$nums = array();
			$nums[0] = ($curtable <> $table) ? -$row['entry'] : $row['entry'];
			if ($curtable <> $table && $row['groupid'])
				$nums[1] = $nums[0] . "." . $row['groupid'];
			foreach ($nums as $num)
			if(isset($drop[$num]))
			{
				// Этот же элемент уже падал в другой подгруппе - считаем общую вероятность.
				$newmin =($drop[$num]['mincount'] < $mincount) ? $drop[$num]['mincount'] : $mincount;
				$newmax = $drop[$num]['maxcount'] + $maxcount;
				$newchance = 100 - (100 - $drop[$num]['percent'])*(100-$chance)/100;
				$drop[$num]['percent'] = $newchance;
				$drop[$num]['mincount'] = $newmin;
				$drop[$num]['maxcount'] = $newmax;
			}
			else
			{
				$drop[$num] = array();
				$drop[$num]['percent'] = $chance;
				$drop[$num]['mincount'] = $mincount;
				$drop[$num]['maxcount'] = $maxcount;
				$drop[$num]['checked'] = false;

				if($AoWoWconf['limit'] > 0 && $num > 0 && ++$total > $AoWoWconf['limit'])
					break;
			}
		}

		// Ищем хоть одну непроверенную reference-ную запись
		$num = 0;
		foreach($drop as $i => $value)
		{
			if($i < 0 && strpos($i,'.')===FALSE && !$value['checked'])
			{
				$num = $i;
				break;
			}
		}

		// Нашли?
		if($num == 0)
		{
			// Все элементы проверены
			if($curtable != $table)
			{
				// но это была reference-ная таблица - надо поискать в основной
				$curtable = $table;

				foreach($drop as $i => $value)
					$drop[$i]['checked'] = false;

				$rows = $DB->select('
						SELECT entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount
						FROM ?#
						WHERE
							item = ?
							AND mincountOrRef > 0
					',
					$curtable,
					$item
				);
			}
			else
				// Если ничего не нашли и в основной таблице, то поиск закончен
				break;
		}
		else
		{
			// Есть непроверенный элемент, надо его проверить
			$drop[$num]['checked'] = true;
			$rows = $DB->select('
					SELECT entry, groupid, ChanceOrQuestChance, mincountOrRef, maxcount
					FROM ?#
					WHERE mincountOrRef = ?
				',
				$curtable,
				$num
			);
		}
	}

	// Чистим reference-ные ссылки
	foreach($drop as $i => $value)
		if($i < 0)
			unset($drop[$i]);

	return $drop;
}



// Функция преобразует координаты из серверных в игровые
function transform_point($at, $point)
{
	$result = $point;

	$result['x'] = round(100 - ($point['y']-$at['y_min']) / (($at['y_max']-$at['y_min']) / 100), 2);
	$result['y'] = round(100 - ($point['x']-$at['x_min']) / (($at['x_max']-$at['x_min']) / 100), 2);
	if (isset($point['spawntimesecs']))
		$result['r'] = sec_to_time($point['spawntimesecs']);
	unset($result['spawntimesecs']);

	return $result;
}

// Функция выбирает подходящую зону для точки из списка
function select_zone($at_data, $point)
{
	global $cached_images, $at_dataoffsets;

	$chosen_area = 0;
	// Сначала ищем в закешированных локациях
	$matching_locations = array();
	foreach($at_data as $at)
	{
		if($at['mapID'] == $point['m'] && $at['x_min'] <= $point['x'] && $at['x_max'] >= $point['x'] && $at['y_min'] <= $point['y'] && $at['y_max'] >= $point['y'])
		{
			// Если нам не сказано игнорировать негативы
			if(!$point['n'])
			{
				if(!$cached_images[$at['areatableID']])
				{
					$filename = 'images/tmp/'.$at['areatableID'].'.png';
					if(!file_exists($filename))
						continue; // Не существует - пропускаем зону

					$cached_images[$at['areatableID']] = imagecreatefrompng($filename);
				}

				$game_x = 100 - ($point['y']-$at['y_min']) / (($at['y_max']-$at['y_min']) / 100);
				$game_y = 100 - ($point['x']-$at['x_min']) / (($at['x_max']-$at['x_min']) / 100);

				if(imagecolorat($cached_images[$at['areatableID']], round($game_x * 10), round($game_y * 10)) !== 0)
					continue;
			}

			$matching_locations[] = $at['areatableID'];
		}
	}
	if($matching_locations)
	{
		if(count($matching_locations) > 1)
		{
			// TODO: а такое бывает? поидеи да, со столицами
			// Из нескольких локаций выбираем самую маленькую
			$chosen_area = $matching_locations[0];
			foreach($matching_locations as $loc)
			{
				$our = $at_data[$at_dataoffsets[$chosen_area]];
				$chk = $at_data[$at_dataoffsets[$loc]];
				// Если эта карта меньше выбранной вначале
				if(abs($our['x_max']-$our['x_min']) > abs($chk['x_max']-$chk['x_min']) || abs($our['y_max']-$our['y_min']) > abs($chk['y_max']-$chk['y_min']))
					$chosen_area = $loc;
			}
		}
		else
			$chosen_area = $matching_locations[0];
	}
	return $chosen_area;
}

// Функция преобразовывает массив серверных координат
// в массив координат и локаций клиента
function transform_coords($recv)
{
	global $DB, $at_dataoffsets;
	// Cached data
	$map_data = array();
	$at_data = array();
	$map_dataoffsets = array();
	$zone_dataoffsets = array();
	$at_dataoffsets = array();
	$at_dataoffsets_offset = 0;
	$loaded_areas = array();

	$data = array();
	$i = -1; // на самом деле в этой переменной хранится id последнего элемента в $data

	// Собираем номера всех карт, где находятся точки
	$mapids = array();
	foreach($recv as $point)
	{
		if(!in_array($point['m'], $mapids))
			$mapids[] = $point['m'];
	}
	// Считываем сколько всего карт существует
	$result = $DB->select('SELECT mapID, areatableID, name_loc?d AS name, x_min, COUNT(*) AS c FROM ?_zones WHERE mapID IN (?a) GROUP BY mapID', $_SESSION['locale'], $mapids);

	if(!$result)
		return false;

	foreach($result as $record)
	{
		$mapid = $record['mapID'];
		$atid = $record['areatableID'];
		$map_data[$mapid] = array(
			'name' => $record['name']
		);
		// Для этой карты существует всего одна локация,
		// причем неизвестно, есть для нее карта или нет.
		if($record['c'] == 1)
		{
			if(file_exists('images/maps/enus/normal/'.$atid.'.jpg'))
			{
				$map_data[$mapid]['atid'] = $atid;
				// Это хак, но пусть так
				if($record['x_min'] == 0)
					$map_data[$mapid]['coords_not_available'] = true;
				else
					// Задаем директиву не использовать негативы
					$map_data[$mapid]['ignore_negatives'] = true;
			}
			else
				// Карта не доступна - записываем, что бы потом не жрать ресурсы.
				$map_data[$mapid]['map_not_available'] = true;
		}
		else
			$map_data[$mapid]['multiple_areas'] = true;
	}

	foreach($recv as $point)
	{
		$mapid = $point['m'];

		$md = $map_data[$mapid];
		$point['n'] = $md['ignore_negatives'];
		// Карта не доступна
		if($md['map_not_available'] || $md['coords_not_available'])
		{
			if(isset($map_dataoffsets[$mapid]))
			{
				// Если у нас уже была точка с такой картой,
				// то увеличиваем население на ней.
				$data[$map_dataoffsets[$mapid]]['population']++;
			}
			else
			{
				// Если нет - создаем новую.
				$data[++$i] = array(
					'population' => 1,
					'name' => $md['name'],
					'atid' => $md['map_not_available'] ? 0 : $md['atid']
				);
				$map_dataoffsets[$mapid] = $i;
			}
			continue;
		}
		// Если всего одна зона на карту,
		// и для нее есть изображение и координаты,
		// сразу же указываем номер зоны
		if(!$md['multiple_areas'])
			$chosen_area = $md['atid'];
		else
			$chosen_area = select_zone($at_data, $point);

		// Если зон на карту много и/или нужная нам не загружена
		if(!$chosen_area || !in_array($chosen_area, $loaded_areas))
		{
			// Загружаем зоны
			$result = $DB->select('
					SELECT mapID, areatableID, x_min, x_max, y_min, y_max, name_loc?d AS name
					FROM ?_zones
					WHERE
						(? BETWEEN x_min AND x_max)
						AND (? BETWEEN y_min AND y_max)
						AND mapID = ?
				',
				$_SESSION['locale'],
				$point['x'],
				$point['y'],
				$point['m']
			);
			foreach($result as $area)
			{
				$loaded_areas[] = $area['areatableID'];
				$at_dataoffsets[$area['areatableID']] = $at_dataoffsets_offset++;
			}
			$at_data = @array_merge($at_data, $result);
			$chosen_area = select_zone($at_data, $point);
		}
		// Если зона так и не найдена (исключительный случай)
		// просто этой точки не будет на карте.

		// Если мы в финальном массиве уже имеем запись на
		// данный номер зоны
		if(isset($zone_dataoffsets[$chosen_area]))
		{
			$offset = $zone_dataoffsets[$chosen_area];
			// Если у нас уже была точка с такой картой,
			// то увеличиваем население на ней, записываем новую точку
			$data[$offset]['population']++;

			if($chosen_area)
				$data[$offset]['points'][] = transform_point($at_data[$at_dataoffsets[$chosen_area]], $point);
		}
		else
		{
			$points_array = $chosen_area ? array(transform_point($at_data[$at_dataoffsets[$chosen_area]], $point)) : array();
			// Если нет - создаем новую.
			$data[++$i] = array(
				'population' => 1,
				'name' => $at_data[$at_dataoffsets[$chosen_area]]['name'],
				'atid' => $chosen_area,
				'points' => $points_array
			);
			$zone_dataoffsets[$chosen_area] = $i;
		}
	}

	return $data;
}

function transform_coords2($points)
{
	global $DB, $cached_images;

	// Собираем номера всех карт, где находятся точки
	$mapids = array();
	foreach ($points as $point)
		if(!in_array($point['m'], $mapids))
			$mapids[] = $point['m'];

	$areas = $DB->select('SELECT mapID, areatableID, name_loc?d AS name, x_min, y_min, x_max, y_max FROM ?_zones WHERE mapID IN (?a)', $_SESSION['locale'], $mapids);

	// Сначала выберем самую хорошую карту для каждой точки
	$bestarea = array();
	$mapexists=array(); // cache
	$maskexists=array(); // cache
	foreach ($points as $pointid => $point)
	{
		foreach ($areas as $areaid => $area)
			if ($point['m'] == $area['mapID'])
			{
				// Определяем "приоритет" этой карты - насколько хорошо она подходит для данной точки
				$curpriority = 0;
				if (!isset($mapexists[$areaid]))
					$mapexists[$areaid] = file_exists('images/maps/enus/normal/'.$area['areatableID'].'.jpg');
				if ($mapexists[$areaid])
					$curpriority |= 1;
				$maskfilename = 'images/tmp/'.$area['areatableID'].'.png';
				if (!isset($maskexists[$areaid]))
					$maskexists[$areaid] = file_exists($maskfilename);
				if ($maskexists[$areaid])
					$curpriority |= 2;
				// Note: Dalaran has its coords reversed.
				if ($point['x'] >= $area['x_min'] && $point['x'] <= $area['x_max'])
					$curpriority |= 8;
				if ($point['y'] >= $area['y_min'] && $point['y'] <= $area['y_max'])
					$curpriority |= 16;
				if (($curpriority&2) && ($curpriority&8) && ($curpriority&16))
				{
					if(!$cached_images[$area['areatableID']])
						$cached_images[$area['areatableID']] = imagecreatefrompng($maskfilename);
					$game_x = 100 - ($point['y']-$area['y_min']) / (($area['y_max']-$area['y_min']) / 100);
					$game_y = 100 - ($point['x']-$area['x_min']) / (($area['x_max']-$area['x_min']) / 100);

					if (imagecolorat($cached_images[$area['areatableID']], round($game_x * 10), round($game_y * 10)) === 0)
						$curpriority |= 4;
			        }

				// И сравниваем текущий приоритет с лучшим
				if (!isset($bestarea[$pointid]) || $bestarea[$pointid]['priority'] < $curpriority ||
				    ($bestarea[$pointid]['priority'] == $curpriority &&
				     $areas[$bestarea[$pointid]['area']]['areatableID'] < $area['areatableID']))
					$bestarea[$pointid] = array(
						'priority' => $curpriority,
						'area' => $areaid
					);
			}
	}

	// А теперь формируем выходной массив карт
	$data = array();
	$data_ids = array(); // stupid 'areatableID => $data index' array
	foreach ($points as $pointid => $point)
		if (isset($bestarea[$pointid]))
		{
			$i = $bestarea[$pointid]['area'];
			$atid = $areas[$i]['areatableID'];
			if (!isset($data_idx[$atid]))
			{
				$data_idx[$atid] = count($data);
				$data[] = array(
					'population' => 0,
					'name' => $areas[$i]['name'],
					'atid' => $atid,
					'points' => array()
				);
			}
			if ($point['type']==0) // Account spawn points only, not waypoints
				$data[$data_idx[$atid]]['population']++;
			if ($areas[$i]['x_min'] != $areas[$i]['x_max'] && $areas[$i]['y_min'] != $areas[$i]['y_max'])
				$data[$data_idx[$atid]]['points'][] = transform_point($areas[$i], $point);
		}
		else
		{
			if (!isset($data_idx[0]))
			{
				$data_idx[0] = count($data);
				$data[] = array(
					'population' => 0,
					'name' => '(unknown)',
					'atid' => 0,
					'points' => array()
				);
			}
			$data[$data_idx[0]]['population']++;
		}
	return $data;
}

// Функция создает полный и окончательный массив информации о местоположении объектов или НПС
function position($id, $type, $spawnMask = 0)
{
	global $smarty, $exdata, $zonedata, $DB, $AoWoWconf, $cached_images;

	$data = $DB->select('
			SELECT guid, map AS m, position_x AS x, position_y AS y, spawntimesecs, {MovementType AS ?#, }"0" AS `type`
			FROM '.$type.'
			WHERE id = ?d {AND spawnMask & ?d}
			{ GROUP BY ROUND(x,?d), ROUND(y,?d) }
			ORDER BY x,y
		',
		($type == 'gameobject' ? DBSIMPLE_SKIP : 'mt'),
		$id,
		$spawnMask ? $spawnMask : DBSIMPLE_SKIP,
		$AoWoWconf['map_grouping'] > 0 ? -$AoWoWconf['map_grouping'] : DBSIMPLE_SKIP,
		$AoWoWconf['map_grouping'] > 0 ? -$AoWoWconf['map_grouping'] : DBSIMPLE_SKIP
	);
	if($type <> 'gameobject')
	{
		$wpWalkingCreaturesGuids = array();
		foreach($data as $spawnid => $spawn)
		{
			if($spawn['mt'] == 2)
				$wpWalkingCreaturesGuids[] = $spawn['guid'];
		}
		if($wpWalkingCreaturesGuids)
		{
			$wps = $DB->select('
					SELECT c.map AS m, m.position_x AS x, m.position_y AS y, "3" AS `type`
					FROM waypoint_data m, creature c
					WHERE
						m.id = c.guid
						AND m.id IN (?a)
					{ GROUP BY ROUND(x,?d), ROUND(y,?d) }
					ORDER BY x,y
				',
				$wpWalkingCreaturesGuids,
				$AoWoWconf['map_grouping'] > 0 ? -$AoWoWconf['map_grouping'] : DBSIMPLE_SKIP,
				$AoWoWconf['map_grouping'] > 0 ? -$AoWoWconf['map_grouping'] : DBSIMPLE_SKIP
			);
			$data = @array_merge($wps, $data);
		}
	}

	if($data)
	{
		$data = transform_coords2($data);

		// Сортируем массив
		do
		{
			$changed = false;
			for($i = 0; $i < count($data); $i++)
			{
				// $l - предыдущий элемент массива
				if(isset($l) && $data[$l]['population'] < $data[$i]['population'])
				{
					$tmp = $data[$l];
					$data[$l] = $data[$i];
					$data[$i] = $tmp;
					$changed = true;
				}
				$l = $i;
			}
			unset($l);
		} while($changed);

		// Удаляем карты
		if($cached_images)
			foreach($cached_images as $img)
				imagedestroy($img);

		return $data;
	}
}
?>