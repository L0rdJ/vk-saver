{extends file='layout.tpl'}

{block name=content}
<article class="module width_full">
	<header><h3>Please login, to continue</h3></header>
		<div class="module_content">
			<p>In order to use VK Saver you should be logged in</p>
			<a href="/?module=auth&action=requestToken">Login</a>
		</div>
</article><!-- end of styles article -->
{/block}