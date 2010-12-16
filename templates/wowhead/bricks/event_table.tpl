{strip}

{assign var="category" value=false}
{foreach from=$data item=curr}
	{if $curr.category}{assign var="category" value=true}{/if}
{/foreach}

	new Listview({ldelim}
		template:'holiday',
		id:'{$id}',
		{if isset($name)}name:LANG.tab_{$name},{/if}
		{if isset($tabsid)}tabs:{$tabsid}, parent:'listview-generic',{/if}
		{if $category}visibleCols:['category'],{/if}
		data:[
			{foreach name=event from=$data item=curr}
				{ldelim}
					id:{$curr.id},
					name:'{$curr.name|escape:"javascript"}',
					{if $curr.category}
						category:{$curr.category},
					{/if}
					{if $curr.today}
						today:{$curr.today},
					{/if}
					startDate:'{$curr.start}',
					endDate:'{$curr.end}'
				{rdelim}
				{if $smarty.foreach.event.last}{else},{/if}
			{/foreach}
		]
	{rdelim});
{/strip}
