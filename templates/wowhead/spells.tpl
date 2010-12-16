{include file='header.tpl'}

	<div id="main">
		<div id="main-precontents"></div>
		<div id="main-contents" class="main-contents">
			<script type="text/javascript">
				g_initPath({$page.path});
			</script>

			<div id="lv-spells" class="listview"></div>

			<script type="text/javascript">
				{include file='bricks/spell_table.tpl' data=$spells id='spells' sort=$page.sort}
			</script>

			<div class="clear"></div>
		</div>
	</div>

{include file='footer.tpl'}
