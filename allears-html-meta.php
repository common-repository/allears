<?php

class AllEarsHtmlMeta {
	// AllEarsHtmlMeta::can_render() and AllEarsWidgetLoader::can_render() are not identical,
	// though they have some common code and should probably share...
	private static function can_render($src_trail) {
		// See https://developer.wordpress.org/reference/functions/is_singular/
		if(!is_singular()) {
			// We render the widget only when displaying a single post/page. If this is
			// a multi-post view, then we suppress rendering the widget
			AllEarsUtils::emit_comment($src_trail . ": rendering suppressed for non-single view");
			return false;
		}

		return true;
	}

	private static function renderOne($name, $content) {
		return "<meta name='" . $name . "' content='" . esc_attr($content) . "'>\n";
	}

	public static function render() {
		// Note that can_render() dumps an HTML comment in the output if it returns "false".
		if(!self::can_render("AllEarsHtmlMeta::render")) {
			return;
		}

		$curr_post_id = get_the_ID();
		if($curr_post_id === false) {
			return;
		}

//		AllEarsUtils::emit_comment("AllEarsHtmlMeta::render: starting processing for post ID = " . $curr_post_id);

		$bgaudio = AllEarsPostMeta::get_bgaudio_info($curr_post_id);
		$lang = AllEarsPostMeta::get_lang($curr_post_id);
		$voice = AllEarsPostMeta::get_voice($curr_post_id);
		$aec = AllEarsPostMeta::get_aec_url($curr_post_id);

		$output = "";

		if($bgaudio != null) {
			$output .= self::renderOne("ae:bgAudioUrl", $bgaudio["url"]);
			if(isset($bgaudio["gain"])) {
				$output .= self::renderOne("ae:bgAudioGain", $bgaudio["gain"]);
			}
			// These strings are already escaped, but better safe than sorry...
			if(isset($bgaudio["title"])) {
				$output .= AllEarsUtils::format_comment("bgAudioTitle: " .$bgaudio["title"]);
			}
			if(isset($bgaudio["attribution"])) {
				$output .= AllEarsUtils::format_comment("bgAudioAttribution: " .$bgaudio["attribution"]);
			}
		}

		if($lang !== "") {
			$output .= self::renderOne("ae:language", $lang);
		}

		if($voice !== "") {
			$output .= self::renderOne("ae:voice", $voice);
		}

		if($aec !== "") {
			$output .= self::renderOne("ae:fullAecUrl", $aec);
		}

		$curr_title = get_the_title();
		if($curr_title !== "") {
			$output .= self::renderOne("ae:title", $curr_title);
		}

		// "c" is the date format string for ISO-8601 dates (added in PHP5?)
		// http://php.net/manual/en/function.date.php
		$date_published = get_the_date("c");
		if($date_published !== "") {
			$output .= self::renderOne("ae:datePublished", $date_published);
		}

		$date_modified = get_the_modified_date("c");
		if($date_modified !== "") {
			$output .= self::renderOne("ae:dateModified", $date_modified);
		}

		// See https://developer.wordpress.org/reference/functions/get_feed_link/
		// There's also https://developer.wordpress.org/reference/functions/get_default_feed/
		$curr_feed = get_feed_link();
		if($curr_feed !== "") {
			// Attribute "data-aec-title" is optional, and it's not used here
			$output .= self::renderOne("ae:feed", $curr_feed);
		}

		$output .= self::renderOne("ae:plugin", "allEars for WordPress v." . ALLEARS_GLOBALS["version"]);
		echo $output;
	}

	public static function init() {
		// https://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
		add_action("wp_head", array("AllEarsHtmlMeta", "render"));
	}
}

AllEarsHtmlMeta::init();