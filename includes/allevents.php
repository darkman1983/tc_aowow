<?php

require_once('includes/db.php');
require_once('includes/allutil.php');

function event_find($conditions = NULL)
{
	global $DB;
	if (is_null($conditions))
	{
		return $DB->select('SELECT eventEntry FROM game_event');
	}
	elseif (is_array($conditions) && count($conditions)==1 && isset($conditions["holiday"]))
	{
		return $DB->select('SELECT eventEntry FROM game_event WHERE holiday=?d', $conditions["holiday"]);
	}
	elseif (is_array($conditions) && count($conditions)==1 && isset($conditions["creature_guid"]))
	{
		return $DB->select('SELECT ABS(eventEntry) AS eventEntry FROM game_event_creature WHERE guid=?d', $conditions["creature_guid"]);
	}
	elseif (is_array($conditions) && count($conditions)==1 && isset($conditions["object_guid"]))
	{
		return $DB->select('SELECT ABS(eventEntry) AS eventEntry FROM game_event_gameobject WHERE guid=?d', $conditions["object_guid"]);
	}
	elseif (is_array($conditions) && count($conditions)==1 && (isset($conditions["quest_id"]) || isset($conditions["quest_creature_id"])))
	{
		if (isset($conditions["quest_id"]))
			$rows = $DB->select('SELECT eventEntry, id, quest FROM game_event_creature_quest WHERE quest=?d', $conditions["quest_id"]);
		else
			$rows = $DB->select('SELECT eventEntry, id, quest FROM game_event_creature_quest WHERE id=?d', $conditions["quest_creature_id"]);
		$result = array();
		// This code is to make each event appear only once in array
		if ($rows)
			foreach ($rows as $row)
			{
				$entry = $row['eventEntry'];
				if (!isset($result[$entry]))
					$result[$entry] = array('eventEntry' => $entry);
				if (!isset($result[$entry]['creatures_quests_id']))
					$result[$entry]['creatures_quests_id'] = array();
				$result[$eventEntry]['creatures_quests_id'][] = array(
					'creature' => $row['id'],
					'quest' => $row['quest']
				);
			}
		return $result;
	}
	else
	{
		die("Unknown event_find condition");
	}
}

function event_name($events)
{
	global $DB;
	if (!$events || !is_array($events) || count($events) == 0)
		return array();

	$entries = array_select_key($events, 'eventEntry');

	$rows = $DB->select('
		SELECT eventEntry, description AS name
		FROM game_event
		WHERE eventEntry IN (?a)',
		$entries
	);

	// Merge original array with new information
	$result = array();
	foreach ($events as $event)
		if (isset($event['eventEntry']))
			$result[$event['eventEntry']] = $event;

	if ($rows)
	{
		foreach ($rows as $event)
			$result[$event['eventEntry']] = @array_merge($result[$event['eventEntry']], $event);
	}

	return $result;
}

function event_infoline($events)
{
	global $DB;
	if (!$events || !is_array($events) || count($events) == 0)
		return array();

	$entries = array_select_key($events, 'eventEntry');

	$rows = $DB->select('
		SELECT
			eventEntry, UNIX_TIMESTAMP(start_time) AS gen_start, UNIX_TIMESTAMP(end_time) AS gen_end,
			occurence, length, holiday, description AS name
		FROM game_event
		WHERE eventEntry IN (?a)',
		$entries
	);

	// Merge original array with new information
	$result = array();
	foreach ($events as $event)
		if (isset($event['eventEntry']))
			$result[$event['eventEntry']] = $event;

	if ($rows)
	{
		foreach ($rows as $row)
			$result[$row['eventEntry']] = @array_merge(
				$result[$row['eventEntry']],
				$row,
				event_startend($row['gen_start'], $row['gen_end'], $row['occurence'], $row['length']),
				array('id' => $row['eventEntry'])  // used in event_table template
			);
	}

	return $result;
}

function event_description($entry)
{
	global $DB;

	$result = event_infoline(array(array('eventEntry' => $entry)));
	if (is_array($result) && count($result) > 0)
		$result = reset($result);
	else
		return NULL;

	$result['period'] = sec_to_time(intval($result['occurence'])*60);

	$result['npcs_guid'] = $DB->selectCol('SELECT guid FROM game_event_creature WHERE eventEntry=?d OR eventEntry=?d', $entry, -$entry);
	$result['objects_guid'] = $DB->selectCol('SELECT guid FROM game_event_gameobject WHERE eventEntry=?d OR eventEntry=?d', $entry, -$entry);
	$result['creatures_quests_id'] = $DB->select('SELECT id AS creature, quest FROM game_event_creature_quest WHERE eventEntry=?d OR eventEntry=?d GROUP BY quest', $entry, -$entry);

	return $result;
}

function event_startend($gen_start, $gen_end, $occurence, $length)
{
	// Convert everything to seconds
	$gen_start = intval($gen_start);
	$gen_end = intval($gen_end);
	$occurence = intval($occurence)*60;
	$length = intval($length)*60;

	$now = time();
	$year = date("Y", $now);

	$start = $gen_start;
	$end = $start + $length;
	$next_start = $start + $occurence;
	$next_end = $end + $occurence;
	while ($next_end <= $gen_end && date("Y", $next_end) <= $year && $end <= $now)
	{
		$start = $next_start;
		$end = $next_end;
		$next_start = $start + $occurence;
		$next_end = $end + $occurence;
	}

	$dateshort = "Y-m-d";
	$datelong = "Y-m-d H:i:s";
	return array(
		'start' => date($dateshort, $start),
		'end' => date($dateshort, $end),
		'starttime' => date($datelong, $start),
		'endtime' => date($datelong, $end),
		'nextstarttime' => date($datelong, $next_start),
		'nextendtime' => date($datelong, $next_end),
		'today' => (($start < $now + 24*60*60) && ($now < $end + 24*60*60)) ? 1 : 0
	);
}

?>
