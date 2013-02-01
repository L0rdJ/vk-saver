{extends file='layout.tpl'}

{block name=content}
	{include file='parts/audio_list.tpl' audios=$data.audios}
{/block}