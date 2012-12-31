{extends file='layout.tpl'}

{block name=content}
<article class="module width_full">
	<div class="module_content">
		{if $data.list.status eq 3}
		<a href="/?module=saver&action=downloadAll">Скачать все в архиве</a>
		{else}
		<img src="/images/ajax-loader.gif" style="margin-left: 50%;left: -110px;position: relative;" />
		{/if}
	</div>
</article>

<article class="module width_full">

	<header style="height: 34px;"></header>

	<table class="tablesorter" cellspacing="0" style="margin-top: 0;">
		<tbody>
			{foreach from=$data.list.audios item=audio}
			<tr>
				<td>{{$audio.artist|truncate:32:'...'}} - {{$audio.title|truncate:64:'...'}}</td>
				<td>{if $audio['is_downloaded'] eq 1}<a href="/download/{{$data.session_id}}/{{$audio['download_name']}}">Загружено<a>{else}{if isset( $audio['skipped'] )}Пропущено{else}Загружаеться...{/if}{if isset( $audio['errors_count'] )} <strong>(неудачных попыток: {{$audio['errors_count']}})</strong>{/if}{/if}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

</article>
{/block}