<script type="text/liquid" id="ac_article">
	<div class="ac_title ac_article">{{ result | highlight: 'title' | unescape }}</div>
	<div class="ac_sub">{{ result.published_at | date: "%h %d, %Y" }}</div>
</script>

<script type="text/liquid" id="ac_tag">
	<div class="ac_title ac_tag">
		<a href="{{ result.url }}">{{ result.name }}</a>
	</div>
</script>
