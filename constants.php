<?php
/**
 * Set additional Noob Task plugin constants.
 *
 * @package noobtask
 * @since 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if(!defined('NOOBTASK_VERSION')){
	//Define default plugin version
	define('NOOBTASK_VERSION', '1.0.0');
}

if(!defined('SUBSITE_TYPE')){
	//Define default subsite type
	define('SUBSITE_TYPE', 'seller');
}

if(!defined('KARTRA_API_KEY')){
	//Define default Kartra key as blank
	define('KARTRA_API_KEY', '');
}

if(!defined('KARTRA_API_PASS')){
	//Define default Kartra pass as blank
	define('KARTRA_API_PASS', '');
}