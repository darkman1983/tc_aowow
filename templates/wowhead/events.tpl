{include file='header.tpl'}

	<div id="main">
		<div id="main-precontents"></div>
		<div id="main-contents" class="main-contents">
			<script type="text/javascript">
				g_initPath({$page.path});
			</script>

			<div id="lv-events" class="listview"></div>

			<script type="text/javascript">
				{include file='bricks/event_table.tpl' data=$events.data id='events'}
			</script>

			<div class="clear"></div>
		</div>
	</div>

{include file='footer.tpl'}
