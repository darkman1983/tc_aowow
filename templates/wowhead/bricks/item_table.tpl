{strip}

{assign var="cost" value=true}
{assign var="percent" value=false}
{assign var="classs1" value=true}
{assign var="classs2" value=true}
{assign var="classs4" value=true}
{assign var="group" value=false}

{foreach from=$data item=curr}
	{if !(isset($curr.cost))}{assign var="cost" value=false}{/if}
	{if isset($curr.percent)}{assign var="percent" value=true}{/if}
	{if !($curr.classs==1)}{assign var="classs1" value=false}{/if}
	{if !($curr.classs==2)}{assign var="classs2" value=false}{/if}
	{if !($curr.classs==4)}{assign var="classs4" value=false}{/if}
	{if $curr.group}{assign var="group" value=true}{/if}
{/foreach}

new Listview(
	{ldelim}template:'item',
	id:'{$id}',
	note:'{$num_item} {#Item_Found#}',
	{if (isset($name))}name:LANG.tab_{$name},{/if}
	{if (isset($tabsid))}tabs:{$tabsid},parent:'listview-generic',{/if}
	extraCols:[
		{if $percent}Listview.extraCols.percent{/if}
		{if $group},Listview.funcBox.createSimpleCol('group', 'group', '10%', 'group'){/if}
		{if $cost}Listview.extraCols.stock, Listview.extraCols.cost{/if}
	],
	{if $classs1}visibleCols:['slots'],
	{elseif $classs2}visibleCols:['dps', 'speed'],
	{elseif $classs4}visibleCols:['armor', 'slot'],{/if}
	hiddenCols:['source'],
	sort:[{if $percent}'-percent',{/if}'name'],
	data:[
	{foreach name=i from=$data item=item}
		{ldelim}
		{* Название/качество вещи, обязательно *}
		name:'{$item.quality2}{$item.name|escape:"quotes"}',
		{* Уровень вещи *}
		{if $item.level}
			level:{$item.level},
		{/if}
		{* Требуемый уровень вещи *}
		{if $item.reqlevel}
			reqlevel:{$item.reqlevel},
		{/if}
		{* Класс вещи, обязательно *}
			classs:{$item.classs},
		{* Подкласс вещи, обязательно *}
			subclass:{$item.subclass},
		{* Кол-во вещей при дропе *}
		{if isset($item.maxcount)}
			{if $item.maxcount>1}
				stack:[{$item.mincount},{$item.maxcount}],
			{/if}
		{/if}
		{* Процент дропа *}
		{if $percent}
			percent:{$item.percent},
		{/if}
		{if $item.group and isset($item.groupcount)}
			group:'({$item.group}){if $item.groupcount!=1} x{$item.groupcount}{/if}',
		{/if}
		{* Стоимость *}
		{if $cost}
			{* Макс. кол-во на продажу *}
			stock:-1,
			cost:[
				{if isset($item.cost.money)}{$item.cost.money}{/if}
				{if isset($item.cost.honor) or isset($item.cost.arena) or isset($item.cost.items)}
					,{if isset($item.cost.honor)}{$item.cost.honor}{/if}
					{if isset($item.cost.arena) or isset($item.cost.items)}
						,{if isset($item.cost.arena)}{$item.cost.arena}{/if}
						{if isset($item.cost.items)}
							,[
							{foreach from=$item.cost.items item=curitem name=c}
								[{$curitem.item},{$curitem.count}]
								{if $smarty.foreach.c.last}{else},{/if}
							{/foreach}
							]
						{/if}
					{/if}
				{/if}
				],
		{/if}
		{if $classs1==1}
			nslots:{$item.slots},
		{/if}
		{if $classs2}
			dps:{$item.dps},
			speed:{$item.speed},
		{/if}
		{if $classs4}
			armor:{$item.armor},
			slot:{$item.slot},
		{/if}
		{* Номер вещи, обязателен *}
		id:{$item.entry}
		{rdelim}{if $smarty.foreach.i.last}{else},{/if}
	{/foreach}
	]{rdelim}
);
{/strip}

