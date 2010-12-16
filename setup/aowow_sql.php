<?php
/*
    aowow_sql - code for generating main AoWoW database from client files
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

  if (!isset($config['english_dbc']))
    die("Path to english DBC files is not configured");

  $dbcdir = $config['english_dbc'];

  require ("dbc2array.php");

  function dbc2array_($filename, $format)
  {
    global $dbcdir;
    if (@stat($dbcdir . $filename) == NULL) $filename = strtolower($filename);
    return dbc2array($dbcdir . $filename, $format);
  }

  function print_insert($header, $data)
  {
    $size = 0;
    echo "$header\n";
    foreach ($data as $row)
    {
      if ($size)
      {
        if ($size > 937420)
        {
          echo ";\n\n\n$header\n";
          $size = 0;
        }
        else
          echo ",\n";
      }

      // quote strings
      foreach ($row as $i => $value)
        if (!is_int($value) && !is_float($value))
          $row[$i] = "'" . str_replace("\r\n", "\\n", addslashes($value)) . "'";

      $outstr = "(" . implode(", ", $row) . ")";
      $size += strlen($outstr);
      echo $outstr;
    }
    echo ";\n\n";
  }
?>
-- Prevent data corruption
SET NAMES 'utf8';
SET SQL_MODE = '';

-- Achievement_Category.dbc
DROP TABLE IF EXISTS `aowow_achievementcategory`;
CREATE TABLE `aowow_achievementcategory` (
  `id` mediumint(11) unsigned NOT NULL,
  `parentAchievement` mediumint(11) NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_achievement`(`parentAchievement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("Achievement_Category.dbc", "nisxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_achievementcategory` VALUES', $dbc);
?>
-- Achievement.dbc
DROP TABLE IF EXISTS `aowow_achievement`;
CREATE TABLE `aowow_achievement` (
  `id` mediumint(11) unsigned NOT NULL,
  `faction` tinyint(1) NOT NULL,
  `map` mediumint(11) NOT NULL,
  `parent` mediumint(11) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `description_loc0` varchar(255) NOT NULL,
  `category` mediumint(11) unsigned NOT NULL,
  `points` mediumint(11) unsigned NOT NULL,
  `order` mediumint(11) unsigned NOT NULL,
  `flags` mediumint(11) unsigned NOT NULL,
  `icon` mediumint(11) unsigned NOT NULL,
  `reward_loc0` varchar(255) NOT NULL,
  `count` mediumint(11) unsigned NOT NULL,
  `refAchievement` mediumint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_category`(`category`),
  INDEX `idx_parent`(`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("Achievement.dbc", "niiisxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxiiiiisxxxxxxxxxxxxxxxxii");
  print_insert('INSERT INTO `aowow_achievement` VALUES', $dbc);
?>
-- Achievement_Criteria.dbc
DROP TABLE IF EXISTS `aowow_achievementcriteria`;
CREATE TABLE `aowow_achievementcriteria` (
  `id` mediumint(11) unsigned NOT NULL,
  `refAchievement` mediumint(11) unsigned NOT NULL,
  `type` mediumint(11) NOT NULL,
  `value1` int(11) NOT NULL,
  `value2` int(11) NOT NULL,
  `value3` int(11) NOT NULL,
  `value4` int(11) NOT NULL,
  `value5` int(11) NOT NULL,
  `value6` int(11) NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `complete_flags` mediumint(11) unsigned NOT NULL,
  `group_flags` mediumint(11) unsigned NOT NULL,
  `timelimit` mediumint(11) unsigned NOT NULL,
  `order` mediumint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_achievement`(`refAchievement`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("Achievement_Criteria.dbc", "niiiiiiiisxxxxxxxxxxxxxxxxiixii");
  print_insert('INSERT INTO `aowow_achievementcriteria` VALUES', $dbc);
?>
-- GlyphProperties.dbc
DROP TABLE IF EXISTS `aowow_glyphproperties`;
CREATE TABLE `aowow_glyphproperties` (
  `id` mediumint(11) NOT NULL,
  `spellid` mediumint(11) NOT NULL,
  `typeflags` mediumint(11) NOT NULL,
  `iconid` mediumint(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("GlyphProperties.dbc", "niii");
  print_insert('INSERT INTO `aowow_glyphproperties` VALUES', $dbc);
?>
-- Item.dbc
DROP TABLE IF EXISTS `aowow_items`;
CREATE TABLE `aowow_items` (
  `entry` mediumint(8) unsigned NOT NULL default '0',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `subclass` tinyint(3) unsigned NOT NULL default '0',
  `Material` tinyint(4) NOT NULL default '0',
  `displayid` mediumint(8) unsigned NOT NULL default '0',
  `InventoryType` tinyint(3) unsigned NOT NULL default '0',
  `sheath` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entry`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("Item.dbc", "niixiiii");
  print_insert('INSERT INTO `aowow_items` VALUES', $dbc);
?>
-- CharTitles.dbc
DROP TABLE IF EXISTS `aowow_char_titles`;
CREATE TABLE `aowow_char_titles` (
  `id` mediumint(11) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("CharTitles.dbc", "nxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_char_titles` (id,name_loc0) VALUES', $dbc);
?>
-- ItemExtendedCost.dbc
DROP TABLE IF EXISTS `aowow_item_extended_cost`;
CREATE TABLE `aowow_item_extended_cost` (
  `extendedcostID` mediumint(11) unsigned NOT NULL,
  `reqhonorpoints` mediumint(11) unsigned NOT NULL,
  `reqarenapoints` mediumint(11) unsigned NOT NULL,
  `reqitem1` mediumint(11) unsigned NOT NULL,
  `reqitem2` mediumint(11) unsigned NOT NULL,
  `reqitem3` mediumint(11) unsigned NOT NULL,
  `reqitem4` mediumint(11) unsigned NOT NULL,
  `reqitem5` mediumint(11) unsigned NOT NULL,
  `reqitemcount1` mediumint(11) unsigned NOT NULL,
  `reqitemcount2` mediumint(11) unsigned NOT NULL,
  `reqitemcount3` mediumint(11) unsigned NOT NULL,
  `reqitemcount4` mediumint(11) unsigned NOT NULL,
  `reqitemcount5` mediumint(11) unsigned NOT NULL,
  `reqpersonalarenarating` mediumint(11) unsigned NOT NULL,
  PRIMARY KEY  (`extendedcostID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("ItemExtendedCost.dbc", "niixiiiiiiiiiiix");
  print_insert('INSERT INTO `aowow_item_extended_cost` VALUES', $dbc);
?>
-- SkillLineAbility.dbc
DROP TABLE IF EXISTS `aowow_skill_line_ability`;
CREATE TABLE `aowow_skill_line_ability` (
  `skillID` mediumint(11) unsigned NOT NULL,
  `spellID` mediumint(11) unsigned NOT NULL,
  `racemask` mediumint(11) unsigned NOT NULL,
  `classmask` mediumint(11) unsigned NOT NULL,
  `req_skill_value` mediumint(11) unsigned NOT NULL,
  `max_value` mediumint(11) unsigned NOT NULL,
  `min_value` mediumint(11) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;
ALTER TABLE `aowow_skill_line_ability` ADD INDEX `spellID`(`spellID`);

<?php
  $dbc = dbc2array_("SkillLineAbility.dbc", "xiiiixxixxiixx");
  print_insert('INSERT INTO `aowow_skill_line_ability` VALUES', $dbc);
?>
-- SkillLine.dbc
DROP TABLE IF EXISTS `aowow_skill`;
CREATE TABLE `aowow_skill` (
  `skillID` mediumint(11) unsigned NOT NULL,
  `categoryID` mediumint(11) NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`skillID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SkillLine.dbc", "nixsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_skill` VALUES', $dbc);
?>
-- SpellDispelType.dbc
DROP TABLE IF EXISTS `aowow_spelldispeltype`;
CREATE TABLE `aowow_spelldispeltype` (
  `id` mediumint(11) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellDispelType.dbc", "nsxxxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_spelldispeltype` VALUES', $dbc);
?>
-- SpellMechanic.dbc
DROP TABLE IF EXISTS `aowow_spellmechanic`;
CREATE TABLE `aowow_spellmechanic` (
  `id` mediumint(11) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellMechanic.dbc", "nsxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_spellmechanic` VALUES', $dbc);
?>
-- Resistances.dbc
DROP TABLE IF EXISTS `aowow_resistances`;
CREATE TABLE `aowow_resistances` (
  `id` mediumint(11) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("Resistances.dbc", "nxxsxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_resistances` VALUES', $dbc);
?>
-- SpellCastTimes.dbc
DROP TABLE IF EXISTS `aowow_spellcasttimes`;
CREATE TABLE `aowow_spellcasttimes` (
  `id` mediumint(11) unsigned NOT NULL,
  `base` mediumint(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellCastTimes.dbc", "nixx");
  print_insert('INSERT INTO `aowow_spellcasttimes` VALUES', $dbc);
?>
-- SpellIcon.dbc
DROP TABLE IF EXISTS `aowow_spellicons`;
CREATE TABLE `aowow_spellicons` (
  `id` int(11) unsigned NOT NULL default '0' COMMENT 'Icon Identifier',
  `iconname` varchar(50) NOT NULL default '' COMMENT 'Icon Name',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Icon Names';

<?php
  $dbc = dbc2array_("SpellIcon.dbc", "ns");
  // strip path like 'Interface\Icons\', leave only icon name
  foreach ($dbc as $i => $row) $dbc[$i][1] = substr(strrchr($dbc[$i][1],"\\"),1);
  print_insert('INSERT INTO `aowow_spellicons` VALUES', $dbc);
?>
-- Lock.dbc
DROP TABLE IF EXISTS `aowow_lock`;
CREATE TABLE `aowow_lock` (
  `lockID` mediumint(11) unsigned NOT NULL,
  `type1` tinyint(1) unsigned NOT NULL,
  `type2` tinyint(1) unsigned NOT NULL,
  `type3` tinyint(1) unsigned NOT NULL,
  `type4` tinyint(1) unsigned NOT NULL,
  `type5` tinyint(1) unsigned NOT NULL,
  `lockproperties1` mediumint(11) unsigned NOT NULL,
  `lockproperties2` mediumint(11) unsigned NOT NULL,
  `lockproperties3` mediumint(11) unsigned NOT NULL,
  `lockproperties4` mediumint(11) unsigned NOT NULL,
  `lockproperties5` mediumint(11) unsigned NOT NULL,
  `requiredskill1` mediumint(11) unsigned NOT NULL,
  `requiredskill2` mediumint(11) unsigned NOT NULL,
  `requiredskill3` mediumint(11) unsigned NOT NULL,
  `requiredskill4` mediumint(11) unsigned NOT NULL,
  `requiredskill5` mediumint(11) unsigned NOT NULL,
  PRIMARY KEY  (`lockID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Locks';

<?php
  $dbc = dbc2array_("Lock.dbc", "niiiiixxxiiiiixxxiiiiixxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_lock` VALUES', $dbc);
?>
-- ItemDisplayInfo.dbc
DROP TABLE IF EXISTS `aowow_icons`;
CREATE TABLE `aowow_icons` (
  `id` int(11) unsigned NOT NULL default '0' COMMENT 'Icon Identifier',
  `iconname` varchar(255) NOT NULL default '' COMMENT 'Icon Name',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Icon Names';

<?php
  $dbc = dbc2array_("ItemDisplayInfo.dbc", "nxxxxsxxxxxxxxxxxxxxxxxxx");
  // replace empty icons with icon "Temp"
  foreach ($dbc as $i => $row) if (!$dbc[$i][1]) $dbc[$i][1] = "Temp";
  print_insert('INSERT INTO `aowow_icons` VALUES', $dbc);
?>
-- ItemSet.dbc
DROP TABLE IF EXISTS `aowow_itemset`;
CREATE TABLE `aowow_itemset` (
  `itemsetID` smallint(3) unsigned NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `item1` mediumint(11) unsigned NOT NULL,
  `item2` mediumint(11) unsigned NOT NULL,
  `item3` mediumint(11) unsigned NOT NULL,
  `item4` mediumint(11) unsigned NOT NULL,
  `item5` mediumint(11) unsigned NOT NULL,
  `item6` mediumint(11) unsigned NOT NULL,
  `item7` mediumint(11) unsigned NOT NULL,
  `item8` mediumint(11) unsigned NOT NULL,
  `item9` mediumint(11) unsigned NOT NULL,
  `item10` mediumint(11) unsigned NOT NULL,
  `spell1` mediumint(5) unsigned NOT NULL,
  `spell2` mediumint(5) unsigned NOT NULL,
  `spell3` mediumint(5) unsigned NOT NULL,
  `spell4` mediumint(5) unsigned NOT NULL,
  `spell5` mediumint(5) unsigned NOT NULL,
  `spell6` mediumint(5) unsigned NOT NULL,
  `spell7` mediumint(5) unsigned NOT NULL,
  `spell8` mediumint(5) unsigned NOT NULL,
  `bonus1` tinyint(1) unsigned NOT NULL,
  `bonus2` tinyint(1) unsigned NOT NULL,
  `bonus3` tinyint(1) unsigned NOT NULL,
  `bonus4` tinyint(1) unsigned NOT NULL,
  `bonus5` tinyint(1) unsigned NOT NULL,
  `bonus6` tinyint(1) unsigned NOT NULL,
  `bonus7` tinyint(1) unsigned NOT NULL,
  `bonus8` tinyint(1) unsigned NOT NULL,
  `skillID` smallint(3) unsigned NOT NULL,
  `skilllevel` smallint(3) unsigned NOT NULL,
  PRIMARY KEY  (`itemsetID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("ItemSet.dbc", "nsxxxxxxxxxxxxxxxxiiiiiiiiiixxxxxxxiiiiiiiiiiiiiiiiii");
  print_insert('INSERT INTO `aowow_itemset` VALUES', $dbc);
?>
-- Spell.dbc
DROP TABLE IF EXISTS `aowow_spell`;
CREATE TABLE `aowow_spell` (
  `spellID` mediumint(11) unsigned NOT NULL,
  `resistancesID` mediumint(11) unsigned NOT NULL,
  `dispeltypeID` mediumint(11) unsigned NOT NULL,
  `mechanicID` mediumint(11) unsigned NOT NULL,
  `spellcasttimesID` mediumint(11) unsigned NOT NULL,
  `cooldown` int(11) unsigned NOT NULL,
  `AuraInterruptFlags` int(11) unsigned NOT NULL,
  `ChannelInterruptFlags` int(11) unsigned NOT NULL, 
  `procChance` mediumint(11) unsigned NOT NULL,
  `procCharges` mediumint(11) unsigned NOT NULL,
  `levelspell` mediumint(11) unsigned NOT NULL, 
  `durationID` smallint(3) unsigned NOT NULL,
  `manacost` mediumint(11) unsigned NOT NULL, 
  `rangeID` tinyint(3) unsigned NOT NULL,
  `stack` mediumint(11) unsigned NOT NULL,
  `tool1` mediumint(11) unsigned NOT NULL, 
  `tool2` mediumint(11) unsigned NOT NULL, 
  `reagent1` mediumint(11) NOT NULL, 
  `reagent2` mediumint(11) NOT NULL, 
  `reagent3` mediumint(11) NOT NULL, 
  `reagent4` mediumint(11) NOT NULL, 
  `reagent5` mediumint(11) NOT NULL, 
  `reagent6` mediumint(11) NOT NULL, 
  `reagent7` mediumint(11) NOT NULL, 
  `reagent8` mediumint(11) NOT NULL, 
  `reagentcount1` mediumint(11) NOT NULL, 
  `reagentcount2` mediumint(11) NOT NULL, 
  `reagentcount3` mediumint(11) NOT NULL, 
  `reagentcount4` mediumint(11) NOT NULL, 
  `reagentcount5` mediumint(11) NOT NULL, 
  `reagentcount6` mediumint(11) NOT NULL, 
  `reagentcount7` mediumint(11) NOT NULL, 
  `reagentcount8` mediumint(11) NOT NULL, 
  `effect1id` mediumint(11) unsigned NOT NULL,
  `effect2id` mediumint(11) unsigned NOT NULL,
  `effect3id` mediumint(11) unsigned NOT NULL,
  `effect1DieSides` int(11) signed NOT NULL,
  `effect2DieSides` int(11) signed NOT NULL,
  `effect3DieSides` int(11) signed NOT NULL,
  `effect1BasePoints` int(11) signed NOT NULL,
  `effect2BasePoints` int(11) signed NOT NULL,
  `effect3BasePoints` int(11) signed NOT NULL,
  `effect1targetA` smallint(5) signed NOT NULL,
  `effect2targetA` smallint(5) signed NOT NULL,
  `effect3targetA` smallint(5) signed NOT NULL,
  `effect1targetB` smallint(5) signed NOT NULL,
  `effect2targetB` smallint(5) signed NOT NULL,
  `effect3targetB` smallint(5) signed NOT NULL,
  `effect1radius` mediumint(11) unsigned NOT NULL,
  `effect2radius` mediumint(11) unsigned NOT NULL,
  `effect3radius` mediumint(11) unsigned NOT NULL,
  `effect1Aura` smallint(5) unsigned NOT NULL,
  `effect2Aura` smallint(5) unsigned NOT NULL,
  `effect3Aura` smallint(5) unsigned NOT NULL,
  `effect1Amplitude` mediumint(11) unsigned NOT NULL,
  `effect2Amplitude` mediumint(11) unsigned NOT NULL,
  `effect3Amplitude` mediumint(11) unsigned NOT NULL,
  `effect_1_proc_value` float NOT NULL,
  `effect_2_proc_value` float NOT NULL,
  `effect_3_proc_value` float NOT NULL,
  `effect1ChainTarget` mediumint(11) unsigned NOT NULL,
  `effect2ChainTarget` mediumint(11) unsigned NOT NULL,
  `effect3ChainTarget` mediumint(11) unsigned NOT NULL,
  `effect1itemtype` int(11) NOT NULL, 
  `effect2itemtype` int(11) NOT NULL, 
  `effect3itemtype` int(11) NOT NULL, 
  `effect1MiscValue` int(11) NOT NULL,
  `effect2MiscValue` int(11) NOT NULL,
  `effect3MiscValue` int(11) NOT NULL,
  `effect1triggerspell` mediumint(11) unsigned NOT NULL,
  `effect2triggerspell` mediumint(11) unsigned NOT NULL,
  `effect3triggerspell` mediumint(11) unsigned NOT NULL,
  `effect_1_proc_chance` float NOT NULL,
  `effect_2_proc_chance` float NOT NULL,
  `effect_3_proc_chance` float NOT NULL,
  `spellicon` mediumint(11) unsigned NOT NULL, 
  `spellname_loc0` varchar(255) NOT NULL,
  `rank_loc0` text NOT NULL,
  `tooltip_loc0` text NOT NULL,
  `buff_loc0` text NOT NULL,
  `manacostpercent` mediumint(11) unsigned NOT NULL,
  `affected_target_level` mediumint(11) unsigned NOT NULL,
  `spellTargets` tinyint(3) unsigned NOT NULL,
  `dmg_multiplier1` float NOT NULL,
  `dmg_multiplier2` float NOT NULL,
  `dmg_multiplier3` float NOT NULL,
  PRIMARY KEY  (`spellID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Spells';

<?php
//  3.0.9.old: "niiixxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxxiiiiiiiiiiiiiiiiiixxxiiiiiixxxxxxxxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxx"
//  3.1.3.old: "niiixxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxxiiiiiiiiiiiiiiiiiixxxiiiiiixxxxxxxxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxx"
//  3.1.3.new: "niiixxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxiiiiiiiiiiiiiiiiiiixxxiiiiiixxxxxxxxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxx"
//  3.2.2a:    "niiixxxxxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxiiiiiiiiiiiiiiiiiiixxxiiiiiixxxxxxxxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxxxxxx"
//  3.3.2:     "niiixxxxxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxiiiiiiiiiiiiiiiiiiixxxiiiiiixxxxxxxxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxxxxxxx"
//  3.3.3a:    "niiixxxxxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxiiiiiiiiiiiiiiiiiiixxxiiiiiixxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxxxxxxx"
  $dbc = dbc2array_("Spell.dbc", "niiixxxxxxxxxxxxxxxxxxxxxxxxiixxiixiixxiixixxxixxiiiiiiiiiiiiiiiiiiixxxiiiiiixxxiiixxxiiiiiiiiiiiiiiifffiiiiiiiiixxxiiifffxxxxxxxxxxxixxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxixxixxxxixxxfffxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_spell` VALUES', $dbc);
?>
-- SpellDuration.dbc
DROP TABLE IF EXISTS `aowow_spellduration`;
CREATE TABLE `aowow_spellduration` (
  `durationID` smallint(3) unsigned NOT NULL,
  `durationBase` int(10) signed NOT NULL,
  PRIMARY KEY  (`durationID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellDuration.dbc", "nixx");
  print_insert('INSERT INTO `aowow_spellduration` VALUES', $dbc);
?>
-- SpellRange.dbc
DROP TABLE IF EXISTS `aowow_spellrange`;
CREATE TABLE `aowow_spellrange` (
  `rangeID` mediumint(11) unsigned NOT NULL,
  `rangeMin` float NOT NULL,
  `rangeMinFriendly` float NOT NULL,
  `rangeMax` float NOT NULL,
  `rangeMaxFriendly` float NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  PRIMARY KEY  (`rangeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellRange.dbc", "nffffxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_spellrange` VALUES', $dbc);
?>
-- SpellRadius.dbc
DROP TABLE IF EXISTS `aowow_spellradius`;
CREATE TABLE `aowow_spellradius` (
  `radiusID` smallint(5) unsigned NOT NULL,
  `radiusBase` float NOT NULL,
  PRIMARY KEY  (`radiusID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("SpellRadius.dbc", "nfxx");
  print_insert('INSERT INTO `aowow_spellradius` VALUES', $dbc);
?>
-- SpellItemEnchantment.dbc
DROP TABLE IF EXISTS `aowow_itemenchantmet`;
CREATE TABLE `aowow_itemenchantmet` (
  `itemenchantmetID` smallint(3) unsigned NOT NULL,
  `text_loc0` text NOT NULL,
  PRIMARY KEY  (`itemenchantmetID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  // 3.0.9: "nxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxxxxxxx"
  // 3.1.3: "nxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxxxxxxxx"
  $dbc = dbc2array_("SpellItemEnchantment.dbc", "nxxxxxxxxxxxxxsxxxxxxxxxxxxxxxxxxxxxxx");
  print_insert('INSERT INTO `aowow_itemenchantmet` VALUES', $dbc);
?>
-- GemProperties.dbc
DROP TABLE IF EXISTS `aowow_gemproperties`;
CREATE TABLE `aowow_gemproperties` (
  `gempropertiesID` smallint(3) unsigned NOT NULL,
  `itemenchantmetID` smallint(3) unsigned NOT NULL,
  PRIMARY KEY  (`gempropertiesID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("GemProperties.dbc", "nixxx");
  print_insert('INSERT INTO `aowow_gemproperties` VALUES', $dbc);
?>
-- Talent.dbc
DROP TABLE IF EXISTS `aowow_talent`;
CREATE TABLE `aowow_talent` (
  `id` mediumint(11) unsigned default '0',
  `tab` mediumint(11) unsigned default '0',
  `row` tinyint(2) unsigned default '0',
  `col` tinyint(2) unsigned default '0',
  `rank1` mediumint(11) unsigned default '0',
  `rank2` mediumint(11) unsigned default '0',
  `rank3` mediumint(11) unsigned default '0',
  `rank4` mediumint(11) unsigned default '0',
  `rank5` mediumint(11) unsigned default '0',
  `dependsOn` mediumint(11) unsigned default '0',
  `dependsOnRank` mediumint(11) unsigned default '0',
  `petmask` int(11) unsigned default '0',
  PRIMARY KEY(`id`),
  UNIQUE KEY `pos` (`tab`,`row`,`col`,`petmask`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  // WARNING!!! BUG!!! Bug-compatible with Fog.
  // Actually petmask is 64-bit. But it does not matter since it's not used.
  $dbc = dbc2array_("Talent.dbc", "niiiiiiiixxxxixxixxxxix");
  print_insert('INSERT INTO `aowow_talent` VALUES', $dbc);
?>
-- TalentTab.dbc
DROP TABLE IF EXISTS `aowow_talenttab`;
CREATE TABLE `aowow_talenttab` (
  `id` mediumint(11) unsigned default '0',
  `name_loc0` varchar(32) NOT NULL default '',
  `classes` mediumint(11) unsigned default '0',
  `pets` mediumint(11) unsigned default '0',
  `order` tinyint(1) unsigned default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pos` (`classes`,`pets`,`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("TalentTab.dbc", "nsxxxxxxxxxxxxxxxxxxiiix");
  print_insert('INSERT INTO `aowow_talenttab` VALUES', $dbc);
?>
-- FactionTemplate.dbc
DROP TABLE IF EXISTS `aowow_factiontemplate`;
CREATE TABLE `aowow_factiontemplate` (
  `factiontemplateID` smallint(4) unsigned NOT NULL,
  `factionID` smallint(4) NOT NULL,
  `A` smallint(1) NOT NULL COMMENT 'Aliance: -1 - hostile, 1 - friendly, 0 - neutral',
  `H` smallint(1) NOT NULL COMMENT 'Horde: -1 - hostile, 1 - friendly, 0 - neutral',
  PRIMARY KEY  (`factiontemplateID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
  $dbc = dbc2array_("FactionTemplate.dbc", "nixiiiiiiiiiii");

  // Generate Friendly/Hostile fields for Alliance and Horde
  $hordeID = 1801;
  $hordeRow = array();
  foreach ($dbc as $row) if ($row[0] == $hordeID) $hordeRow = $row;
  $allianceID = 1802;
  $allianceRow = array();
  foreach ($dbc as $row) if ($row[0] == $allianceID) $allianceRow = $row;

  // These two arrays are used later in aowow_factions table
  $factionA = array();
  $factionH = array();

  foreach ($dbc as $i => $row)
  {
    // Calculate A/H fields
    $A = 0;
    if ($row[5]==$allianceID || $row[6]==$allianceID || $row[7]==$allianceID || $row[7]==$allianceID)
      $A = -1;
    else if ($row[9]==$allianceID || $row[10]==$allianceID || $row[11]==$allianceID || $row[12]==$allianceID)
      $A = 1;
    else if (($row[3] & $allianceRow[2]) != 0 || ($row[2] & $allianceRow[3]) != 0)
      $A = 1;
    else if (($row[4] & $allianceRow[2]) != 0 || ($row[2] & $allianceRow[4]) != 0)
      $A = -1;
    $factionA[$row[1]] = $A;

    $H = 0;
    if ($row[5]==$hordeID || $row[6]==$hordeID || $row[7]==$hordeID || $row[7]==$hordeID)
      $H = -1;
    else if ($row[9]==$hordeID || $row[10]==$hordeID || $row[11]==$hordeID || $row[12]==$hordeID)
      $H = 1;
    else if (($row[3] & $hordeRow[2]) != 0 || ($row[2] & $hordeRow[3]) != 0)
      $H = 1;
    else if (($row[4] & $hordeRow[2]) != 0 || ($row[2] & $hordeRow[4]) != 0)
      $H = -1;
    $factionH[$row[1]] = $H;

    // Set A/H fields and remove others
    $dbc[$i][2] = $A;
    $dbc[$i][3] = $H;
    unset($dbc[$i][4]);
    unset($dbc[$i][5]);
    unset($dbc[$i][6]);
    unset($dbc[$i][7]);
    unset($dbc[$i][8]);
    unset($dbc[$i][9]);
    unset($dbc[$i][10]);
    unset($dbc[$i][11]);
    unset($dbc[$i][12]);
  }

  print_insert('INSERT INTO `aowow_factiontemplate` VALUES', $dbc);
?>
-- Faction.dbc
DROP TABLE IF EXISTS `aowow_factions`;
CREATE TABLE `aowow_factions` (
  `factionID` mediumint(11) unsigned NOT NULL,
  `reputationListID` mediumint(11) NOT NULL,
  `team` mediumint(11) unsigned NOT NULL,
  `side` tinyint(1) NOT NULL,
  `name_loc0` varchar(255) NOT NULL,
  `description1_loc0` text COMMENT 'Description from client',
  `description2_loc0` text COMMENT 'Description from wowwiki.com',
  PRIMARY KEY  (`factionID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

<?php
//  3.2.2a:    "nixxxxxxxxxxxxxxxxisixxxxxxxxxxxxxxxsxxxxxxxxxxxxxxsx"
//  3.3.2:     "nixxxxxxxxxxxxxxxxixxxxsixxxxxxxxxxxxxxxsxxxxxxxxxxxxxxsx"
  $dbc = dbc2array_("Faction.dbc", "nixxxxxxxxxxxxxxxxixxxxsixxxxxxxxxxxxxxxsxxxxxxxxxxxxxxsx");
  // Calculate side
  foreach ($dbc as $i => $row)
  {
    $dbc[$i][4] = $dbc[$i][3]; // put 'name' on it's place
    // Get side from aowow_factiontemplate values
    $dbc[$i][3] = 0;
    if (isset($factionA[$row[0]]) && isset($factionH[$row[0]]))
    {
      if ($factionA[$row[0]] == 1 && $factionH[$row[0]] == -1)
        $dbc[$i][3] = 1;
      if ($factionA[$row[0]] == -1 && $factionH[$row[0]] == 1)
        $dbc[$i][3] = 2;
    }
  }
  print_insert('INSERT INTO `aowow_factions` VALUES', $dbc);
?>
-- WorldMapArea.dbc, Map.dbc, AreaTable.dbc
DROP TABLE IF EXISTS `aowow_zones`;
CREATE TABLE `aowow_zones` (
  `mapID` smallint(3) unsigned NOT NULL COMMENT 'Map Identifier',
  `areatableID`  smallint(3) unsigned NOT NULL COMMENT 'Zone Id',
  `name_loc0` varchar(255) NOT NULL COMMENT 'Map Name',
  `x_min` float NOT NULL DEFAULT 0.0,
  `y_min` float NOT NULL DEFAULT 0.0,
  `x_max` float NOT NULL DEFAULT 0.0,
  `y_max` float NOT NULL DEFAULT 0.0,
  `type` tinyint(2) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Maps';

<?php
/*
  // Old Fog-like generation version
  $dbc = dbc2array_("AreaTable.dbc", "nxxxxxxxxxxsxxxxxxxxxxxxxxxxxxxxxxxx");
  $mapnames = array();
  foreach ($dbc as $row) $mapnames[$row[0]] = $row[1];

  $dbc = array();

  // Instance maps
  $dbc_tmp = dbc2array_("Map.dbc", "nxixsxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  foreach ($dbc_tmp as $row)
  {
    if ($row[1] > 0)
      $dbc[] = array($row[0], $row[3], $row[2], 0, 0, 0, 0);
  }

  // Regular maps
  $dbc_tmp = dbc2array_("WorldMapArea.dbc", "xiisffffxx");
  foreach ($dbc_tmp as $row)
  {
    if (isset($mapnames[$row[1]]) && !empty($mapnames[$row[1]]))
    {
      $y_min = ($row[3]<$row[4]) ? $row[3] : $row[4];
      $y_max = ($row[3]<$row[4]) ? $row[4] : $row[3];
      $x_min = ($row[5]<$row[6]) ? $row[5] : $row[6];
      $x_max = ($row[5]<$row[6]) ? $row[6] : $row[5];
      $dbc[] = array($row[0], $row[1], $mapnames[$row[1]], $x_min, $y_min, $x_max, $y_max);
    }
  }
  unset($mapnames);
*/

  $dbc = array();

  // Fog added the `type` column for something... So let's get it.
  $maptype = array();
  $areatype = array();
// 3.1.3.new: nxixsxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
// 3.2.2a:    nxixsxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
// 3.3.2:     nxixxsxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
  $dbc_tmp = dbc2array_("Map.dbc", "nxixxsxxxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
  foreach ($dbc_tmp as $row)
  {
    $maptype[$row[0]] = $row[1];
    if ($row[3]) $areatype[$row[0] . "@" . $row[3]] = $row[1];
  }

  $dbc_tmp = dbc2array_("AreaTable.dbc", "nixxxxxxxxxsxxxxxxxxxxxxxxxxxxxxxxxx");
  foreach ($dbc_tmp as $row)
  {
    $type = 0;
    if (isset($maptype[$row[1]]))
      $type = $maptype[$row[1]];
    if (isset($areatype[$row[1]."@".$row[0]]))
      $type = $areatype[$row[1]."@".$row[0]];
    $dbc[$row[0]] = array($row[1], $row[0], $row[2], 0, 0, 0, 0, $type);
  }

  // Update data with coords, where available
// 3.1.3.new: xiisffffxx
// 3.2.2a:    xiisffffxxx
  $dbc_tmp = dbc2array_("WorldMapArea.dbc", "xiisffffxxx");
  foreach ($dbc_tmp as $row)
  {
    if (isset($dbc[$row[1]]))
    {
      $dbc[$row[1]][3] = ($row[5]<$row[6]) ? $row[5] : $row[6]; // x_min
      $dbc[$row[1]][4] = ($row[3]<$row[4]) ? $row[3] : $row[4]; // y_min
      $dbc[$row[1]][5] = ($row[5]<$row[6]) ? $row[6] : $row[5]; // x_max
      $dbc[$row[1]][6] = ($row[3]<$row[4]) ? $row[4] : $row[3]; // y_max
    }
  }

  unset($dbc_tmp);
  print_insert('INSERT INTO `aowow_zones` VALUES', $dbc);

  // TODO: Get duplicates from Map.dbc automatically. Currently they are:
?>
-- Onyxia's Lair
UPDATE aowow_zones SET mapID = 249 WHERE areatableID = 2159;
-- Hall of Legends
UPDATE aowow_zones SET mapID = 450 WHERE areatableID = 2917;
