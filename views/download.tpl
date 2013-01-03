{extends file='layout.tpl'}

{block name=content}
{if $data.list.status eq 3}
<script>
jQuery( function() {
	jQuery( 'input.select-all-adudio' ).bind( 'click', function( e ) {
		e.preventDefault();
		jQuery( 'input.audio' ).attr( 'checked', 'checked' );
	});
} );
</script>
{/if}

<article class="module width_full">
	<div class="module_content">
		{if $data.list.status eq 3}
		<p>
			Вы можете скачать архив со всеми аудиозаписаями по следующей ссылке: <a href="/?module=saver&action=downloadAll">Скачать все в архиве</a>.
		</p>
		<p>
			Так же Вы можете синхронизировать скачанные аудиозаписи с <a href="http://www.google.com/intl/ru/drive/start/index.html">Google Drive</a>. Для этого отметье аудиозаписи и нажмите по кнопке "Синхронизировать отмеченные с Google Drive".
		</p>
		{else}
		<img src="/images/ajax-loader.gif" style="margin-left: 50%;left: -110px;position: relative;" />
		{/if}
	</div>
</article>

{if $data.list.status eq 3}
<form action="/?module=googleDrive&action=sync" method="post">
{/if}

<article class="module width_full">

	<header style="height: 34px;">
		{if $data.list.status eq 3}
		<div class="submit_link">
			<input type="submit" class="select-all-adudio" value="Отметить все"> <input type="submit" value="Синхронизировать отмеченные с Google Drive">
		</div>
		{/if}
	</header>

	<table class="tablesorter" cellspacing="0" style="margin-top: 0;">
		<tbody>
			{foreach from=$data.list.audios key=aid item=audio}
			<tr>
				{if $data.list.status eq 3}
				<td width="1%"><input type="checkbox" name="selected_audios[]" value="{{$aid}}" id="audio-{{$aid}}" class="audio"></td>
				{/if}
				<td><label for="audio-{{$aid}}">{{$audio.artist|truncate:32:'...'}} - {{$audio.title|truncate:64:'...'}}</label></td>
				<td>{if $audio['is_downloaded'] eq 1}<a href="/download/{{$data.session_id}}/{{$audio['download_name']}}">Загружено<a>{else}{if isset( $audio['skipped'] )}Пропущено{else}Загружаеться...{/if}{if isset( $audio['errors_count'] )} <strong>(неудачных попыток: {{$audio['errors_count']}})</strong>{/if}{/if}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	{if $data.list.status eq 3}
	<footer>
		<div class="submit_link">
			<input type="submit" class="select-all-adudio" value="Отметить все"> <input type="submit" value="Синхронизировать отмеченные с Google Drive">
		</div>
	</footer>
	{/if}

</article>

{if $data.list.status eq 3}
</form>
{/if}
{/block}
