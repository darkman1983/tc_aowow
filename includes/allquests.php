<?php

require_once('includes/game.php');
require_once('includes/alllocales.php');
require_once('includes/allitems.php');

global $AoWoWconf;

$quest_class = array(
	0	=> array(36, 45, 3, 25, 4, 46, 279, 41, 2257, 1, 10, 139, 12, 3430, 3433, 267, 1537, 4080, 38, 44, 51, 3487, 130, 1519, 33, 8, 47, 85, 1497, 28, 40, 11),
	1	=> array(331, 16, 3524, 3525, 148, 1657, 405, 14, 15, 361, 357, 493, 215, 1637, 1377, 406, 440, 141, 17, 3557, 400, 1638, 1216, 490, 618),
	8	=> array(3522, 3483, 3518, 3523, 3520, 3703, 3679, 3519, 3521),
	10	=> array(3537, 4024, 4395, 65, 394, 495, 210, 3711, 67, 4197, 66),
	2	=> array(4494, 3790, 3477, 719, 1584, 1583, 1941, 3905, 2557, 4196, 133, 4375, 4272, 4264, 3562, 4095, 3792, 2100, 2367, 2437, 722, 491, 796, 2057, 3791, 3789, 209, 2017, 4100, 1417, 3845, 3846, 2366, 3713, 3847, 1581, 3849, 4120, 4228, 3714, 3717, 3715, 717, 3716, 4415, 4723, 1337, 206, 1196, 718, 978),
	3	=> array(2677, 3606, 2562, 3836, 2717, 3456, 2159, 3429, 3428, 3840, 3842, 4500, 4722, 4273, 3805, 19),
	4	=> array(-372, -263, -261, -161, -141, -262, -162, -82, -61, -81),
	5	=> array(-181, -121, -304, -201, -324, -101, -24, -371, -373, -182, -264),
	6	=> array(-25, 3358, 2597, 3820, 4710, 4384, 3277),
	9	=> array(-370, -1002, -364, -1007, -1003, -1005, -1004, -366, -369, -1006, -1008, -374, -1001, -284, -41, -22, -375),
	7	=> array(-365, -241, -1, -344, -367, -368),
	-2	=> array(0)
);

$quest_type = array(
	1  => LOCALE_QUEST_TYPE_GROUP,
	21 => LOCALE_QUEST_TYPE_LIFE,
	41 => LOCALE_QUEST_TYPE_PVP,
	62 => LOCALE_QUEST_TYPE_RAID,
	81 => LOCALE_QUEST_TYPE_DUNGEON,
	82 => LOCALE_QUEST_TYPE_WORLD_EVENT,
	83 => LOCALE_QUEST_TYPE_LEGENDARY,
	84 => LOCALE_QUEST_TYPE_ESCORT,
	85 => LOCALE_QUEST_TYPE_HEROIC,
	88 => LOCALE_QUEST_TYPE_RAID10,
	89 => LOCALE_QUEST_TYPE_RAID25
);

$quest_faction_reward = array(
	-9 => -5,
	-8 => -1000,
	-7 => -500,
	-6 => -350,
	-5 => -250,
	-4 => -150,
	-3 => -75,
	-2 => -25,
	-1 => -10,
	0 => 0,
	1 => 10,
	2 => 25,
	3 => 75,
	4 => 150,
	5 => 250,
	6 => 350,
	7 => 500,
	8 => 1000,
	9 => 5
);

// Флаги квестов
define('QUEST_FLAGS_NONE',				0);
define('QUEST_FLAGS_STAY_ALIVE',		1);
define('QUEST_FLAGS_PARTY_ACCEPT',		2);
define('QUEST_FLAGS_EXPLORATION',		4);
define('QUEST_FLAGS_SHARABLE',			8);
define('QUEST_FLAGS_NONE2',				16);
define('QUEST_FLAGS_EPIC',				32);
define('QUEST_FLAGS_RAID',				64);
define('QUEST_FLAGS_TBC',				128);
define('QUEST_FLAGS_UNK2',				256);
define('QUEST_FLAGS_HIDDEN_REWARDS',	512);
define('QUEST_FLAGS_AUTO_REWARDED',	1024);
define('QUEST_FLAGS_TBC_RACES',			2048);
define('QUEST_FLAGS_DAILY',				4096);
define('QUEST_FLAGS_UNK5',				8192);

define('QUEST_SPECIALFLAGS_NONE',		0);
define('QUEST_SPECIALFLAGS_REPEATABLE',	1);
define('QUEST_SPECIALFLAGS_SCRIPTED',	2);

// Флаги для GetQuestInfo
define('QUEST_DATAFLAG_MINIMUM',	1);
define('QUEST_DATAFLAG_STRINGS',	2);
define('QUEST_DATAFLAG_SERIES',		4);
define('QUEST_DATAFLAG_LOCALE',		8); // Специальный флаг, $questcols не требуется
define('QUEST_DATAFLAG_REWARDS',	16); // Содержит также Req's
define('QUEST_DATAFLAG_PROPS',		32);
define('QUEST_DATAFLAG_LISTINGS', (QUEST_DATAFLAG_MINIMUM | QUEST_DATAFLAG_REWARDS | QUEST_DATAFLAG_PROPS));
define('QUEST_DATAFLAG_AJAXTOOLTIP', (QUEST_DATAFLAG_LISTINGS | QUEST_DATAFLAG_SERIES | QUEST_DATAFLAG_STRINGS | QUEST_DATAFLAG_LOCALE));

$questcols[QUEST_DATAFLAG_MINIMUM]	= array('entry', 'Title');
$questcols[QUEST_DATAFLAG_STRINGS]	= array('Objectives', 'Details', 'RequestItemsText', 'OfferRewardText', 'EndText', 'ObjectiveText1', 'ObjectiveText2', 'ObjectiveText3', 'ObjectiveText4');
$questcols[QUEST_DATAFLAG_REWARDS]	= array('RewardChoiceItemId1', 'RewardChoiceItemId2', 'RewardChoiceItemId3', 'RewardChoiceItemId4', 'RewardChoiceItemId5', 'RewardChoiceItemId6', 'RewardChoiceItemCount1', 'RewardChoiceItemCount2', 'RewardChoiceItemCount3', 'RewardChoiceItemCount4', 'RewardChoiceItemCount5', 'RewardChoiceItemCount6', 'RewardItemId1', 'RewardItemId2', 'RewardItemId3', 'RewardItemId4', 'RewardItemCount1', 'RewardItemCount2', 'RewardItemCount3', 'RewardItemCount4', 'RewardMoneyMaxLevel', 'RewardOrRequiredMoney', 'RequiredSpellCast1', 'RequiredSpellCast2', 'RequiredSpellCast3', 'RequiredSpellCast4', 'RequiredNpcOrGo1', 'RequiredNpcOrGo2', 'RequiredNpcOrGo3', 'RequiredNpcOrGo4', 'RequiredItemId1', 'RequiredItemId2', 'RequiredItemId3', 'RequiredItemId4', 'RequiredItemCount1', 'RequiredItemCount2', 'RequiredItemCount3', 'RequiredItemCount4', 'SuggestedPlayers', 'RequiredNpcOrGoCount1', 'RequiredNpcOrGoCount2', 'RequiredNpcOrGoCount3', 'RequiredNpcOrGoCount4', 'RewardSpell', 'RewardSpellCast', 'RewardFactionValueId1', 'RewardFactionValueId2', 'RewardFactionValueId3', 'RewardFactionValueId4', 'RewardFactionValueId5', 'RewardFactionId1', 'RewardFactionId2', 'RewardFactionId3', 'RewardFactionId4', 'RewardFactionId5', 'RewardFactionValueId1', 'RewardFactionValueId2', 'RewardFactionValueId3', 'RewardFactionValueId4', 'RewardFactionValueId5', 'SourceItemId', 'SourceItemCount', 'SourceSpellId', 'RequiredFactionId1', 'RequiredFactionValue1', 'RequiredMinRepFaction', 'RequiredMinRepValue', 'RequiredMaxRepFaction', 'RequiredMaxRepValue', 'RequiredPlayerKills', 'RewardTalents', 'RequiredSourceItemId1', 'RequiredSourceItemCount1', 'RequiredSourceItemId2', 'RequiredSourceItemCount2', 'RequiredSourceItemId3', 'RequiredSourceItemCount3', 'RequiredSourceItemId4', 'RequiredSourceItemCount4', 'RewardHonor', 'RewardMailTemplateId', 'RewardMailDelay', 'PointX', 'PointY', 'StartScript', 'CompleteScript');
$questcols[QUEST_DATAFLAG_PROPS]	= array('Type', 'RequiredClasses', 'ZoneOrSort', 'Flags', 'Level', 'MinLevel', 'RequiredRaces', 'RequiredSkillPoints', 'RequiredSkillId', 'LimitTime', 'SpecialFlags', 'RewardTitleId');
$questcols[QUEST_DATAFLAG_SERIES]	= array('PrevQuestId', 'NextQuestIdChain', 'ExclusiveGroup', 'NextQuestId');

$quest_cols[2] = array('Id', 'Title', 'Level', 'MinLevel', 'RequiredRaces', 'RewardChoiceItemId1', 'RewardChoiceItemId2', 'RewardChoiceItemId3', 'RewardChoiceItemId4', 'RewardChoiceItemId5', 'RewardChoiceItemId6', 'RewardChoiceItemCount1', 'RewardChoiceItemCount2', 'RewardChoiceItemCount3', 'RewardChoiceItemCount4', 'RewardChoiceItemCount5', 'RewardChoiceItemCount6', 'RewardItemId1', 'RewardItemId2', 'RewardItemId3', 'RewardItemId4', 'RewardItemCount1', 'RewardItemCount2', 'RewardItemCount3', 'RewardItemCount4', 'RewardMoneyMaxLevel', 'RewardOrRequiredMoney', 'Type', 'ZoneOrSort', 'Flags');
$quest_cols[3] = array('Title', 'Level', 'MinLevel', 'RequiredRaces', 'RewardChoiceItemId1', 'RewardChoiceItemId2', 'RewardChoiceItemId3', 'RewardChoiceItemId4', 'RewardChoiceItemId5', 'RewardChoiceItemId6', 'RewardChoiceItemCount1', 'RewardChoiceItemCount2', 'RewardChoiceItemCount3', 'RewardChoiceItemCount4', 'RewardChoiceItemCount5', 'RewardChoiceItemCount6', 'RewardItemId1', 'RewardItemId2', 'RewardItemId3', 'RewardItemId4', 'RewardItemCount1', 'RewardItemCount2', 'RewardItemCount3', 'RewardItemCount4', 'RewardMoneyMaxLevel', 'RewardOrRequiredMoney', 'Type', 'RequiredClasses', 'ZoneOrSort', 'Flags', 'RewardFactionValueId1', 'RewardFactionValueId2', 'RewardFactionValueId3', 'RewardFactionValueId4', 'RewardFactionValueId5', 'RewardFactionId1', 'RewardFactionId2', 'RewardFactionId3', 'RewardFactionId4', 'RewardFactionId5', 'RewardFactionValueId1', 'RewardFactionValueId2', 'RewardFactionValueId3', 'RewardFactionValueId4', 'RewardFactionValueId5', 'Objectives', 'Details', 'RequestItemsText', 'OfferRewardText', 'RequiredNpcOrGo1', 'RequiredNpcOrGo2', 'RequiredNpcOrGo3', 'RequiredNpcOrGo4', 'RequiredItemId1', 'RequiredItemId2', 'RequiredItemId3', 'RequiredItemId4', 'RequiredItemCount1', 'RequiredItemCount2', 'RequiredItemCount3', 'RequiredItemCount4', 'SourceItemId', 'RequiredNpcOrGoCount1', 'RequiredNpcOrGoCount2', 'RequiredNpcOrGoCount3', 'RequiredNpcOrGoCount4', 'ObjectiveText1', 'ObjectiveText2', 'ObjectiveText3', 'ObjectiveText4', 'EndText', 'PrevQuestID', 'NextQuestIdChain', 'ExclusiveGroup', 'NextQuestID', 'RewSpellCast', 'RewSpell', 'RequiredSkillPoints', 'RequiredFactionId1', 'RequiredFactionValue1', 'SuggestedPlayers', 'LimitTime', 'Flags', 'SpecialFlags', 'RewardTitleId', 'RequiredMinRepFaction', 'RequiredMinRepValue', 'RequiredMaxRepFaction', 'RequiredMaxRepValue', 'SourceSpellId', 'RequiredSkillId', 'RequiredSpellCast1', 'RequiredSpellCast2', 'RequiredSpellCast3', 'RequiredSpellCast4');

function QuestReplaceStr($str)
{
	// Uppercase to lowercase
	$str = strtr($str, array(
		'$B'	=> '$b',
		'$R'	=> '$r',
		'$C'	=> '$c',
		'$N'	=> '$n',
	));
	// Single ones
	$str = strtr($str, array(
		'$b'	=> '<br />',
		'$r'	=> htmlspecialchars('<'.LOCALE_RACE.'>'),
		'$c'	=> htmlspecialchars('<'.LOCALE_CLASS.'>'),
		'$n'	=> htmlspecialchars('<'.LOCALE_NAME.'>'),
		"\r"	=> '',
		"\n"	=> '',
	));
	// Gender
	$str = preg_replace('/\$g(.*?):(.*?);/iu', htmlspecialchars('<$1/$2>'), $str);

	return $str;
}

// Информация, возвращаемая этой функцией, очень помогает
// оценить доступность и выполнимость квестов.
function GetFlagsDetails($data)
{
	$result = array();
	// Неявно используемые в квесте итемы
	$SourceItems = array();
	for ($i=1; $i<=4; $i++)
		if (isset($data['RequiredSourceItemId'.$i]) && isset($data['RequiredSourceItemCount'.$i]) && $data['RequiredSourceItemId'.$i])
			if ($data['RequiredSourceItemCount'.$i] == 1)
				$SourceItems[] = $data['RequiredSourceItemId'.$i];
			else
				$SourceItems[] = $data['RequiredSourceItemId'.$i] . "x" . $data['RequiredSourceItemCount'.$i];

	// Разные квестовые флаги, и клиентские и серверные
	if ($data['Flags'])
	{
		if ($data['Flags'] & QUEST_FLAGS_STAY_ALIVE)     $result[] = LOCALE_QUEST_FLAGS_STAY_ALIVE;
		if ($data['Flags'] & QUEST_FLAGS_PARTY_ACCEPT)   $result[] = LOCALE_QUEST_FLAGS_PARTY_ACCEPT;
		if ($data['Flags'] & QUEST_FLAGS_EXPLORATION)    $result[] = LOCALE_QUEST_FLAGS_EXPLORATION;
		if ($data['Flags'] & QUEST_FLAGS_SHARABLE)       $result[] = LOCALE_QUEST_FLAGS_SHARABLE;
		if ($data['Flags'] & QUEST_FLAGS_EPIC)           $result[] = LOCALE_QUEST_FLAGS_EPIC;
		if ($data['Flags'] & QUEST_FLAGS_RAID)           $result[] = LOCALE_QUEST_FLAGS_RAID;
		if ($data['Flags'] & QUEST_FLAGS_TBC)            $result[] = LOCALE_QUEST_FLAGS_TBC;
		//if ($data['Flags'] & QUEST_FLAGS_UNK2)           $result[] = LOCALE_QUEST_FLAGS_UNK2;
		if ($data['Flags'] & QUEST_FLAGS_HIDDEN_REWARDS) $result[] = LOCALE_QUEST_FLAGS_HIDDEN_REWARDS;
		if ($data['Flags'] & QUEST_FLAGS_AUTO_REWARDED)  $result[] = LOCALE_QUEST_FLAGS_AUTO_REWARDED;
		if ($data['Flags'] & QUEST_FLAGS_TBC_RACES)      $result[] = LOCALE_QUEST_FLAGS_TBC_RACES;
		if ($data['Flags'] & QUEST_FLAGS_DAILY)          $result[] = LOCALE_QUEST_FLAGS_DAILY;
		if ($data['Flags'] & QUEST_FLAGS_UNK5)           $result[] = LOCALE_QUEST_FLAGS_UNK5;
	}

	// Неявно используемые доп. элементы (интересно, кто назвал эту константу "..._UNK2"?)
	if (($data['Flags'] & QUEST_FLAGS_UNK2) || $SourceItems)
	{
		$tmp = LOCALE_QUEST_FLAGS_UNK2;
		if ($SourceItems) $tmp = $tmp . " (" . implode(", ", $SourceItems) . ")";
		$result[] = $tmp;
	}

	// Специальные серверные флаги - повторяемость и завершение скриптом
	if ($data['SpecialFlags'])
	{
		if ($data['SpecialFlags'] & QUEST_SPECIALFLAGS_REPEATABLE) $result[] = LOCALE_QUEST_SPECIALFLAGS_REPEATABLE;
		if ($data['SpecialFlags'] & QUEST_SPECIALFLAGS_SCRIPTED)   $result[] = LOCALE_QUEST_SPECIALFLAGS_SCRIPTED;
	}

	// Наличие стартовых и финишных скриптов
	if ($data['PointX'] || $data['PointY']) $result[] = LOCALE_QUEST_HAS_POI;
	if ($data['StartScript'])               $result[] = LOCALE_QUEST_HAS_START_SCRIPT;
	if ($data['CompleteScript'])            $result[] = LOCALE_QUEST_HAS_COMPLETE_SCRIPT;

	return $result;
}

function GetQuestXpOrMoney($data)
{
	// From MaNGOS Sources
	$pLevel = $data['MinLevel'] + 1;
	$qLevel = $data['Level'];
	$RewardMoneyMaxLevel = $data['RewardMoneyMaxLevel'];
	if(!$RewardMoneyMaxLevel)
		return 0;
	$fullxp = 0;
	if($qLevel >= 15)
		$fullxp = $RewardMoneyMaxLevel / 6.0;
	elseif($qLevel == 14)
		$fullxp = $RewardMoneyMaxLevel / 4.8;
	elseif($qLevel == 13)
		$fullxp = $RewardMoneyMaxLevel / 3.666;
	elseif($qLevel == 12)
		$fullxp = $RewardMoneyMaxLevel / 2.4;
	elseif($qLevel == 11)
		$fullxp = $RewardMoneyMaxLevel / 1.2;
	elseif($qLevel >= 1 && $qLevel <= 10)
		$fullxp = $RewardMoneyMaxLevel / 0.6;
	elseif($qLevel == 0)
		$fullxp = $RewardMoneyMaxLevel;

	if( $pLevel <= $qLevel +  5 )
		return $fullxp;
	elseif( $pLevel == $qLevel +  6 )
		return ($fullxp * 0.8);
	elseif( $pLevel == $qLevel +  7 )
		return ($fullxp * 0.6);
	elseif( $pLevel == $qLevel +  8 )
		return ($fullxp * 0.4);
	elseif( $pLevel == $qLevel +  9 )
		return ($fullxp * 0.2);
	else
		return ($fullxp * 0.1);
}

// ????
function GetQuestTitle(&$data)
{
	$title = QuestReplaceStr(localizedName($data, 'Title'));
	$data['Title'] = $title;
	return $title;
}

function GetQuestStrings(&$data)
{
	$data['Title']				= QuestReplaceStr(				  (localizedName($data, 'Title')));
	$data['Objectives']			= QuestReplaceStr(htmlspecialchars(localizedName($data, 'Objectives')));
	$data['Details']			= QuestReplaceStr(htmlspecialchars(localizedName($data, 'Details')));
	$data['RequestItemsText']	= QuestReplaceStr(htmlspecialchars(localizedName($data, 'RequestItemsText')));
	$data['OfferRewardText']	= QuestReplaceStr(htmlspecialchars(localizedName($data, 'OfferRewardText')));
	$data['EndText']			= QuestReplaceStr(htmlspecialchars(localizedName($data, 'EndText')));

	for($j=0;$j<=3;++$j)
		$data['ObjectiveText'][$j] = QuestReplaceStr(htmlspecialchars(localizedName($data, 'ObjectiveText'.$j)));
}

function GetQuestReq($id, $count, $type)
{
	global $DB;
	switch($type)
	{
		case 1:
			$row = $DB->selectRow('
					SELECT name
						{, l.name_loc?d AS name_loc}
					FROM creature_template c
						{ LEFT JOIN (locales_creature l) ON l.entry=c.entry AND ? }
					WHERE
						c.entry = ?d
					LIMIT 1
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$id
			);
			$name = localizedName($row);
			return $name.(($count>1)? (' x'.$count): '');
			break;
		case 2:
			$row = $DB->selectRow('
					SELECT name
						{, l.name_loc?d AS name_loc}
					FROM item_template c
						{ LEFT JOIN (locales_item l) ON l.entry=c.entry AND ? }
					WHERE
						c.entry = ?d
					LIMIT 1
				',
				($_SESSION['locale']>0)? $_SESSION['locale']: DBSIMPLE_SKIP,
				($_SESSION['locale']>0)? 1: DBSIMPLE_SKIP,
				$id
			);
			$name = localizedName($row);
			return $name.(($count>1)? (' x'.$count): '');
			break;
	}
}

function GetQuestTooltip($row)
{
	$x = '';
	
	// Название квеста
	$x .= '<table><tr><td><b class="q">'.$row['Title'].'</b></td></tr></table>';

	$x .= '<table>';
	if($row['Objectives'])
	{
		$x .= '<tr><td><br>';
		$x .= $row['Objectives'];
		$x .= '</td></tr>';
	}

//	$x .= '<br>';

	if((($row['RequiredNpcOrGo1']) and ($row['RequiredNpcOrGoCount1'])) or
			(($row['RequiredNpcOrGo2']) and ($row['RequiredNpcOrGoCount2'])) or
			(($row['RequiredNpcOrGo3']) and ($row['RequiredNpcOrGoCount3'])) or
			(($row['RequiredNpcOrGo4']) and ($row['RequiredNpcOrGoCount4'])) or
			(($row['RequiredItemId1']) and ($row['RequiredItemCount1'])) or
			(($row['RequiredItemId2']) and ($row['RequiredItemCount2'])) or
			(($row['RequiredItemId3']) and ($row['RequiredItemCount3'])) or
			(($row['RequiredItemId4']) and ($row['RequiredItemCount4'])))
	{
		$x .= '<tr><td><br>';
		$x .= '<div class="q">'.LOCALE_REQUIREMENTS.':<br></div>';
		for($j=1;$j<=4;$j++)
			if($row['RequiredNpcOrGo'.$j] and $row['RequiredNpcOrGoCount'.$j])
				$x .= '- '
					.(
						(!empty($row['ObjectiveText'][$j]))?
						$row['ObjectiveText'][$j]:
						GetQuestReq($row['RequiredNpcOrGo'.$j], $row['RequiredNpcOrGoCount'.$j], 1)
					).'<br>';
		for($j=1;$j<=4;$j++)
			if($row['RequiredItemId'.$j] and $row['RequiredItemCount'.$j])
				$x .= '- '.GetQuestReq($row['RequiredItemId'.$j], $row['RequiredItemCount'.$j], 2).'<br>';
		$x .= '</td></tr>';
	}
	$x .= '</table>';

	return $x;
}

function GetQuestDBLocale($quest)
{
	global $DB;
	$data = array();
	$loc = $_SESSION['locale'];
	$row = $DB->selectRow('
			SELECT
				Title_loc?d AS Title_loc,
				Details_loc?d AS Details_loc,
				Objectives_loc?d AS Objectives_loc,
				OfferRewardText_loc?d AS OfferRewardText_loc,
				RequestItemsText_loc?d AS RequresItemsText_loc,
				EndText_loc?d AS EndText_loc,
				ObjectiveText1_loc?d AS ObjectiveText1_loc,
				ObjectiveText2_loc?d AS ObjectiveText2_loc,
				ObjectiveText3_loc?d AS ObjectiveText3_loc,
				ObjectiveText4_loc?d AS ObjectiveText4_loc
			FROM locales_quest
			WHERE Id = ?d
			LIMIT 1
		',
		$loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc,
		$quest
	);
	if($row)
		foreach($row as $item => $itemContent)
			if(!empty($itemContent))
				$data[$item] = $itemContent;
	return $data;
}

function GetDBQuestInfo($id, $dataflag = QUEST_DATAFLAG_MINIMUM)
{
	global $DB, $questcols;
	$data = $DB->selectRow('
			SELECT
				1
				{, ?# } {, ?# } {, ?# } {, ?# } {, ?# }
			FROM v_quest_template
			WHERE entry=?d
			LIMIT 1
		',
		($dataflag & QUEST_DATAFLAG_MINIMUM)?$questcols[QUEST_DATAFLAG_MINIMUM]:DBSIMPLE_SKIP,
		($dataflag & QUEST_DATAFLAG_STRINGS)?$questcols[QUEST_DATAFLAG_STRINGS]:DBSIMPLE_SKIP,
		($dataflag & QUEST_DATAFLAG_SERIES) ?$questcols[QUEST_DATAFLAG_SERIES] :DBSIMPLE_SKIP,
		($dataflag & QUEST_DATAFLAG_PROPS)  ?$questcols[QUEST_DATAFLAG_PROPS]  :DBSIMPLE_SKIP,
		($dataflag & QUEST_DATAFLAG_REWARDS)?$questcols[QUEST_DATAFLAG_REWARDS]:DBSIMPLE_SKIP,
		$id
	);
	if(!$data)
		return false;
	else
		return GetQuestInfo($data, $dataflag);
}

/*
 * &$data - ссылка на массив с данными
 * $dataflag - флаг уровень:
 * QUEST_DATAFLAG_MINIMUN	- entry, Title
 * QUEST_DATAFLAG_STRINGS	- Objectives, Details, RequestItemsText, OfferRewardText, EndText, ObjectiveText1, ObjectiveText2, ObjectiveText3, ObjectiveText4
 * QUEST_DATAFLAG_SERIES	- PrevQuestID, NextQuestIdChain, ExclusiveGroup, NextQuestID
 * QUEST_DATAFLAG_PROPS		- Daily, Type, side, etc.
 * QUEST_DATAFLAG_REWARDS	- RewardChoiceItemId1, RewardChoiceItemId2, RewardChoiceItemId3, RewardChoiceItemId4, RewardChoiceItemId5, RewardChoiceItemId6, RewardChoiceItemCount1', 'RewardChoiceItemCount2, RewardChoiceItemCount3, 'RewardChoiceItemCount4', 'RewardChoiceItemCount5', 'RewardChoiceItemCount6', 'RewardItemId1', 'RewardItemId2', 'RewardItemId3', 'RewardItemId4', 'RewardItemCount1', 'RewardItemCount2', 'RewardItemCount3', 'RewardItemCount4', 'RewardMoneyMaxLevel', 'RewardOrRequiredMoney', 'RequiredSpellCast1', 'RequiredSpellCast2', 'RequiredSpellCast3', 'RequiredSpellCast4', 'RequiredNpcOrGo1', 'RequiredNpcOrGo2', 'RequiredNpcOrGo3', 'RequiredNpcOrGo4', 'RequiredItemId1', 'RequiredItemId2', 'RequiredItemId3', 'RequiredItemId4', 'RequiredItemCount1', 'RequiredItemCount2', 'RequiredItemCount3', 'RequiredItemCount4', 'SourceItemId', 'RequiredNpcOrGoCount1', 'RequiredNpcOrGoCount2', 'RequiredNpcOrGoCount3', 'RequiredNpcOrGoCount4
 *
 */
function GetQuestInfo(&$data, $dataflag = QUEST_DATAFLAG_MINIMUM)
{
	global $DB, $quest_class, $quest_faction_reward;

	// Локализация
	if($dataflag & QUEST_DATAFLAG_LOCALE && $_SESSION['locale'] > 0)
	{
		$loc = $_SESSION['locale'];
		$row = $DB->selectRow('
				SELECT
					Title_loc?d AS Title_loc,
					Details_loc?d AS Details_loc,
					Objectives_loc?d AS Objectives_loc,
					OfferRewardText_loc?d AS OfferRewardText_loc,
					RequestItemsText_loc?d AS RequresItemsText_loc,
					EndText_loc?d AS EndText_loc,
					ObjectiveText1_loc?d AS ObjectiveText1_loc,
					ObjectiveText2_loc?d AS ObjectiveText2_loc,
					ObjectiveText3_loc?d AS ObjectiveText3_loc,
					ObjectiveText4_loc?d AS ObjectiveText4_loc
				FROM locales_quest
				WHERE Id = ?d
				LIMIT 1
			',
			$loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc, $loc,
			$data['entry']
		);

		if($row)
			$data = @array_merge($data, $row);
	}
	// Минимальные данные
	// ID квеста
	$data['Id'] = $data['Id'];
	// Имя квеста
	$data['Title'] = GetQuestTitle($data);

	// Описания
	if($dataflag & QUEST_DATAFLAG_STRINGS)
		GetQuestStrings($data);

	// Свойства
	if($dataflag & QUEST_DATAFLAG_PROPS)
	{
		// Уровень квеста
		$data['Level'] = $data['Level'];
		// Требуемый уровень квеста
		$data['MinLevel'] = $data['MinLevel'];
		// Доступен расам
		$data['side'] = races($data['RequiredRaces']);
		// Флаги
		$data['Flags'] = $data['Flags'];
		// Ежедневный квест?
		if($data['Flags'] & QUEST_FLAGS_DAILY)
			$data['Daily'] = true;
		// Тип квеста
		$data['type'] = $data['Type'];
		global $quest_type;
		if(isset($quest_type[$data['type']]))
			$data['typename'] = $quest_type[$data['type']];
		else
			$data['typename'] = $data['type'];
		// Путь к этому разделу (главная категория)
		foreach($quest_class as $i => $class)
			if(in_array($data['ZoneOrSort'], $class))
			{
				$data['maincat']=$i;
				break;
			}
		// Категория 1
		$data['category'] = $data['ZoneOrSort'];
		// Категория 2 ???
		$data['category2'] = $data['Flags'];
		// Требуемое пати
		if($data['SuggestedPlayers']>1)
			$data['splayers'] = $data['SuggestedPlayers'];
		// Лимит времени
		if($data['LimitTime']>0)
			$data['LimitTime'] = sec_to_time($data['LimitTime']);
		else
			unset($data['LimitTime']);
		if($data['Flags'] & QUEST_FLAGS_SHARABLE)
			$data['Sharable'] = true;
		if($data['SpecialFlags'] & QUEST_SPECIALFLAGS_REPEATABLE)
			$data['Repeatable'] = true;
		if($data['RewardTitleId']>0)
			$data['titlereward'] = $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_char_titles WHERE id=?d LIMIT 1', $data['RewardTitleId']);
	}

	// Награды и задания
	if($dataflag & QUEST_DATAFLAG_REWARDS)
	{
		// Опыт/деньги@70
		$data['xp'] = GetQuestXpOrMoney($data);
		// Награды вещей
		for($j=0;$j<=6;++$j)
			if(($data['RewardChoiceItemId'.$j]!=0) and ($data['RewardChoiceItemCount'.$j]!=0))
				$data['itemchoices'][] = @array_merge(
					allitemsinfo($data['RewardChoiceItemId'.$j], 0),
					array('count' => $data['RewardChoiceItemCount'.$j])
				);
		for($j=0;$j<=4;++$j)
			if(($data['RewardItemId'.$j]!=0) and ($data['RewardItemCount'.$j]!=0))
				$data['itemrewards'][] = @array_merge(
					allitemsinfo($data['RewardItemId'.$j], 0),
					array('count' => $data['RewardItemCount'.$j])
				);
		// Вознаграждение репутацией
		for($j=1;$j<=5;$j++)
			if($data['RewardFactionValueId'.$j] != 0)
			{
				$value = $data['RewardFactionValueId'.$j];
				$id = $data['RewardFactionId'.$j];
				if (!$value && isset($quest_faction_reward[$id]))
					$value=$quest_faction_reward[$id];
				if ($value)
					$data['reprewards'][] = @array_merge(factioninfo($data['RewardFactionValueId'.$j]), array('value' => $value));
			}
		// Вознаграждение деньгами
		if($data['RewardOrRequiredMoney']>0)
			$data['money'] = money2coins($data['RewardOrRequiredMoney']);
		elseif($data['RewardOrRequiredMoney']<0)
			$data['moneyreq'] = money2coins(-$data['RewardOrRequiredMoney']);
		if ($data['RewardMoneyMaxLevel'])
			$data['moneymaxlevel'] = money2coins($data['RewardMoneyMaxLevel']);
	}

	// Последовательность квестов, требования, цепочки
	if($dataflag & QUEST_DATAFLAG_SERIES)
	{
		// не используется для вычисления самих сериесов, исключительно для внесения соответствующих полей в массив информации
	}
	
	// Все ОК. Это не обязательный return, но в некоторых функциях нужен.
	return $data;
}

// just a placeholder
function allquestinfo($id)
{
	global $allquests;

	if(!$allquests[$id])
		$allquests[$id] = GetQuestInfo($id, QUEST_DATAFLAG_MINIMUM);

	return $allquests[$id];
}

?>