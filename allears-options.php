<?php

class AllEarsOptions {
	const PAGE_TITLE = "allEars options";
	// A "slug" is a readable part of the URL (query param or permalink)
	// Forcing it to be the same as the plugin's ID, just for consistency...
	const PAGE_SLUG = "allears";
	const SUBMENU_LABEL = "allEars";

	const DB_ID_WIDGET = "allEars_config_widget";
	const GROUP_LABEL_WIDGET = "allEars_group_widget";
	const SECTION_ID_WIDGET = "allEars_section_widget";

	const FIELD_ID_WIDGET_KEY = "allEars_field_widget_key";
	const WIDGET_DB_WIDGET_KEY = "key";

	const FIELD_DEF_WIDGET_KEY = array(
		"id" => self::FIELD_ID_WIDGET_KEY,
		"label" => "Widget key",
		"render_fn" => array("AllEarsOptions", "render_text"),
		// "validation_fn" is a function that takes in input a value, and returns "null" if
		// validation succeeded, or an error message if it failed.
		"validation_fn" => array("AllEarsOptions", "validate_widget_key"),
		"sanitize_fn" => array("AllEarsOptions", "sanitize_text"),
		"section_id" => self::SECTION_ID_WIDGET,
		// "id" and all tha follows are added to $args of "render_fn" by AllEarsOptions::add_settings_field()
		"db_id" => self::DB_ID_WIDGET,
		"db_key_id" => self::WIDGET_DB_WIDGET_KEY,
	);

	const FIELD_DEF_WIDGET_DEFAULT_ATTS = array(
		"id" => "allEars_field_widget_default_atts",
		"label" => "Widget default attributes",
		"render_fn" => array("AllEarsOptions", "render_widget_default_atts"),
		"validation_fn" => array("AllEarsOptions", "validate_widget_default_atts"),
		"sanitize_fn" => array("AllEarsOptions", "sanitize_widget_default_atts"),
		"section_id" => self::SECTION_ID_WIDGET,
		// "id" and all tha follows are added to $args of "render_fn" by AllEarsOptions::add_settings_field()
		"db_id" => self::DB_ID_WIDGET,
		"db_key_id" => "default_atts",
		"help" => "Set the default attributes to be automatically added to all <em>[allears-widget]</em> shortcodes on all posts.<br />" .
				"This setting applies to shortcodes added explicitly as well as shortcodes added automatically. " .
				"Use the same syntax you use for the shortcode attributes. " .
				"To override a default for a specific shortcode, simply define the attribute again on the shortcode itself."
	);

	const FIELD_DEF_WIDGET_AUTO = array(
		"id" => "allEars_field_widget_auto",
		"label" => "Widget on all posts",
		"render_fn" => array("AllEarsOptions", "render_checkbox"),
		// No validation_fn needed for a checkbox
		"sanitize_fn" => array("AllEarsOptions", "sanitize_checkbox"),
		"section_id" => self::SECTION_ID_WIDGET,
		// "id" and all tha follows are added to $args of "render_fn" by AllEarsOptions::add_settings_field()
		"db_id" => self::DB_ID_WIDGET,
		"db_key_id" => "auto",
		"help" => "If you check this box, the plugin will automatically add a widget to the top of all posts that don't explicitly have the shortcode. ".
				"If this feature is enabled here, you can disable it on individual posts in the post editor."
	);

	const WIDGET_FIELDS = array(
		self::FIELD_DEF_WIDGET_KEY,
		self::FIELD_DEF_WIDGET_DEFAULT_ATTS,
		self::FIELD_DEF_WIDGET_AUTO,
	);

	// Returns a (unsanitized) value, or "" if the $db_id is missing or if $db_key_id is
	// missing from $db_id.
	// We want to check if $db_key_id exists, mostly because we want to "future proof" this
	// logic, so that it can work later if we add new $db_key_id that won't be found in
	// existing installation before upgrading.
	private static function get_db_value($db_id, $db_key_id) {
		// Note that we're not using self::get_option() here, because this function
		// is called once for every key in the option, and self::get_option() creates
		// a new empty array with all the option keys if "$db_id" is not found. That's
		// a bit expensive for the use case in this function.
		//
		// The first argument is the name of the settings stored in the DB.
		// The function returns "false" if there's no settings stored in the DB, which
		// means the user has not stored anything there yet...
		$option = get_option($db_id);

		if($option !== false && isset($option[$db_key_id])) {
			$db_value = $option[$db_key_id];
		} else {
			$db_value = "";
		}
		return $db_value;
	}

	// The return value is NOT sanitized, the caller must take whatever action she wants.
	public static function get_widget_key() {
		return self::get_db_value(self::DB_ID_WIDGET, self::FIELD_DEF_WIDGET_KEY["db_key_id"]);
	}

	public static function get_widget_default_atts() {
		return self::get_db_value(self::DB_ID_WIDGET, self::FIELD_DEF_WIDGET_DEFAULT_ATTS["db_key_id"]);
	}

	public static function is_widget_auto() {
		return self::get_db_value(self::DB_ID_WIDGET, self::FIELD_DEF_WIDGET_AUTO["db_key_id"]);
	}

	public static function section_widget_text() {
		$support_email = ALLEARS_GLOBALS["support_email"];
		$email_subject = "allEars widget key request";
		// For the use of "%0D%0A", see
		// https://stackoverflow.com/questions/22765834/insert-a-line-break-in-mailto-body
		// It looks like esc_url() below does not attempt to double-escape "%0D%0A"
		// (encoded "\r\n"), so it's safe to use it this way.
		// Also tried:
		// - No line terminator: esc_url() doesn't encode the "native" CR LF from the file.
		// - Add "\r\n" to the text below: esc_url() removes them from the output, no encoding.
		//
		// The closing "EOT" MUST BE at the beginning of the line, don't indent it...
		$email_body = <<<EOT
	I'd like to register my domain and receive a free allEars widget key.%0D%0A
%0D%0A
	Your name:%0D%0A
	Domain (e.g. "example.com"):%0D%0A
	allEars account email:%0D%0A
	Primary use of the widget key:%0D%0A
EOT;

		// See https://codex.wordpress.org/Function_Reference/esc_url
		$href = esc_url("mailto:" . $support_email . "?subject=" . $email_subject . "&body=" . $email_body);
?>
		<p>
			Configuration settings for your allEars widget. All the settings on this section are effective only if a "widget key" is specified.
			To request a widget key, please contact <a href="<?=$href?>" target="_top"><?=$support_email?></a>.
		</p>
<?php
		AllEarsUtils::emit_comment(json_encode(get_option(self::DB_ID_WIDGET)));
	}

	public static function sanitize_text($value) {
		return sanitize_text_field(trim($value));
	}

	// Returns an array of key/value pairs from $input.
	// Keys are always turned to lowercase, values are not.
	public static function sanitize_widget_default_atts($input) {
		// See https://codex.wordpress.org/Function_Reference/shortcode_parse_atts
		$parsed_atts = shortcode_parse_atts($input);
		if(empty($parsed_atts)) {
			// shortcode_parse_atts() can return either an empty array or an empty string,
			// but we want sanitize_widget_default_atts() to only return an empty array()
			// in all cases.
			// We actually would like to return "null" if there's an error, but we don't
			// know how shortcode_parse_atts() processes errors...
			return array();
		}
		// Note that in this function we can't emit HTML comments, so we need to suppress
		// that in the next call with "false" as the second argument.
		$parsed_atts = AllEarsWidgetLoader::shortcode_atts_remove_invalid($parsed_atts, false);
		//AllEarsUtils::dbg(json_encode($parsed_atts));
		return $parsed_atts;
	}

	public static function sanitize_checkbox($value) {
		if($value === null) {
			return "";
		}
		return $value;
	}

	// Returns an error message if validation fails, "null" if validation succeeded.
	public static function validate_widget_key($value) {
		// $value should already be sanitized
		if($value === "" || preg_match("/^([a-z0-9]{5})+$/i", $value)) {
			// Allow the empty string, to allow users to unset the widget key.
			return null;
		}
		return "Invalid value for \"" . self::FIELD_DEF_WIDGET_KEY["label"] . "\"";
	}

	// Returns an error message if validation fails, "null" if validation succeeded.
	public static function validate_widget_default_atts($value) {
		// $value is the result of sanitize_widget_default_atts()
		if($value === null) {
			return "Unable to parse parameter list for \"" . self::FIELD_DEF_WIDGET_DEFAULT_ATTS["label"] . "\"";
		}
		return null;
	}

	private static function get_option($db_id, $field_def_array) {
		// Retrieve the existing option set, then merge the changes (if valid)
		$option = get_option($db_id);
		if($option === false) {
			// If get_option() returns "false", then nothing is stored in the database,
			// so create a new array now.
			$option = array();
			foreach($field_def_array as $def) {
				$option[$def["db_key_id"]] = "";
			}
		}
		return $option;
	}

	private static function field_sanitize($value, $field_def) {
		if(!isset($field_def["sanitize_fn"])) {
			return $value;
		}
		// Sanitize functions are stored as arrays where [0] is the
		// class name and [1] is the function name.
//		$fn_arr = $field_def["sanitize_fn"];
//		return $fn_arr[0]::$fn_arr[1]($value);
		return call_user_func($field_def["sanitize_fn"], $value);
	}

	// If $value passes validation, the function stores it in $option,
	// otherwise $option is left untouched. If an error is found, the
	// error is queued with add_settings_error().
	// Note that $option is passed by reference, not by value (the "&" operator).
	private static function field_validate(&$option, $value, $field_def) {
		$db_key_id = $field_def["db_key_id"];

		if(!isset($field_def["validation_fn"])) {
			// No validation function, assume the value is good
			$option[$db_key_id] = $value;
			return;
		}

		// Validation functions are stored as arrays where [0] is the
		// class name and [1] is the function name.
//		$fn_arr = $field_def["validation_fn"];
//		$err = $fn_arr[0]::$fn_arr[1]($value);
		$err = call_user_func($field_def["validation_fn"], $value);
		if($err == null) {
			// Validation succeeded
			$option[$db_key_id] = $value;
		} else {
			add_settings_error(
				// "Slug title of the setting to which this error applies." Not sure what
				// this means, I'm setting it to the first argument of register_setting()
				self::GROUP_LABEL_WIDGET,
				// Error code (arbitrary, used to form the HTML ID of the message)
				"validationError",
//				"Invalid \"key\" value: " . json_encode($input),
				$err,
				// Class to use when visualizing the error ("error" is the default anyway...)
				"error"
			);
			// If there was an error, don't touch the existing value in "$option"
		}
	}

	public static function option_validate($input) {
		// Retrieve the existing option set, then merge the changes (if valid)
		$option = self::get_option(self::DB_ID_WIDGET, self::WIDGET_FIELDS);

		foreach(self::WIDGET_FIELDS as $def) {
			$db_key_id = $def["db_key_id"];
			$new_val = self::field_sanitize($input[$db_key_id], $def);
			// If $new_val passes validation, the function stores it in $option,
			// otherwise $option is left untouched.
			self::field_validate($option, $new_val, $def);
		}

		//settings_errors(self::GROUP_LABEL_WIDGET);
		return $option;
	}

	private static function render_help($args) {
		if(isset($args["help"])) {
			# We don't want to escape the help string, it must allow HTML tags...
			#$escaped_help = esc_attr($args["help"]);
			$help = $args['help'];
			echo "<span class='howto' style='margin-left: 0.2rem;'>$help</span>";
		}
	}

	public static function render_widget_default_atts($args) {
		$html_id = $args["id"];
		$db_id = $args["db_id"];
		$db_key_id = $args["db_key_id"];

		$db_value = self::get_db_value($db_id, $db_key_id);
		$output = "";
		if($db_value != "") {
			foreach($db_value as $key => $val) {
				if($output != "") {
					// Add a separator from the second value on... (a whitespace is sufficient)
					$output .= " ";
				}
				$output .= self::sanitize_text($key) . "=\"" . self::sanitize_text($val) . "\"";
			}
		}

		// "id" must be the same ID used as arg (0) of add_settings_field().
		// "name" must start with the same DB_ID used in register_settings(). If it doesn't, you'll
		// get option_validate() being called with "$input == null", and you'll scratch your head
		// trying to figure out why the heck...
		echo "<input id='{$html_id}' name='{$db_id}[{$db_key_id}]' size='80' type='text' value='{$output}' />";
		self::render_help($args);
	}

	public static function render_text($args) {
		$html_id = $args["id"];
		$db_id = $args["db_id"];
		$db_key_id = $args["db_key_id"];

		$db_value = self::sanitize_text(self::get_db_value($db_id, $db_key_id));

		// "id" must be the same ID used as arg (0) of add_settings_field().
		// "name" must start with the same DB_ID used in register_settings(). If it doesn't, you'll
		// get option_validate() being called with "$input == null", and you'll scratch your head
		// trying to figure out why the heck...
		echo "<input id='{$html_id}' name='{$db_id}[{$db_key_id}]' size='40' type='text' value='{$db_value}' />";
		self::render_help($args);
	}

	public static function render_checkbox($args) {
		$html_id = $args["id"];
		$db_id = $args["db_id"];
		$db_key_id = $args["db_key_id"];

		$db_value = self::sanitize_checkbox(self::get_db_value($db_id, $db_key_id));
		$checked = ($db_value != "") ? "checked" : "";
		// "id" must be the same ID used as arg (0) of add_settings_field().
		// "name" must start with the same DB_ID used in register_settings(). If it doesn't, you'll
		// get option_validate() being called with "$input == null", and you'll scratch your head
		// trying to figure out why the heck...
		echo "<input id='{$html_id}' name='{$db_id}[{$db_key_id}]' type='checkbox' {$checked} />";
		self::render_help($args);
	}

	public static function add_settings_field($field_def) {
		$render_fn_args = array(
			"id" => $field_def["id"],
			"db_id" => $field_def["db_id"],
			"db_key_id" => $field_def["db_key_id"],
			"help" => isset($field_def["help"]) ? $field_def["help"] : ""
		);

		add_settings_field(
			// field ID
			$field_def["id"],
			// field title/label
			$field_def["label"],
			// rendering function
			$field_def["render_fn"],
			// name of page (see do_settings_section())
			self::PAGE_SLUG,
			// "section ID" this field goes into (see first argument of add_settings_section()).
			$field_def["section_id"],
			// "$args" passed to "render_fn" when invoked
			$render_fn_args
		);
	}

	// See http://ottopress.com/2009/wordpress-settings-api-tutorial/ and
	// https://codex.wordpress.org/Creating_Options_Pages
	public static function admin_init() {
		register_setting(
			// group label
			self::GROUP_LABEL_WIDGET,
			// name of the settings stored in DB.
			// Use http://yoursite.com/wp-admin/options.php to see all the options currently stored
			self::DB_ID_WIDGET,
			// validation function
			array("AllEarsOptions", "option_validate")
		);

		add_settings_section(
			// section ID
			self::SECTION_ID_WIDGET,
			// section title. Note that this gets rendered as an <h2>, so be careful about
			// what you use in render_options_page() for the page title...
			"Widget settings",
			// section description rendering function
			// For the "callable syntax" for static functions, see here: https://developer.wordpress.org/reference/functions/add_action/
			array("AllEarsOptions", "section_widget_text"),
			// name of page (see do_settings_section())
			self::PAGE_SLUG
		);

		foreach(self::WIDGET_FIELDS as $def) {
			self::add_settings_field($def);
		}
	}

	public static function render_options_page() {
		/* "<?= $var ?>" is the "short tag" version for "<?php echo $var; ?>" */
	?>
		<div class="wrap">
			<h2><?= self::PAGE_TITLE ?></h2>
			<form method="post" action="options.php"> 
	<?php
		// The first argument is a group label (see also register_settings())
		settings_fields(self::GROUP_LABEL_WIDGET);

		// Render all the sections added with add_settings_section() referencing self::PAGE_SLUG.
		do_settings_sections(self::PAGE_SLUG);

		submit_button();
	?>
			</form>
		</div>
	<?php
	}

	public static function register_options_page() {
		add_options_page(
			// pageTitle
			self::PAGE_TITLE,
			// settings submenuLabel
			self::SUBMENU_LABEL,
			// capability/privilege level required ("manage_options" is required)
			"manage_options",
			// label to use on URL
			self::PAGE_SLUG,
			// function rendering the page
			array("AllEarsOptions", "render_options_page")
		);
	}

	public static function init() {
		// See http://ottopress.com/2009/wordpress-settings-api-tutorial/
		add_action("admin_menu", array("AllEarsOptions", "register_options_page"));
	}
}

AllEarsOptions::init();



// See https://www.smashingmagazine.com/2011/10/create-custom-post-meta-boxes-wordpress/
class AllEarsPostMeta {
	const NONCE = "allEars_nonce";

	const BOX_ID = "allEars-post-box";

	// This is just a marker to be used in conjunction with any "FIELD_DEF_"
	// that uses "render_section"
	const FIELD_DEF_SECTION_END = array();

	const FIELD_DEF_BGAUDIO_SECTION = array(
		// "__section" is a special marker.
		"id" => "__section",
		"label" => "Background audio",
		"render_fn" => array("AllEarsPostMeta", "render_section"),
	);

	const FIELD_DEF_BGAUDIO_URL = array(
		"id" => "allEars-meta-bgaudio-url",
		"label" => "URL",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_url"),
		"meta_key_id" => "allEars_bgaudio_url",
	);

	const FIELD_DEF_BGAUDIO_PLAYER = array(
		"render_fn" => array("AllEarsPostMeta", "render_audio_player"),
	);

	const FIELD_DEF_BGAUDIO_GAIN = array(
		"id" => "allEars-meta-bgaudio-gain",
		"label" => "Gain",
		"help" => "A number between 0 and 1",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_text"),
		"meta_key_id" => "allEars_bgaudio_gain",
	);

	const FIELD_DEF_BGAUDIO_TITLE = array(
		"id" => "allEars-meta-bgaudio-title",
		"label" => "Title",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_text"),
		"meta_key_id" => "allEars_bgaudio_title",
	);

	const FIELD_DEF_BGAUDIO_ATTRIBUTION = array(
		"id" => "allEars-meta-bgaudio-attribution",
		"label" => "Attribution",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_text"),
		"meta_key_id" => "allEars_bgaudio_attribution",
	);

	const FIELD_DEF_LANG = array(
		"id" => "allEars-meta-lang",
		"label" => "Language",
		"help" => "E.g. \"fr\" or \"en-GB\"; use the format of the &lt;html&gt; tag's \"lang\" attribute (BCP47)",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_text"),
		"meta_key_id" => "allEars_lang",
	);

	const FIELD_DEF_VOICE = array(
		"id" => "allEars-meta-voice",
		"label" => "Voice",
		"help" => "Note: the \"alt\" voices are not available for all languages",
		"render_fn" => array("AllEarsPostMeta", "render_voice"),
		"save_fn" => array("AllEarsPostMeta", "save_text"),
		"meta_key_id" => "allEars_voice",
	);

	const FIELD_DEF_AEC = array(
		"id" => "allEars-meta-aec",
		"label" => "AEC URL",
		"render_fn" => array("AllEarsPostMeta", "render_text"),
		"save_fn" => array("AllEarsPostMeta", "save_url"),
		"meta_key_id" => "allEars_aec",
	);

	const FIELD_DEF_WIDGET_SECTION = array(
		"id" => "__section",
		"label" => "Widget",
		"render_fn" => array("AllEarsPostMeta", "render_section"),
	);

	// This field is just printing a warning, no DB value to manage
	const FIELD_DEF_WIDGET_KEY_MISSING = array(
		"render_fn" => array("AllEarsPostMeta", "render_widget_key_missing"),
	);

	const FIELD_DEF_DISABLE_AUTO = array(
		"id" => "allEars-meta-disable-auto",
		"label" => "No auto-widget on this post",
		"render_fn" => array("AllEarsPostMeta", "render_widget_disable_auto"),
		"save_fn" => array("AllEarsPostMeta", "save_widget_disable_auto"),
		"meta_key_id" => "allEars_disable_auto",
	);

	const FIELD_DEF_DEBUG = array(
		"id" => "allEars-meta-debug",
		"label" => "Console logging",
		"render_fn" => array("AllEarsPostMeta", "render_checkbox"),
		"save_fn" => array("AllEarsPostMeta", "save_checkbox"),
		"meta_key_id" => "allEars_debug",
	);

	const METABOX_FIELDS = array(
		self::FIELD_DEF_VOICE,
		self::FIELD_DEF_LANG,
		self::FIELD_DEF_BGAUDIO_SECTION,
			self::FIELD_DEF_BGAUDIO_URL,
			self::FIELD_DEF_BGAUDIO_PLAYER,
			self::FIELD_DEF_BGAUDIO_GAIN,
			self::FIELD_DEF_BGAUDIO_TITLE,
			self::FIELD_DEF_BGAUDIO_ATTRIBUTION,
		self::FIELD_DEF_SECTION_END,
		self::FIELD_DEF_AEC,
		self::FIELD_DEF_WIDGET_SECTION,
			self::FIELD_DEF_WIDGET_KEY_MISSING,
			self::FIELD_DEF_DISABLE_AUTO,
			self::FIELD_DEF_DEBUG,
		self::FIELD_DEF_SECTION_END,
	);

	private static function is_nonce_valid() {
		return (isset($_POST[self::NONCE]) && wp_verify_nonce($_POST[self::NONCE], basename( __FILE__ )));
	}

	// Note that this function returns "" and "false" to identify different conditions:
	// - When returning "", the input is empty (and should be accepted as such)
	// - When returning "false", the input is invalid (and should probably be discarded)
	private static function get_box_url_value($input_id) {
		if(!isset($_POST[$input_id])) {
			return "";
		}
		$url = $_POST[$input_id];
		// See also https://cmljnelson.wordpress.com/2018/08/31/url-validation-in-wordpress/
		if(esc_url_raw($url) !== $url) {
			return false;
		}
		return $url;
	}

	private static function get_box_text_value($input_id) {
		// Get the data in the meta box and sanitize it
		// Note that get_post_meta() uses the empty string to mean "key not found", so it's
		// safe to do the same here, we use the empty string to mean "no value".
		return (isset($_POST[$input_id]) ? sanitize_text_field($_POST[$input_id]) : "" );
	}

	// This function converts the value of a checkbox into either the empty string (meaning "off")
	// or the string "on". We do this because update_post_meta() seems to convert booleans to
	// strings anyway (empty string for "false" and "1" for "true"), so we might as well control
	// the scalar values we want to use... the choice of empty string for "false" is for consistency
	// with the behavior of get_box_text_value()/get_post_meta() when a value is missing.
	private static function get_box_checkbox_value($input_id) {
		if(!isset($_POST[$input_id])) {
			return "";
		}
		// See https://wordpress.stackexchange.com/questions/143449/save-checkbox-value-in-metabox
		// A checkbox that's set can return either the number 1 or "on", but one that's unset
		// returns nothing, and that case is already covered above...
		return "on"; // $_POST[$input_id];
	}

	private static function update_meta($post_id, $meta_key, $new_value) {
		// Get the meta value of the custom field key.
		// If the key does not exist, the function returns the empty string (because the last
		// argument is "true", otherwise it would be an empty array).
		// See also https://developer.wordpress.org/reference/functions/get_post_meta/
		$db_value = get_post_meta($post_id, $meta_key, true);

		if($new_value !== "") {
			// The box input field is set
			if($db_value === "") {
				// The DB value was not set, add it now.
				// Using update_post_meta() instead of add_post_meta(), as per the documentation
				// internally it'll call add_post_meta() if required.
				update_post_meta($post_id, $meta_key, $new_value, true);
//				add_post_meta($post_id, $meta_key, $new_value, true);
			} elseif($new_value !== $db_value) {
				// The box input value is different from the DB, update the DB
				update_post_meta($post_id, $meta_key, $new_value);
			} // Else, $new_value === $db_value, do nothing
		} else {
			// We used to delete only if the $db_value was not already the empty string,
			// as we assumed the empty string meant "no value". Then we found ourself with
			// the database actually storing "", and once that happened, we were stuck,
			// it would not accept any new value, even calling update_post_meta() unconditionally
			// was doing nothing. So it's best to delete even if we think there's nothing there,
			// just in case we end up back in that state. After deleting the empty string stored
			// in the database, everything went back to normal...
//			if($db_value !== "") {
				// If there's no box meta value but a DB value exists, delete it.
				delete_post_meta($post_id, $meta_key);
//			}
		}
	}

	public static function dump_post_meta($post_id) {
		AllEarsUtils::dbg("dump_post_meta(post_id = " . $post_id . "): " . json_encode(get_post_meta($post_id)));
	}

	public static function get_bgaudio_info($post_id) {
		$ret_val = array(
			"url" => esc_attr(get_post_meta($post_id, self::FIELD_DEF_BGAUDIO_URL["meta_key_id"], true)),
			"gain" => esc_attr(get_post_meta($post_id, self::FIELD_DEF_BGAUDIO_GAIN["meta_key_id"], true)),
			"title" => esc_attr(get_post_meta($post_id, self::FIELD_DEF_BGAUDIO_TITLE["meta_key_id"], true)),
			"attribution" => esc_attr(get_post_meta($post_id, self::FIELD_DEF_BGAUDIO_ATTRIBUTION["meta_key_id"], true)),
		);

		// In the loose equality check, "null" and empty string are equivalent to "false"
		if($ret_val["url"] == false) {
			return null;
		}
		return $ret_val;
	}

	public static function get_lang($post_id) {
		return esc_attr(get_post_meta($post_id, self::FIELD_DEF_LANG["meta_key_id"], true));
	}

	public static function get_voice($post_id) {
		return esc_attr(get_post_meta($post_id, self::FIELD_DEF_VOICE["meta_key_id"], true));
	}

	public static function get_aec_url($post_id) {
		return esc_attr(get_post_meta($post_id, self::FIELD_DEF_AEC["meta_key_id"], true));
	}

	private static function get_db_checkbox_value($post_id, $meta_key) {
		// It's stored as a string in the database (see get_box_checkbox_value()).
		// We're converting it to a boolean here...
		if(get_post_meta($post_id, $meta_key, true) === "") {
			return false;
		}
		return true;
	}

	public static function get_debug($post_id) {
		return self::get_db_checkbox_value($post_id, self::FIELD_DEF_DEBUG["meta_key_id"]);
	}

	public static function get_disable_auto($post_id) {
		return self::get_db_checkbox_value($post_id, self::FIELD_DEF_DISABLE_AUTO["meta_key_id"]);
	}

	private static function save_url($post_id, $args) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];

		$url = self::get_box_url_value($html_id);

		if($url !== false) {
			self::update_meta($post_id, $meta_key_id, $url);
		} // Else silently discard the invalid value
	}

	private static function save_text($post_id, $args) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];

		$text = self::get_box_text_value($html_id);

		if($text !== false) {
			self::update_meta($post_id, $meta_key_id, $text);
		} // Else silently discard the invalid value
	}

	private static function save_checkbox($post_id, $args) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];

		$checkbox_value = self::get_box_checkbox_value($html_id);
		self::update_meta($post_id, $meta_key_id, $checkbox_value);
	}

	private static function save_widget_disable_auto($post_id, $args) {
		if(AllEarsOptions::is_widget_auto()) {
			// Since we display this option only when the site-wide "auto" flag is on,
			// we can't try to save the value unless "auto" is on as well.
			self::save_checkbox($post_id, $args);
		}
	}

	// Meta box setup function.
	public static function save($post_id, $post) {
		// First validate the nonce
		if(!self::is_nonce_valid()) {
			return;
		}

		// Get the post type object
		$post_type = get_post_type_object($post->post_type);

		// Check if the current user has permission to edit the post
		if(!current_user_can($post_type->cap->edit_post, $post_id)) {
			return;
		}

		// Get the data in the meta box and sanitize it
		// Note that get_post_meta() uses the empty string to mean "key not found", so it's
		// safe to do the same here, we use the empty string to mean "no value".
		foreach(self::METABOX_FIELDS as $def) {
			if(isset($def["save_fn"])) {
				call_user_func($def["save_fn"], $post_id, $def);
			}
		}
	}

	// Duplicated from AllEarsOptions, we'll need to consolidate later.
	private static function render_help($args) {
		if(isset($args["help"])) {
			# We don't want to escape the help string, it must allow HTML tags...
			#$escaped_help = esc_attr($args["help"]);
			$help = $args['help'];
			echo "<span class='howto' style='margin-left: 0.2rem;'>$help</span>";
		}
	}

	private static function render_section($post, $args, $end=false) {
		$label = $args["label"];
		if(!$end) {
			echo "<strong>$label</strong>\n";
			echo "<div style='padding: 0 0.5rem; border: 1px solid rgba(0,0,0,.125);'>\n";
		} else {
			echo "</div>\n";
		}
	}

	private static function render_text($post, $args) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];
		$label = $args["label"];

		$db_value = esc_attr(get_post_meta($post->ID, $meta_key_id, true));

		echo "<p>\n";
		echo "<label for='$html_id'>$label</label><br />\n";
		echo "<input class='widefat' type='text' name='$html_id' id='$html_id' value='$db_value' size='30' />\n";
		self::render_help($args);
		echo "</p>\n";
	}

	private static function render_checkbox($post, $args) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];
		$label = $args["label"];

		$db_value = self::get_db_checkbox_value($post->ID, $meta_key_id);
		$checked = ($db_value) ? "checked" : "";

		echo "<p>\n";
		echo "<input id='{$html_id}' name='{$html_id}' type='checkbox' {$checked} />\n";
		echo "<label for='{$html_id}'>$label</label>\n";
		self::render_help($args);
		echo "</p>\n";
	}

	private static function render_dropdown($post, $args, $values) {
		$html_id = $args["id"];
		$meta_key_id = $args["meta_key_id"];
		$label = $args["label"];

		$db_value = esc_attr(get_post_meta($post->ID, $meta_key_id, true));

		echo "<p>\n";
		echo "<label for='$html_id'>$label</label><br />\n";
		echo "<select class='widefat' id='$html_id' name='$html_id'>\n";
		foreach($values as $key => $value) {
			if($key == "_none") {
				$key = "";
			}
			// This should work also for the empty key...
			if($key == $db_value) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			echo "<option value='$key'$selected>$value</option>\n";
		}
		echo "</select>\n";
		self::render_help($args);
		echo "</p>\n";
	}

	private static function render_voice($post, $args) {
		self::render_dropdown($post, $args, array(
			// "_none" is a special marker for the empty option.
			"_none" => "[ Use player default ]",
			"m0" => "Male",
			"m1" => "Male (alt)",
			"f0" => "Female",
			"f1" => "Female (alt)",
		));
	}

	private static function render_audio_player($post, $args) {
		$url = esc_attr(get_post_meta($post->ID, self::FIELD_DEF_BGAUDIO_URL["meta_key_id"], true));
		if($url != false) {
			echo "<audio controls src='$url'></audio>\n";
		}
	}

	private static function render_widget_key_missing($post, $args) {
		$widget_key = AllEarsOptions::get_widget_key();
		if($widget_key === "") {
			$submenu_url = menu_page_url(AllEarsOptions::PAGE_SLUG , false);
			echo "<p class='howto'>Warning: the following settings are going to be ignored while no widget key is set. " .
								"Go to <a href='" . $submenu_url . "'>Settings->allEars</a> to set your widget key.</p>";
		}
	}

	private static function render_widget_disable_auto($post, $args) {
		if(AllEarsOptions::is_widget_auto()) {
			self::render_checkbox($post, $args);
		}
	}

	/* Display the post meta box. */
	public static function render_box($post) {
		wp_nonce_field(basename( __FILE__ ), self::NONCE);

		$last_section_def = null;
		foreach(self::METABOX_FIELDS as $def) {
			if($def === self::FIELD_DEF_SECTION_END) {
				if($last_section_def != null) {
					self::render_section($post, $last_section_def, true);
					$last_section_def = null;
				} else {
					// Ignore it if there wasn't anything open, getting here means a bug.
				}
				continue;
			}

			if(isset($def["render_fn"])) {
				if($def["id"] == "__section") {
					// We can "auto close" a previous section even without FIELD_DEF_SECTION_END,
					// as long as we don't want to support nested sections.
					if($last_section_def != null) {
						call_user_func($last_section_def["render_fn"], $post, $last_section_def, true);
					}
					$last_section_def = $def;
				}

				call_user_func($def["render_fn"], $post, $def);
			}
		}
	}

	public static function add_meta_box() {
		add_meta_box(
			// Unique ID
			self::BOX_ID,
			// Title
			"allEars",
			// Callback function
			array("AllEarsPostMeta", "render_box"),
			// Admin page (or post type)
			array("post", "page"),
			// Context
			"side",
			// Priority
			"default"
		);
	}

	public function setup_meta_box() {
		// Add meta box on the 'add_meta_boxes' hook
		add_action("add_meta_boxes", array("AllEarsPostMeta", "add_meta_box"));

		// Save post meta on the 'save_post' hook
		add_action("save_post", array("AllEarsPostMeta", "save"), 10, 2);
	}

	public static function init() {
		/* Fire our meta box setup function on the post editor screen. */
		add_action("load-post.php", array("AllEarsPostMeta", "setup_meta_box"));
		add_action("load-post-new.php", array("AllEarsPostMeta", "setup_meta_box"));
	}
}

AllEarsPostMeta::init();