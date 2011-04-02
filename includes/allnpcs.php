<?php
require_once ( 'includes/alllocales.php' );

// Для списка creatureinfo()
$npc_cols [0] = array ( 'name', 'subname', 'minlevel', 'maxlevel', 'type', 'rank', 'A', 'H' );
$npc_cols [1] = array ( 'subname', 'minlevel', 'maxlevel', 'type', 'rank', /*'minhealth', 'maxhealth', 'minmana', 'maxmana',*/ 'mingold', 'maxgold', 'lootid', 'skinloot', 'pickpocketloot', 'spell1', 'spell2', 'spell3', 'spell4', 'A', 'H', 'mindmg', 'maxdmg', 'attackpower', 'dmg_multiplier', /*'armor',*/ 'difficulty_entry_1' );

// Функция информации о создании
function creatureinfo2 ( $Row )
{
    $name_sub_locale = localizedName ( $Row, 'subname' );
    $creature = array ( 'entry' => $Row ['entry'], 'name' => str_replace ( ' (1)', LOCALE_HEROIC, localizedName ( $Row ) ), 'subname' => $name_sub_locale, 'minlevel' => $Row ['minlevel'], 'maxlevel' => $Row ['maxlevel'], 'react' => $Row ['A'] . ',' . $Row ['H'], 'type' => $Row ['type'], 'classification' => $Row ['rank'] );
    
    $x = '';
    $x = '';
    $x .= '';
    $x .= "<table><tr><td><b class=\"q\">" . htmlspecialchars ( str_replace ( ' (1)', LOCALE_HEROIC, localizedName ( $Row ) ) ) . "</b></td></tr></table><table><tr><td>";
    if ( ! empty ( $name_sub_locale ) ) $x .= $name_sub_locale . "<br>";
    $level = ( $Row ['rank'] == 3 ) ? '??' : ( ( $Row ['minlevel'] == $Row ['maxlevel'] ) ? $Row ['minlevel'] : "{$Row ['minlevel']} - {$Row ['maxlevel']}" );
    switch ( $Row ['rank'] )
    {
        case 1:
            $rank = ' (Elite)';
            break;
        case 2:
            $rank = ' (Rar Elite)';
            break;
        case 3:
            $rank = ' (Boss)';
            break;
        case 4:
            $rank = ' (Rar)';
            break;
        default:
            $rank = '';
            break;
    }
    
    switch ( $Row ['type'] )
    {
        case 1:
            $type = 'Wildtier';
            break;
        case 2:
            $type = 'Drachkin';
            break;
        case 3:
            $type = 'Dämon';
            break;
        case 4:
            $type = 'Elementar';
            break;
        case 5:
            $type = 'Riese';
            break;
        case 6:
            $type = 'Untoter';
            break;
        case 7:
            $type = 'Humanoid';
            break;
        case 8:
            $type = 'Tier';
            break;
        case 9:
            $type = 'Mechanisch';
            break;
        case 10:
            $type = 'Nicht kategorisiert';
            break;
        default:
            $type = '';
            break;
    }
    
    $x .= "Level {$level} {$type}{$rank}";
    $x .= "</td></tr></table>";
    
    $creature ['tooltip'] = $x;
    
    return $creature;
}

// Функция информации о создании
function creatureinfo ( $id )
{
    global $DB;
    global $npc_cols;
    $row = $DB->selectRow ( '
			SELECT ?#, c.entry
			{
				, l.name_loc' . $_SESSION ['locale'] . ' as `name_loc`
				, l.subname_loc' . $_SESSION ['locale'] . ' as `subname_loc`
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
		', $npc_cols [0], ( $_SESSION ['locale'] > 0 ) ? 1 : DBSIMPLE_SKIP, ( $_SESSION ['locale'] > 0 ) ? 1 : DBSIMPLE_SKIP, $id );
    return creatureinfo2 ( $row );
}

?>