<?php
/**
 * @package   Project Name
 * @author    Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date      01 Dec 2012
 **/

session_start();

require_once( '../include.php' );

$controller = isset( $_GET['module'] ) ? $_GET['module'] : 'index';
$action     = isset( $_GET['action'] ) ? $_GET['action'] : 'index';

$controllerClass = 'Controller' . ucfirst( $controller );
$actionMethod    = 'do' . ucfirst( $action );

try{
	if( class_exists( $controllerClass ) === false ) {
		throw new Exception( 'Could not find controller PHP class (' . $controllerClass . ')' );
	}
	$controllerObject = new $controllerClass();

	$callback = array( $controllerObject, $actionMethod );
	if( is_callable( $callback ) === false ) {
		throw new Exception( 'Could not find action PHP method (' . $actionMethod . ')' );
	}

	call_user_func( $callback );
} catch( Exception $e ) {
	Controller::renderView(
		'error',
		array(
			'message' => $e->getMessage(),
			'path'    => 'System Error'
		)
	);
}
