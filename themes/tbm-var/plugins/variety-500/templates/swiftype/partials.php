<script type="text/liquid" id="ac_article">
	<div class="ac_title">{{ result | highlight: 'title' | unescape }}</div>
</script>

<script type="text/liquid" id="ac_tag">
	<div class="ac_title">
		<a href="{{ result.url }}">{{ result.name }}</a>
	</div>
</script>
