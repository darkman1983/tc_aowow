<?php
/*
    aowow_sql_loc - code for generating AoWoW database localization
    This file is a part of AoWoW project.
    Copyright (C) 2010  Mix <ru-mangos.ru>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
  require("config.php");

  if (!isset($config['local_dbc']))
    die("Path to localized DBC files is not configured");

  $dbcdir = $config['local_dbc'];
  $L = $config['locale'];

  require ("dbc2array.php");

  function dbc2array_($filename, $format)
  {
    global $dbcdir;
    if (@stat($dbcdir . $filename) == NULL) $filename = strtolower($filename);
    return dbc2array($dbcdir . $filename, $format);
  }

  function print_update($table, $data, $keys, $values)
  {
    foreach ($data as $row)
    {
      $where = array();
      foreach ($keys as $i => $name)
        $where[] = "`$name` = " . "'" . str_replace("\r\n", "\\n", addslashes($row[$i])) . "'";

      $set = array();
      foreach ($values as $i => $name)
        $set[] = "`$name` = " . "'" . str_replace("\r\n", "\\n", addslashes($row[$i])) . "'";

      echo "UPDATE $table SET " , implode(", ", $set) , " WHERE " , implode(" AND ", $where), ";\n";
    }
  }

  // function that converts format string to specified locale
  // i.e. "nSxxxxxxxxxxxxxxxx" => "nxxxxxxxxsxxxxxxxx"
  function L($format)
  {
    global $L;
    $result = $format;
    for ($i=0; $i<strlen($format); $i++)
      if ($format[$i] == 'S')
      {
        $result[$i] = 'x';
        $result[$i+$L] = 's';
      }
    return $result;
  }
?>
-- Prevent data corruption
SET NAMES 'utf8';
SET SQL_MODE = '';

-- Altering AoWoW Table Structure.
ALTER TABLE aowow_achievement
	ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`,
	ADD COLUMN `description_loc<?=$L?>` varchar(255) AFTER `description_loc0`,
	ADD COLUMN `reward_loc<?=$L?>` varchar(255) AFTER `reward_loc0`;
ALTER TABLE aowow_achievementcriteria
	ADD COLUMN `name_loc<?=$L?>` varchar(255) AFTER `name_loc0`;
ALTER TABLE aowow_achievementcategory
	ADD COLUMN `name_loc<?=$L?>` varchar(255) AFTER `name_loc0`;
ALTER TABLE aowow_char_titles ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_skill ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_spelldispeltype ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_spellmechanic ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_resistances ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_itemset ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_spellrange ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_zones ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`;
ALTER TABLE aowow_factions
	ADD COLUMN `name_loc<?=$L?>` varchar(255) NOT NULL AFTER `name_loc0`,
	ADD COLUMN `description1_loc<?=$L?>` text AFTER `description1_loc0`,
	ADD COLUMN `description2_loc<?=$L?>` text AFTER `description2_loc0`;
ALTER TABLE aowow_itemenchantmet ADD COLUMN `text_loc<?=$L?>` text NOT NULL AFTER `text_loc0`;
ALTER TABLE aowow_spell
	ADD COLUMN `spellname_loc<?=$L?>` varchar(255) NOT NULL AFTER `spellname_loc0`,
	ADD COLUMN `rank_loc<?=$L?>` text NOT NULL AFTER `rank_loc0`,
	ADD COLUMN `tooltip_loc<?=$L?>` text NOT NULL AFTER `tooltip_loc0`,
	ADD COLUMN `buff_loc<?=$L?>` text NOT NULL AFTER `buff_loc0`;
ALTER TABLE aowow_talenttab ADD COLUMN `name_loc<?=$L?>` varchar(32) NOT NULL AFTER `name_loc0`;
-- Writing data into files
<?php
  $dbc = dbc2array_("Achievement_Category.dbc", L("nxSxxxxxxxxxxxxxxxxx"));
  print_update('aowow_achievementcategory', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("Achievement.dbc", L("nxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_achievement', $dbc, array(0=>"id"), array(1=>"name_loc$L", 2=>"description_loc$L", 3=>"reward_loc$L"));

  $dbc = dbc2array_("Achievement_Criteria.dbc", L("nxxxxxxxxSxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_achievementcriteria', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("CharTitles.dbc", L("nxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_char_titles', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("SkillLine.dbc", L("nxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_skill', $dbc, array(0=>"skillID"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("SpellDispelType.dbc", L("nSxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_spelldispeltype', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("SpellMechanic.dbc", L("nSxxxxxxxxxxxxxxxx"));
  print_update('aowow_spellmechanic', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("Resistances.dbc", L("nxxSxxxxxxxxxxxxxxxx"));
  print_update('aowow_resistances', $dbc, array(0=>"id"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("ItemSet.dbc", L("nSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_itemset', $dbc, array(0=>"itemsetID"), array(1=>"name_loc$L"));

  $dbc = dbc2array_("SpellRange.dbc", L("nxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_spellrange', $dbc, array(0=>"rangeID"), array(1=>"name_loc$L"));

/*
  /// My version of zones extraction (based on english extraction code)
  $dbc = dbc2array_("AreaTable.dbc", L("nxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxx"));
  $mapnames = array();
  foreach ($dbc as $row) $mapnames[$row[0]] = $row[1];

  $dbc = array();
  // Instance maps
  $dbc_tmp = dbc2array_("Map.dbc", L("nxixSxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  foreach ($dbc_tmp as $row)
    if ($row[1] > 0)
      $dbc[] = array($row[0], $row[3], $row[2]);

  // Regular maps
  $dbc_tmp = dbc2array_("WorldMapArea.dbc", "xiisffffxx");
  foreach ($dbc_tmp as $row)
    if (isset($mapnames[$row[1]]) && !empty($mapnames[$row[1]]))
      $dbc[] = array($row[0], $row[1], $mapnames[$row[1]]);
  unset($mapnames);
  unset($dbc_tmp);
  print_update('aowow_zones', $dbc, array(0=>"mapID", 1=>"areatableID"), array(2=>"name_loc$L"));
*/

  /// Alternative version of zones extraction (based on Fog's file)
  //$dbc = dbc2array_("AreaTable.dbc", L("nxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxx"));
  //print_update('aowow_zones', $dbc, array(0=>"areatableID"), array(1=>"name_loc$L"));
  //$dbc = dbc2array_("Map.dbc", L("nxixSxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  //print_update('aowow_zones', $dbc, array(0=>"mapID", 3=>"areatableID"), array(2=>"name_loc$L"));

  /// debug check for collisions in Fog's version (shows >20 collisions)
  //$dbc = dbc2array_("AreaTable.dbc", L("nxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxx"));
  //$mapnames = array();
  //foreach ($dbc as $row) $mapnames[$row[0]] = $row[1];
  //$dbc = dbc2array_("Map.dbc", L("nxixSxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  //foreach ($dbc as $row)
  //  if (isset($mapnames[$row[3]]) && $mapnames[$row[3]] != $row[2])
  //    echo "Collision: '", $row[2], "' (", $row[0], ",", $row[3], ") != '", $mapnames[$row[3]], "'\n";
  //unset($mapnames);

  /// Last version, the most simple one
  $dbc = dbc2array_("AreaTable.dbc", L("nxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_zones', $dbc, array(0=>"areatableID"), array(1=>"name_loc$L"));


  $dbc = dbc2array_("Faction.dbc", L("nxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxx"));
  print_update('aowow_factions', $dbc, array(0=>"factionID"), array(1=>"name_loc$L", 2=>"description1_loc$L"));

  $dbc = dbc2array_("SpellItemEnchantment.dbc", L("nxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_itemenchantmet', $dbc, array(0=>"itemenchantmetID"), array(1=>"text_loc$L"));

// 3.1.3.new: nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
// 3.2.2a:    nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
// 3.3.2:     nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
// 3.3.3a:    nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

  $dbc = dbc2array_("Spell.dbc", L("nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxSxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_spell', $dbc, array(0=>"spellID"), array(1=>"spellname_loc$L", 2=>"rank_loc$L", 3=>"tooltip_loc$L", 4=>"buff_loc$L"));

  $dbc = dbc2array_("TalentTab.dbc", L("nSxxxxxxxxxxxxxxxxxxxxxx"));
  print_update('aowow_talenttab', $dbc, array(0=>"id"), array(1=>"name_loc$L"));
?>
