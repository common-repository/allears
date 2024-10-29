<?php

class AllEarsWidgetLoader {
	const SCRIPT_URL = "https://getallears.com/AeWidgetLoader.min.js";
	const SCRIPT_HANDLE = "allears-widget-script";

	const CONTAINER_ID = "allEarsWidgetContainer";

	const SHORTCODE_LABEL = "allears-widget";

	// shortcode_atts() changes the keys to lowercase, so don't use camelCase or any uppercase
	// in the keys you define here.
	const SHORTCODE_ATTS_DEFAULTS = array(
		"width" => "",
		"maxwidth" => "",
		"height" => "",
		"widgetstyle" => "",
		"class" => "",
		"style" => "",
	);

	const SHORTCODE_TO_SCRIPT_ATTS_MAP = array(
		"width" => "data-width",
		"maxwidth" => "data-maxwidth",
		"height" => "data-height",
		"widgetstyle" => "data-style",
	);

	// This function dumps an HTML comment if $src_trail is not "null".
	private static function can_render($src_trail) {
		// See https://developer.wordpress.org/reference/functions/is_singular/
		if(!is_singular()) {
			// We render the widget only when displaying a single post/page. If this is
			// a multi-post view, then we suppress rendering the widget
			if($src_trail != null) {
				AllEarsUtils::emit_comment($src_trail . ": rendering suppressed for non-single view");
			}
			return false;
		}

		$widget_key = AllEarsOptions::get_widget_key();

		if($widget_key === "") {
			if($src_trail != null) {
				AllEarsUtils::emit_comment($src_trail . ": can't render without a widget key");
			}
			return false;
		}

		return true;
	}

	// This function is currently not being used here, since in shortcode_atts_merge() we
	// now use shortcode_atts() instead of wp_parse_args(). On the other hand, we still
	// need this function in AllEarsOptions::sanitize_widget_default_atts(), because there
	// we don't want a merge to happen, we only want to sanitize and store the attributes
	// that are actually listed.
	public static function shortcode_atts_remove_invalid($input_atts, $emit_invalid = true) {
		$valid_keys = array_keys(self::SHORTCODE_ATTS_DEFAULTS);
		$ret_val = array();
		$invalid_key_list = array();

		foreach($input_atts as $key => $value) {
			$lower_key = strtolower($key);
			if(in_array($lower_key, $valid_keys)) {
				$ret_val[$lower_key] = $value;
			} else {
				// See http://php.net/manual/en/function.array-push.php for the syntax below
				$invalid_key_list[] = $lower_key;
			}
		}
		if(count($invalid_key_list) > 0 && $emit_invalid) {
			AllEarsUtils::emit_comment("shortcode_atts_remove_invalid(): invalid attributes discarded: " . json_encode($invalid_key_list));
		}
		return $ret_val;
	}

	public static function shortcode_atts_merge($input_atts) {
		$default_atts = self::SHORTCODE_ATTS_DEFAULTS;
		$config_atts = AllEarsOptions::get_widget_default_atts();

		// We're using shortcode_atts() instead of wp_parse_args() because we want
		// the filtering logic of shortcode_atts() to apply here too.
		// See https://codex.wordpress.org/Function_Reference/wp_parse_args
		$merged_default_atts = shortcode_atts($default_atts, $config_atts);
		AllEarsUtils::dbg("shortcode_atts_merge: default + config = " . json_encode($merged_default_atts));

		if($input_atts === null) {
			// Nothing else to do...
			return $merged_default_atts;
		}

		// See https://developer.wordpress.org/plugins/shortcodes/shortcodes-with-parameters/
		// Note that the arguments in shortcode_atts() are reversed compared to wp_parse_args()...
		$merged_atts = shortcode_atts($merged_default_atts, $input_atts);
		AllEarsUtils::dbg("shortcode_atts_merge: input = " . json_encode($input_atts));
		AllEarsUtils::dbg("shortcode_atts_merge: default + config + input = " . json_encode($merged_atts));

		return $merged_atts;
	}

	public static function shortcode_atts_get() {
		// See https://codex.wordpress.org/Function_Reference/get_shortcode_regex
		global $post;
		// Note that unlike the page above, https://developer.wordpress.org/reference/functions/get_shortcode_regex/
		// says get_shortcode_regex() accepts one parameter...
		$pattern = get_shortcode_regex(array(self::SHORTCODE_LABEL));

		if(!preg_match_all('/'. $pattern . '/s', $post->post_content, $matches)) {
			AllEarsUtils::dbg("shortcode_atts_get(): no match found");
			// If no match is found, just return the auto+default.
			return self::shortcode_atts_merge(null);
		}

		if(!(is_array($matches) && isset($matches[2]))) {
			AllEarsUtils::dbg("shortcode_atts_get(): no match found (2)");
			// If no match is found, just return the auto+default.
			return self::shortcode_atts_merge(null);
		}

		$idx = array_search(self::SHORTCODE_LABEL, $matches[2]);
		if($idx === false) {
			AllEarsUtils::dbg("shortcode_atts_get(): no match found (3)");
			// If no match is found, just return the auto+default.
			return self::shortcode_atts_merge(null);
		}

		$input_atts_string = $matches[3][$idx];
		$input_atts = shortcode_parse_atts($input_atts_string);
		AllEarsUtils::dbg("shortcode_atts_get(): \"" . $input_atts_string . "\" -> " . json_encode($input_atts));

		return self::shortcode_atts_merge($input_atts);
	}

	public static function script_loader_get_script_atts($shortcode_atts) {
		$map = self::SHORTCODE_TO_SCRIPT_ATTS_MAP;
		$ret_val = "";
		foreach($shortcode_atts as $key => $value) {
			if(isset($map[$key]) && $value !== "") {
				$ret_val .= " " . $map[$key] . "=\"" . $value . "\"";
			}
		}
		return $ret_val;
	}

	// See https://wordpress.stackexchange.com/questions/110929/adding-additional-attributes-in-script-tag-for-3rd-party-js
	public static function script_loader($tag, $handle, $src) {
		if($handle === self::SCRIPT_HANDLE) {
			// Most of the validation was already done by script_enqueuer(), so we don't need
			// anything extra here... we can assume there is a $widget_key, and we can assume
			// there is a "allears-widget" shortcode.
			$widget_key = AllEarsOptions::get_widget_key();

			// Make sure to have a whitespace at the beginning of each "$data-*" variable, so it's
			// easier to concatenate them into something legal.
			$data_key = " data-key='" . esc_attr($widget_key) . "'";

			$shortcode_atts = self::shortcode_atts_get();
			AllEarsUtils::dbg("script_loader(): " . json_encode($shortcode_atts));
			$script_atts = self::script_loader_get_script_atts($shortcode_atts);

			$curr_post_id = get_the_ID();
			$data_url = "";
			$data_debug = "";
			if($curr_post_id !== false) {
				$aec = AllEarsPostMeta::get_aec_url($curr_post_id);
				if($aec != "") {
					$data_url = " data-url='" . esc_attr($aec) . "'";
				}
				$debug = AllEarsPostMeta::get_debug($curr_post_id);
				$log_msg = "debug = " . json_encode($debug) . ", orig = " .
							json_encode(get_post_meta($curr_post_id, AllEarsPostMeta::FIELD_DEF_DEBUG["meta_key_id"], true));
				AllEarsUtils::emit_comment($log_msg);
				if($debug === true) {
					$data_debug = " data-debug";
				}
			}

			$data_container_id = " data-container-id='" . self::CONTAINER_ID . "'";

			$tag = "<script" . $data_container_id . $data_key . $data_url . $data_debug . $script_atts .
					" type='text/javascript' src='" . $src . "'></script>";
			AllEarsPostMeta::dump_post_meta($curr_post_id);
		}
		return $tag;
	}

	public static function script_enqueuer() {
		// Note that can_render() dumps an HTML comment in the output if it returns "false".
		if(!self::can_render("script_enqueuer")) {
			return;
		}

		global $post;
		if(!(AllEarsOptions::is_widget_auto() || has_shortcode($post->post_content, self::SHORTCODE_LABEL))) {
			// Do not enqueue the allEars script if the post does not have the shortcode
			// in the content (or the site is configured to add the widget on all posts),
			// since that means the post should not have the widget.
			// Note that has_shortcode() is expensive, and it's ok to use it only as long
			// as we continue to check for is_singular() above. It would be too much to
			// use in a loop of many posts.
			// See also https://wordpress.stackexchange.com/questions/165754/enqueue-scripts-styles-when-shortcode-is-present
			return;
		}

		wp_enqueue_script(self::SCRIPT_HANDLE, self::SCRIPT_URL);
	}

	// Pass any number of string arguments to get concatenated in output
	private static function wrap_content() {
		// Get the argument list as an array
		$args_array = func_get_args();

		$start_aetag = AllEarsTag::render("soa");
		$end_aetag = AllEarsTag::render("eoa");

		return $start_aetag . implode($args_array) . $end_aetag;
	}

	public static function prepend_widget_to_content($content) {
		if(!(self::can_render(null) && AllEarsOptions::is_widget_auto())) {
			return $content;
		}

		$curr_post_id = get_the_ID();

		if(AllEarsPostMeta::get_disable_auto($curr_post_id)) {
			// "auto" is on, but this post has been explicitly excluded.
			return self::wrap_content("<!-- allEars: auto-widget explicitly disabled for this post -->", $content);
		}

		// Note that "$content" is the HTML output, so we need to use "$post->post_content"
		// instead to find the source code with the shortcode.
		global $post;
		if(has_shortcode($post->post_content, self::SHORTCODE_LABEL)) {
			// The post has an explicit shortcode, skip this, we don't want to have two widgets.
			return self::wrap_content($content);
		}
		return self::wrap_content(self::widget_shortcode(AllEarsOptions::get_widget_default_atts()), $content);
	}

	// See also https://codex.wordpress.org/Shortcode_API
	public static function widget_shortcode($input_atts) {
		// Note that can_render() dumps an HTML comment in the output if it returns "false".
		if(!self::can_render("widget_shortcode")) {
			return;
		}

		$merged_atts = self::shortcode_atts_merge($input_atts);

		// Some of the shortcode attributes are managed by script_loader() (they are used to
		// populate the <script> tag). Some others are used by this function, to populate the
		// widget container.
		$style = $merged_atts["style"];
		if($style !== "") {
			$style = " style='" . $style . "'";
		}

		$class = sanitize_html_class($merged_atts["class"]);
		if($class !== "") {
			$class = " class='" . $class . "'";
		}

		$output = "<div id='" . self::CONTAINER_ID . "'" . $class . $style . "></div>";
		return $output;
	}

	public static function init() {
		// See https://wordpress.stackexchange.com/questions/110929/adding-additional-attributes-in-script-tag-for-3rd-party-js
		add_filter("script_loader_tag", array("AllEarsWidgetLoader", "script_loader"), 10, 3); // Where $priority is 10, $accepted_args is 3.
		add_action("wp_enqueue_scripts", array("AllEarsWidgetLoader", "script_enqueuer"));

		add_shortcode(self::SHORTCODE_LABEL, array("AllEarsWidgetLoader", "widget_shortcode"));

		add_filter("the_content", array("AllEarsWidgetLoader", "prepend_widget_to_content"));
	}
}

AllEarsWidgetLoader::init();