ABOUT
=====
This is a set of scripts to generate all the data necessary for AoWoW
from client files. It allows to generate:
* icons
* regular maps (dungeons maps are not supported yet)
* basic english database
* database localization for other languages

Short instruction
=================
1. Unpack MPQ files.
2. Edit config.php, set path to extracted files there.
3. To generate icons run: php generate_icons.php
4. To generate maps run: php generate_maps1.php
5. To generate english database run: php aowow_sql.php > aowow.sql
   (Note: you should have DBC files extracted from enGB client)
6. To generate localization run: php aowow_sql_loc.php > aowow_loc.sql
   (Note: you should have DBC files extracted from localized client)

Details
=======
1. Unpack MPQ files.

You need to extract at least the following directories:
  DBFilesClient
  Interface\Icons
  Interface\Spellbook
  Interface\WorldMap

Since most of them are stored in several MPQ files extract them starting
from the oldest one to the newest, overwriting older files with newer one.
Don't forget about MPQs in the enGB (ruRU, etc.) subdirectory.

Note: under case-sensitive filesystems (i.e. under Linux) names of all
files and directories must be in lowercase.


2. Edit config.php, set path to extracted files there.

You must set at least $config['mpq'] to point to the directory
where you extracted MPQ files.

You also may comment out option $config['tmp'] if you want to save some
time - that will disable generation of map masks (see below).

NOTE: All pathes must end with "/".


3. To generate icons run: php generate_icons.php

Alternatively you can open this file in your browser.
It will attempt to generate all the icons and save them to
the directories, specified in config.php.


4. To generate maps run: php generate_maps1.php

Same, alternatively you can open this file in your browser.

Map generation will take some time.

NOTE: Generation of map masks (option $config['tmp']) is mainly for
developers. Default masks that come with aowow (aowow/images/tmp)
are usually good enough. And you don't need to generate them again.
So you may comment out that option in config.php and make maps
generation faster.


5. To generate english database run in command line:
      php aowow_sql.php > aowow.sql

That will generate file `aowow.sql` containing dump of the database.
It uses option $config['english_dbc'] from config.php. And that option
should point to the directory with DBC files, extracted from enGB client.

After generation completes check last few lines of generated file.
If the file ends with something like:
  Incorrect format string specified for file DBFilesClient/Spell.dbc
  (recordCount=49876 fieldCount=236 recordSize=944 stringSize=3215678)
it means that generation FAILED. Most probably because extractor is
not compatible with that version of client. Please contact the developer
or use files extracted from compatible client.

NOTE: It may take up to half of GiB or RAM to process some tables.
Adjust your "memory_limit" and "max_execution_time" in PHP options
if needed.


6. To generate localization run in command line:
      php aowow_sql_loc.php > aowow_loc.sql

That will generate file `aowow_loc.sql` containing dump of additional
database localization. It uses option $config['local_dbc'] from
config.php, which must point to the directory with DBC files,
extracted from client with appropriate localization.

After generation completes check last few lines of generated file.
If the file ends with something like:
  Incorrect format string specified for file DBFilesClient/Spell.dbc
  (recordCount=49876 fieldCount=236 recordSize=944 stringSize=3215678)
it means that generation FAILED. Most probably because extractor is
not compatible with that version of client. Please contact the developer
or use files extracted from compatible client.

NOTE: Localized database does not work alone. It's an additional
option. AoWoW requires english database anyway.
