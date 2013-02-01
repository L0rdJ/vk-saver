{extends file='layout.tpl'}

{block name=content}
<article class="module width_full">
	<div class="module_content">
		{if $data.list.sync_status eq 4}
			{if $data.folder}
			<p>Аудиозаписи успешно синхронизированы с <a href="https://drive.google.com/#folders/{{$data.folder.id}}" target="_blank">папкой Google диска<a></p>
			{/if}
		{else}
			<img src="/images/ajax-loader.gif" style="margin-left: 50%;left: -110px;position: relative;" />
		{/if}
	</div>
</article>

<article class="module width_full">

	<header style="height: 34px;"></header>

	<table class="tablesorter" cellspacing="0" style="margin-top: 0;">
		<tbody>
			{foreach from=$data.list.audios key=aid item=audio}
			{if isset( $audio['is_sync_complete'] )}
			<tr>
				<td>{{$audio.artist|truncate:32:'...'}} - {{$audio.title|truncate:64:'...'}}</td>
				<td>{if $audio['is_sync_complete'] eq 1}Синхронизировано{else}Синхронизация активна...{/if}</td>
			</tr>
			{/if}
			{/foreach}
		</tbody>
	</table>

</article>
{/block}
