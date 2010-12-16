<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
{include file='head.tpl'}
</head>

<body>
<div id="layers"></div>
<!--[if lte IE 6]><table id="ie6layout"><tr><th class="ie6layout-th"></th><td id="ie6layout-td"><div id="ie6layout-div"></div><![endif]-->
<div id="layout">
	<div id="header">
		<div id="header-logo">
			<a href="."></a>
			<h1>{$page.Title|escape:"html"}</h1>
		</div>
	</div>

		<div id="wrapper" class="nosidebar">
		<div id="toptabs">
			<div id="toptabs-inner">
				<div id="toptabs-right">
					<div id="toplinks" class="toplinks">
						{if $user}<a href="?user={$user.name}">{$user.name}</a>|<a href="?account=signout">{#Sign_out#}</a>{else}<a href="?account=signin">{#Sign_in#}</a>{/if}
						|<a href="javascript:;" id="toptabs-menu-language">{#Language#} <small>&#9660;</small></a>
						<script type="text/javascript">g_initHeaderMenus()</script>
					</div>
				</div>
				<div id="ptewhjkst46"></div>
				<div class="clear"></div>
			</div>
		</div>
		<div id="topbar-right"><div><form action="."><a href="javascript:;"></a><input name="search" size="35" value="" id="oh2345v5ks" /></form></div></div>
		<div id="topbar"><span id="kbl34h6b43" class="menu-buttons"></span><div class="clear"></div></div>
		{strip}<script type="text/javascript">
			g_initHeader({$page.tab});
			LiveSearch.attach(ge('oh2345v5ks'));
			{if $allitems}{include			file='bricks/allitems_table.tpl'		data=$allitems			}{/if}
			{if $allspells}{include			file='bricks/allspells_table.tpl'		data=$allspells			}{/if}
			{if $allachievements}{include	file='bricks/allachievements_table.tpl'	data=$allachievements	}{/if}
			{* TODO: Factions, Quests, NPCs, Objects, g_gatheredzones(?) *}
		</script>{/strip}
