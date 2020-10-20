<?php

namespace SLW\SRC\Helpers;

/**
 * Return a view
 *
 * @param $viewName
 *
 * @param array $viewData
 *
 * @return false|string
 */
function view($viewName, $viewData = array()) {
	// Convert array to variables
	extract($viewData);

	ob_start();
	include(Slw()->pluginDir() . '/views/' . $viewName . '.php');
	return ob_get_clean();
}