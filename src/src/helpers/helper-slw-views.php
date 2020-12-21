<?php

namespace SLW\SRC\Helpers;

if ( ! defined('WPINC') ) {
	die;
}

/**
 * Return a view
 *
 * @param $viewName
 *
 * @param array $viewData
 *
 * @return false|string
 */
function view( $viewName, $viewData = array() ) {
	// Convert array to variables
	extract( $viewData );

	// view paths
	$view_paths = apply_filters( 'slw_view_paths', array(
		Slw()->pluginDir() . '/views/',
	) );

	ob_start();
	foreach( $view_paths as $path ) {
		$dir_files = list_files( $path );
		foreach( $dir_files as $file ) {
			$file_basename = basename( $file, '.php' );
			if( $file_basename == $viewName ) {
				include( $file );
			}
		}
	}
	return ob_get_clean();
}