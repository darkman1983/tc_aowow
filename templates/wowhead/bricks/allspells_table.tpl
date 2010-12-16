{strip}

var _ = g_spells;
{foreach from=$data key=id item=item}
	_[{$id}]={ldelim}icon:'{$item.icon}',name_{$language}:'{$item.name|escape:"javascript"}'{rdelim};
{/foreach}

{/strip}