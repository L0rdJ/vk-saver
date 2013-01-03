<?php
/**
 * @package VK Saver
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    20 Dec 2012
 **/

require_once( 'controllers/controller.php' );
require_once( 'controllers/index.php' );
require_once( 'controllers/saver.php' );
require_once( 'controllers/auth.php' );
require_once( 'controllers/google_drive.php' );

require_once( 'helpers/download.php' );

require_once( 'lib/smarty/Smarty.class.php' );
require_once( 'lib/vk/vkapi.php' );
require_once( 'lib/google_api/Google_Client.php' );
require_once( 'lib/google_api/contrib/Google_DriveService.php' );
