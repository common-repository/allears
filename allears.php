<?php
/*
Plugin Name: allEars
Plugin URI: https://wordpress.org/plugins/allears/
Description: Read aloud the contents of your posts. This plugin allows you to add the allEars widget to your posts. The widget enables playback of text using the allEars app. For more information, visit https://getallears.com/about.
Author: allEars
Author URI: https://getallears.com/about
Version: 1.1.1
License: Apache License, Version 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0
*/

define("ALLEARS_PLUGIN_DIR", plugin_dir_path(__FILE__));

require_once(ALLEARS_PLUGIN_DIR . "allears-options.php");
require_once(ALLEARS_PLUGIN_DIR . "allears-html-meta.php");
require_once(ALLEARS_PLUGIN_DIR . "allears-shortcode.php");
require_once(ALLEARS_PLUGIN_DIR . "allears-shortcode-aetag.php");

function allEars_admin_init(){
	AllEarsOptions::admin_init();
}

const ALLEARS_GLOBALS = array(
	// These variables are filled by the build script
	"version" => "1.1.1",
	"debug" => false,
	"env" => "prod",
	"buildid" => "PIA-MRH",
	"support_email" => "support@getallears.com"
);

class AllEarsUtils {
	// Surprisingly WordPress does not have the next function. If you need to emit
	// HTML comments, the only thing you care about is to avoid anything that looks
	// like a comment closing "-->". As far as I understand it, WordPress has a lot
	// of functions to cleanup strings:
	// https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
	// But you either get functions that only touch "<" ("sanitize*"), or functions
	// that replace too much (including double quotes, all the "esc*"). So we need
	// to add this simple function ourselves...
	public static function sanitize_comment($text) {
		return str_replace(">", "&gt;", $text);
	}

	public static function dbg($text) {
		if(ALLEARS_GLOBALS["debug"]) {
			echo "<!-- allEars-debug - " . self::sanitize_comment($text) . " -->\n";
		}
	}

	public static function format_comment($text) {
		return "<!-- allEars: " . self::sanitize_comment($text) . " -->\n";
	}

	public static function emit_comment($text) {
		echo self::format_comment($text);
	}
}


add_action("admin_init", "allEars_admin_init");
