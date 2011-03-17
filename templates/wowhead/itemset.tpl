{include file='header.tpl'}

		<div id="main">

			<div id="main-precontents"></div>

			<div id="main-contents" class="main-contents">
				<script type="text/javascript">
					{include file='bricks/allcomments.tpl'}
					var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeid}, name: '{$itemset.name|escape:"quotes"}'{rdelim};
					g_initPath({$page.path});
				</script>

				<table class="infobox">
					<tr><th>{#Quick_Facts#}</th></tr>
					<tr><td><div class="infobox-spacer"></div>
						<ul>
							<li><div>{#Level#}: {$itemset.minlevel}{if $itemset.minlevel!=$itemset.maxlevel - {$itemset.maxlevel}{/if}</div></li>{if $user.roles == 2}<li><div><a href="?admin.editarticle=5.{$itemset.entry}">{#Write_article#}</a></div></li>{/if}{if $itemset.Aflags & 2}<li><div>{#Not_Available_to_Players#}</div></li>{/if}{if $itemset.Aflags & 8}<li><div>{#No_Longer_Available_to_Players#}</div></li>{/if} {if $itemset.Aflags & 16}<li><div>{#Added_in_patch_24#}</div></li>{/if} 
						</ul>
					</td></tr>
				</table>

				<div class="text">
					<a href="http://www.wowhead.com/?{$query}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
					<h1>{$itemset.name}</h1>
					{$itemset.article}
					{$itemset.name} - состоит из {$itemset.count} предметов:
					<table class="iconlist">
						{section name=i loop=$itemset.pieces}<tr><th align="right" id="iconlist-icon{$smarty.section.i.index+1}"></th><td><span class="q{$itemset.pieces[i].quality}"><a href="?item={$itemset.pieces[i].entry}">{$itemset.pieces[i].name}</a></span></td></tr>{/section} 
					</table>
					<script type="text/javascript">
						{section name=i loop=$itemset.pieces}ge('iconlist-icon{$smarty.section.i.index+1}').appendChild(g_items.createIcon({$itemset.pieces[i].entry}, 0, 0));{/section}
					</script>
					<h3>Бонус за комплект</h3>

					Ношение большего числа предметов из этого комплекта предоставит бонусы для вашего персонажа.
					<ul>
						{section name=i loop=$itemset.spells}<li><div>{$itemset.spells[i].bonus} частей: <a href="?spell={$itemset.spells[i].entry}">{$itemset.spells[i].tooltip}</a></div></li>{/section}
					</ul>

				<h2>{#Related#}</h2>

			</div>

			<div id="tabs-generic"></div>
			<div id="listview-generic" class="listview"></div>
<script type="text/javascript">
var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
new Listview({ldelim}template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: tabsRelated, parent: 'listview-generic', data: lv_comments{rdelim});
tabsRelated.flush();
</script>

			{include file='bricks/contribute.tpl'}

			</div>
		</div>
	</div>
{include file='footer.tpl'}
