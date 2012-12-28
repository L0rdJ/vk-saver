<?php
/**
 * @package VK Saver
 * @class   DownloadHelper
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    28 Dec 2012
 **/

class DownloadHelper
{
	const STATUS_CREATED     = 1;
	const STATUS_DOWNLOADING = 2;
	const STATUS_DOWNLOADED  = 3;

	private $listsPath    = null;
	private $downloadPath = null;
	private $sessionID    = null;

	public function __construct( $sessionID = null ) {
		if( $this->listsPath === null ) {
			$this->listsPath = dirname( __FILE__ ) . '/../cache/download_lists/';
		}
		if( $this->downloadPath === null ) {
			$this->downloadPath = dirname( __FILE__ ) . '/../public_html/download/';
		}
		$this->sessionID = $sessionID !== null ? $sessionID : session_id();
	}

	public function setSessionID( $sessionID ) {
		$this->sessionID = $sessionID;
	}

	public function getAllDownloadLists() {
		$lists = scandir( $this->listsPath );
		foreach( $lists as $key => $list ) {
			if(
				$list == '.'
				|| $list == '..'
			) {
				unset( $lists[ $key ] );
			}
		}
		return $lists;
	}

	private function getDownloadListFile() {
		return $this->listsPath . $this->sessionID;
	}

	public function isActiveDownloadList() {
		$list = $this->getDownloadList();
		if( $list === false ) {
			return false;
		}

		return (int) $list['status'] !== self::STATUS_DOWNLOADED;
	}

	public function removeDownloadList() {
		return @unlink( $this->getDownloadListFile() );
	}

	public function createDownloadList( array $audios ) {
		$list = array(
			'status' => self::STATUS_CREATED,
			'audios' => array()
		);
		foreach( $audios as $audio ) {
			$list['audios'][ $audio['aid'] ] = array(
				'artist'        => $audio['artist'],
				'title'         => $audio['title'],
				'url'           => $audio['url'],
				'is_downloaded' => 0,
				'download_name' => self::getAudioDownloadName( $audio )
			);
		}

		$this->storeDownoadListInfo( $list );

		// Download list will be changed by download.php CLI script
		chmod( $this->getDownloadListFile(), 0777 );

		// Remove previously downloaded files
		$files = glob( $this->downloadPath . $this->sessionID . '/*' );
		foreach( $files as $file ) {
			@unlink($file);
		}
		@rmdir( $this->downloadPath . $this->sessionID );
	}

	public static function getAudioDownloadName( array $audio ) {
		$title = htmlspecialchars_decode( $audio['artist'] ) . ' - ' . htmlspecialchars_decode( $audio['title'] );

		$replacement = array(
			'й'=>'i','ц'=>'c','у'=>'u','к'=>'k','е'=>'e','н'=>'n',
			'г'=>'g','ш'=>'sh','щ'=>'sh','з'=>'z','х'=>'x','ъ'=>'\'',
			'ф'=>'f','ы'=>'i','в'=>'v','а'=>'a','п'=>'p','р'=>'r',
			'о'=>'o','л'=>'l','д'=>'d','ж'=>'zh','э'=>'ie','ё'=>'e',
			'я'=>'ya','ч'=>'ch','с'=>'s','м'=>'m','и'=>'i','т'=>'t',
			'ь'=>'\'','б'=>'b','ю'=>'yu',
			'Й'=>'I','Ц'=>'C','У'=>'U','К'=>'K','Е'=>'E','Н'=>'N',
			'Г'=>'G','Ш'=>'SH','Щ'=>'SH','З'=>'Z','Х'=>'X','Ъ'=>'\'',
			'Ф'=>'F','Ы'=>'I','В'=>'V','А'=>'A','П'=>'P','Р'=>'R',
			'О'=>'O','Л'=>'L','Д'=>'D','Ж'=>'ZH','Э'=>'IE','Ё'=>'E',
			'Я'=>'YA','Ч'=>'CH','С'=>'S','М'=>'M','И'=>'I','Т'=>'T',
			'Ь'=>'\'','Б'=>'B','Ю'=>'YU'
		);
		foreach( $replacement as $i => $u ) {
			$title = mb_eregi_replace( $i, $u, $title );
		}

		$title = preg_replace( '/[^\w\d -\(\) -]/', '', $title );
		$title = trim( $title ) . '.mp3';
		return $title;
	}

	public function storeDownoadListInfo( array $list ) {
		$filename = $this->getDownloadListFile();
		$fp = fopen( $filename, 'w' );
		fwrite( $fp, json_encode( $list ) );
		fclose( $fp );
	}

	public function getDownloadList() {
		$filename = $this->getDownloadListFile();
		if( file_exists( $filename ) === false ) {
			return false;
		}
		return json_decode( file_get_contents( $filename ), true );
	}

	public function downloadAudio( array $audio ) {
		$downloadDir = $this->downloadPath . $this->sessionID . '/';
		if( file_exists( $downloadDir ) === false ) {
			mkdir( $downloadDir );
			chmod( $downloadDir, 0777 );
		}

		$file  = $downloadDir . $audio['download_name'];
		if( file_exists( $file ) ) {
			return true;
		}

		$source      = fopen( $audio['url'], 'r' );
		$destination = fopen( $file, 'w' );
		if( @stream_copy_to_stream( $source, $destination ) == 0 ) {
			unlink( $file );
			return false;
		}
		chmod( $file, 0777 );
		return true;
	}

	public function createZIP() {
		$zip  = new ZipArchive();
		$list = $this->getDownloadList();

		$downloadDir = $this->downloadPath . $this->sessionID . '/';
		$zipFile     = $downloadDir . 'all.zip';
		if( file_exists( $zipFile ) ) {
			return true;
		}

		$files = array();
		if( $zip->open( $zipFile, ZIPARCHIVE::CREATE ) !== true ) {
			return false;
		}
		foreach( $list['audios'] as $audio ) {
			$file = $downloadDir . $audio['download_name'];
			if( file_exists( $file ) ) {
				$zip->addFile( $file, $audio['download_name'] );
			}
		}
		$zip->close();
	}
}
