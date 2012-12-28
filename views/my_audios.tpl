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
	<form action="/?module=saver&action=download" method="post">

		<header style="height: 34px;">
			<div class="submit_link">
				<input type="submit" class="select-all-adudio" value="Select all"> <input type="submit" value="Download selected">
			</div>
		</header>

		<table class="tablesorter" cellspacing="0" style="margin-top: 0;">
			<tbody>
				{foreach from=$data.audios item=audio}
				<tr>
					<td width="1%"><input type="checkbox" name="selected_audios[]" value="{{$audio.aid}}" id="audio-{{$audio.aid}}" class="audio"></td>
					<td><label for="audio-{{$audio.aid}}">{{$audio.artist|truncate:32:'...'}} - {{$audio.title|truncate:64:'...'}}</label></td>
					<td width="1%">{{$audio.duration_f}}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>

		<footer>
			<div class="submit_link">
				<input type="submit" class="select-all-adudio" value="Select all"> <input type="submit" value="Download selected">
			</div>
		</footer>

	</form>
</article>
{/block}