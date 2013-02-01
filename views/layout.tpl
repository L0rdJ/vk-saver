<!DOCTYPE html>
<html lang="en">
	{include file='head.tpl'}

	<body>

		<header id="header">
			<hgroup>
				<h1 class="site_title"><a href="/">VK Saver</a></h1>
			</hgroup>
		</header> <!-- end of header bar -->

		<section id="secondary_bar">
			<div class="user">
				{if isset( $data.user )}<p>{{$data.user.first_name}} {{$data.user.last_name}} (<a href="/?module=auth&action=logout">Выйти</a>)</p>{/if}
			</div>
			{{if isset( $data.path )}}
			<div class="breadcrumbs_container">
				<article class="breadcrumbs"><a href="/">VK Saver</a> <div class="breadcrumb_divider"></div> <a class="current">{{$data.path}}</a></article>
			</div>
			{/if}
		</section><!-- end of secondary bar -->

		<aside id="sidebar" class="column">
			<form class="quick_search" action="/" method="get">
				<input type="hidden" name="module" value="saver" />
				<input type="hidden" name="action" value="search" />
				<input type="text" value="Поиск" name="q" onfocus="this.value='';">
			</form>
			<hr/>

			{include file='menu.tpl'}

			<footer>
				<hr />
				<p>Разработал: <strong><a href="http://www.linkedin.com/in/serheydolgushev">Долгушев Сергей</a></strong></p>
				<p>Код: <strong><a href="https://github.com/L0rdJ/vk-saver">https://github.com/L0rdJ/vk-saver</a></strong></p>
				<p>Theme by <a href="http://www.medialoot.com">MediaLoot</a></p>
			</footer>
		</aside><!-- end of sidebar -->

		<section id="main" class="column">

			{foreach from=$messages item=message}
			<h4 class="alert_{{$message.type}}">{{$message.text}}</h4>
			{/foreach}

		   	{block name=error_alert}{/block}
		   	{block name=warning_alert}{/block}
		   	{block name=success_alert}{/block}

			{block name=content}{/block}

			<div class="spacer"></div>
		</section>

	</body>
</html>