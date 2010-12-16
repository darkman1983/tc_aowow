<pre>
<?php
/*
    generate_maps2.php - code for extracting dungeon maps for AoWoW
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

  if (!isset($config["mpq"]))
    die("Path to extracted MPQ files is not configured");
  if (!isset($config["maps"]))
    die("Path where to extract maps is not configured");

  $mpqdir = $config["mpq"];
  $outmapdir = $config["maps"];

  $dbcdir = $mpqdir . "DBFilesClient/";
  $normaldir = $outmapdir . "normal/";
  $zoomdir   = $outmapdir . "zoom/";

  $mapaspect = 1002.0/668.0;

  @mkdir($outmapdir);
  @mkdir($normaldir);
  @mkdir($zoomdir);

  require("dbc2array.php");
  require("imagecreatefromblp.php");

  function status($message)
  {
    echo $message;
    @ob_flush();
    flush();
    @ob_end_flush();
  }

  die("Not implemented yet. Sorry.\n");
?>
</pre>
