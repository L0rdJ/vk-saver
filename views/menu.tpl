<ul>
	<li class="icn_audio"><a href="/?module=saver">Мои Аудиозаписи</a></li>
	<li class="icn_categories"><a href="/?module=saver&action=search">Поиск</a></li>
	<li class="icn_folder"><a href="/?module=saver&action=download">Загрузки{if isset( $data.progress.download )} ({{$data.progress.download.processed}}/{{$data.progress.download.total}}){/if}</a></li>
	<li class="icn_new_article"><a href="/?module=googleDrive&action=sync">Синхронизации{if isset( $data.progress.sync )} ({{$data.progress.sync.processed}}/{{$data.progress.sync.total}}){/if}</a></li>
</ul>