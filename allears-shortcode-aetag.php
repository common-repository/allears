<?php

class AllEarsTag {
	const SHORTCODE_LABEL = "aetag";

	const CAPTURES_VISIBLE = "visible";
	const CAPTURES_HIDDEN = "hidden";
	const CAPTURES_NONE = null;

	const SHORTCODE_TAGS = array(
		"fga" => array(
			// "captures" tracks the behavior when emitting output:
			// 1. Whether or not the tag captures text, which in turn affects how to deal with
			//    self-closing (or not) shortcodes.
			// 2. Whether or not we should append "style='display:none;'" to the HTML attributes.
			"captures" => self::CAPTURES_HIDDEN,
			// Map valid tags to their valid attributes. Since the same attribute has multiple
			// alternate labels, the "canonical" label is mapped to each alternative, so we can
			// make sure each attribute is only rendered once.
			// For attributes that accept positional placement, use the position number in quotes,
			// starting from position "1". This convention assumes that:
			// 1. In order to use positional placement, the tag must also use positional placement
			//    (and take position "0").
			// 2. Only required attributes use positional placement (can't do it with optional
			//    attributes).
			"atts" => array(
				"t" => "t", "title" => "t",
				"at" => "at", "attribution" => "at",
				"pb" => "pb", "pausebefore" => "pb",
				"pa" => "pa", "pauseafter" => "pa",
				// We're using the long form "href" as canonical because this attribute is
				// required, so the long form makes it easier for users to understand the error
				// message if they omit the attribute.
				"h" => "href", "href" => "href",
				"text" => "text"
			),
			// In the "reqd" ("required") array, store a list of canonical labels.
			// If nothing is required, use an empty array.
			"reqd" => array("href"),
		),
		"p" => array(
			"captures" => self::CAPTURES_HIDDEN,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
				"2" => "text", "text" => "text",
			),
			"reqd" => array("value"),
		),
		"voice" => array(
			"captures" => self::CAPTURES_NONE,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
			),
			"reqd" => array("value"),
		),
		"ignore" => array(
			"captures" => self::CAPTURES_VISIBLE,
			"atts" => array(),
			"reqd" => array(),
		),
		"sub" => array(
			"captures" => self::CAPTURES_VISIBLE,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
				"2" => "text", "text" => "text",
			),
			"reqd" => array("value"),
		),
		"ipa" => array(
			"captures" => self::CAPTURES_VISIBLE,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
				"2" => "text", "text" => "text",
			),
			"reqd" => array("value"),
		),
		"lang" => array(
			"captures" => self::CAPTURES_VISIBLE,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
				"2" => "text", "text" => "text",
			),
			"reqd" => array("value"),
		),
		"as" => array(
			"captures" => self::CAPTURES_VISIBLE,
			"atts" => array(
				// Accepts positional placement of the "value"
				"1" => "value", "value" => "value",
				"2" => "text", "text" => "text",
			),
			"reqd" => array("value"),
		),
	);

	// Note that this function does not sanitize "$text", the caller
	// will need to do the sanitizing, if required. See comment at
	// AllEarsTag::format_comment().
	private static function format_msg($text, $tag="") {
		if($tag != "") {
			$tag = " " . $tag;
		}
		return "[" . self::SHORTCODE_LABEL . $tag . "]: " . $text;
	}

	private static function format_comment($text, $tag="") {
		// We're sanitizing here, not in AllEarsTag::format_msg(), because format_msg()
		// is also called by AllEarsTag::dbg(), which calls AllEarsUtils::dbg(), which
		// in turn also calls AllEarsUtils::sanitize_comment(). In truth, nesting
		// sanitize_comment(sanitize_comment($text)) doesn't do anything bad, but no
		// point in doing it if we can avoid it...
		//
		// Note also that unlike AllEarsUtils::format_comment(), we're not adding "\n" at
		// the end of the comment here...
		return "<!-- " . AllEarsUtils::sanitize_comment(self::format_msg($text, $tag)) . " -->";
	}

	private static function dbg($text, $tag="") {
		AllEarsUtils::dbg(self::format_msg($text, $tag));
	}

	private static function atts_get_tag($input_atts, &$errmsg) {
		$errmsg = null;
		$tag = null;

		// In case both the positional and "tag='<tag>'" forms are present, the "tag='<tag>'"
		// form has priority.
		if(isset($input_atts[0])) {
			$tag = $input_atts[0];
		}

		if(isset($input_atts["tag"])) {
			$tag = $input_atts["tag"];
		}

		if($tag == null) {
			$errmsg = "omitting shortcode without tag";
			return null;
		}

		$tmp = self::SHORTCODE_TAGS;
		if(isset($tmp[$tag])) {
			return $tag;
		}

		$errmsg = "omitting shortcode with unknown tag \"" . $tag . "\"";
		return null;
	}

	private static function to_html_att($att) {
		if($att == "value") {
			return "data-aec-value";
		}
		if($att == "text") {
			return "data-aec-text";
		}
		return "data-aec-attr-" . $att;
	}

	private static function atts_get_validated_atts($input_atts, $tag, &$errmsg) {
		$errmsg = null;

		$valid_atts = self::SHORTCODE_TAGS[$tag]["atts"];
		$ret_val = array();
		foreach($input_atts as $key => $value) {
			if($key == "0" || $key == "tag") {
				// Skip the tag...
				continue;
			}
			if(isset($valid_atts[$key])) {
				# Note that we're not escaping HTML tags here. The client doesn't have
				# a problem if "data-aec-text" includes some tags, and escapes them
				# correctly, so there shouldn't be any risk of HTML injection.
				# If we did escape HTML tags, we'd need to be careful not to escape
				# HTML entities, because in the "data-aec-text" the only way to insert
				# the "]" character is to use the HTML entity "&#93;", otherwise Wordpress
				# gets confused, as it considers any "]" as shortcode closing brackets.
				$ret_val[$valid_atts[$key]] = $value;
			} else {
				self::dbg("ignoring invalid attribute \"" . $key . "\"", $tag);
			}
		}
		self::dbg("validated_atts = " . json_encode($ret_val), $tag);

		// Validated that all required attributes have been included
		$reqd_atts = self::SHORTCODE_TAGS[$tag]["reqd"];
		foreach($reqd_atts as $key) {
			if(!isset($ret_val[$key])) {
				$errmsg = "omitting shortcode, required attribute \"" . $key . "\" missing";
				return null;
			}
		}
		return $ret_val;
	}

	private static function to_text($validated_atts) {
		$ret_val = "";
		foreach($validated_atts as $key => $value) {
			$ret_val .= " " . self::to_html_att($key) . "=\"" . $value . "\"";
		}
		return $ret_val;
	}

	public static function render($tag, $validated_atts=array(), $captured_text="", $captures=self::CAPTURES_NONE) {
		// Both CAPTURES_NONE and CAPTURES_HIDDEN need the extra style, so let's
		// make it the default.
		$extra_style = " style=\"display:none;\"";

		if($captures == self::CAPTURES_NONE) {
			// If the tag doesn't capture text but there's text captured, most likely
			// the user forgot to close the shortcode ([aetag /] or [/aetag]). Let's
			// just put all the text after closing the HTML tag.
			$close = "></span>" . $captured_text;
		} else {
			if($captures == self::CAPTURES_VISIBLE) {
				$extra_style = "";
			}
			$close = ">" . $captured_text . "</span>";
		}

		return "<span data-aec-tag=\"" . $tag . "\"" . self::to_text($validated_atts) . $extra_style . $close;
	}

	// See also https://codex.wordpress.org/Shortcode_API
	public static function tag_shortcode($input_atts, $captured_text="") {

		$tag = self::atts_get_tag($input_atts, $errmsg);
		if($tag == null) {
			return self::format_comment($errmsg) . $captured_text;
		}

		$tag_info = self::SHORTCODE_TAGS[$tag];

		$validated_atts = self::atts_get_validated_atts($input_atts, $tag, $errmsg);
		// Make sure to use the "strict check" below, otherwise the empty array will
		// match the condition too (but the empty array[] represents a valid output
		// for aetags like "ignore").
		if($validated_atts === null) {
			if($tag_info["captures"] == self::CAPTURES_HIDDEN) {
				return self::format_comment($errmsg, $tag);
			}
			return self::format_comment($errmsg, $tag) . $captured_text;
		}

		return self::render($tag, $validated_atts, $captured_text, $tag_info["captures"]);
	}

	public static function init() {
		// See https://wordpress.stackexchange.com/questions/110929/adding-additional-attributes-in-script-tag-for-3rd-party-js
		add_shortcode(self::SHORTCODE_LABEL, array("AllEarsTag", "tag_shortcode"));
	}
}

AllEarsTag::init();