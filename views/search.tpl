{extends file='layout.tpl'}

{block name=content}
<script>
jQuery( function() {
	jQuery( 'input.select-all-adudio' ).bind( 'click', function( e ) {
		e.preventDefault();
		jQuery( 'input.audio' ).attr( 'checked', 'checked' );
	});
} );
</script>

<article class="module width_full">
	<form class="quick_search" action="/" method="get" style="padding: 0px;">
		<input type="hidden" name="module" value="saver" />
		<input type="hidden" name="action" value="search" />

		<div class="module_content">
			<input type="text" name="q" value="{{$data.q}}" style="width:100%;">
		</div>
		<footer>
			<div class="submit_link">
				Искать
				<select name="type">
					<option value="audio">Аудиозаписи</option>
				</select>
				Сортировать по
				<select name="sort">
					<option value="2">популярности</option>
					<option value="0"{if $data.sort eq 0} selected="selected"{/if}>дате добавления</option>
				</select>
				<input type="submit" value="Искать">
			</div>
		</footer>
	</form>
</article>

{if $data.audios|count > 0}
	{include file='parts/audio_list.tpl' audios=$data.audios}
{/if}
{/block}