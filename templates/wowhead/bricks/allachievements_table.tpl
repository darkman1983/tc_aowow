{strip}

var _ = g_achievements;
{foreach from=$data key=id item=item}
	_[{$id}]={ldelim}icon:'{$item.icon|escape:"javascript"}',name_{$language}:'{$item.name|escape:"javascript"}'{rdelim};
{/foreach}

{/strip}