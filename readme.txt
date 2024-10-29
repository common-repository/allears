=== allEars ===
Contributors: marcodb
Tags: text-to-speech, tts, speech, audio, RSS feed, podcast,
Stable tag: 1.1.1
Tested up to: 4.9.8
Requires at least: 4.0
License: Apache License, Version 2.0

Bring your blog to life by adding an audio channel for your readers.


== Description ==

The allEars player lets your audience listen to your blog posts, and streams all your content using a playlist fed via RSS.
Use this plugin to embed the allEars player into your posts, or use it to add more depth to your allEars audio channel. This plugin allows you to tune your content to sound more like a podcast: add sound effects, a background soundtrack, or use multiple voices. Keep on reading to find out all you can do to enhance the audio version of your blog with this plugin.
For more information about allEars, visit [https://getallears.com/about](https://getallears.com/about).


= Using the plugin =

When you enable this plugin, you'll see an "allEars" meta box in your post editor. The meta box helps you add some useful properties to your post:

* **Voice**: choose if the main voice reading the post should be a male or a female voice. You can alternate multiple voices in a post by inserting the *[aetag voice]* shortcode wherever you want the voice to change.

* **Language**: tell the allEars player the main language of the post. You can have the post read with different accents by using language codes like "en-US", "en-GB" or "en-AU". If your post includes foreign words or sentences, you can tag them using the *[aetag lang]* shortcode, and the allEars player will do its best to pronounce them as they should.

* **Background audio**: specify the URL of a soundtrack to play while your content is being read, and tune its volume. Your background music can do wonders when you add pauses between sentences, using the *[aetag p]* shortcode in your text.

In addition, you can use the following shortcodes in your text:

* **[aetag fga]**: (for "foreground audio") to insert audio recordings or sound effects.
* **[aetag sub]**: (for "substitute") to instruct the allEars player to read something differently from what's written on the page (like "Read Colorado when I write CO").
* **[aetag ignore]**: useful if there's a section you'd rather want to stay on the written page, but not be read aloud.

If you choose to have the allEars widget on your posts, it can be added automatically to the top of each post, or you can control its exact location on the page using the shortcode *[allears-widget]*.


== Installation ==

Install the allEars plugin on your blog, then activate it, and you're good to go! Most features don't require a key, but you'll need one in order to use the embedded allEars player on your blog posts. The details to request a widget key can be found in the allEars configuration page, under Settings->allEars of your WordPress installation.


== Frequently Asked Questions ==

= Can I control the layout of the allEars widget? =
The allEars widget shortcode takes a number of options. These options can be specified with the shortcode, or added as default for the whole site under Settings->allEars. The available options are:

* **width**: the width of the widget container, expressed in any acceptable CSS form. The minimum width should never be below 80px.
* **maxwidth**: the max-width of the widget container, expressed in any acceptable CSS form.
* **height**: the height of the widget container, expressed in any acceptable CSS form.
* **style**: CSS styles to assign to the widget container.
* **class**: CSS classes to assign to the widget container.
* **widgetstyle**: use a preset style for the widget. You can choose among:

	- *docked*: (the default) the widget is placed on the page where the shortcode is located, with standard formatting.
	- *sticky-top*: the widget is placed on the page where the shortcode is located, with standard formatting, but stays visible once the reader scrolls below that position.
	- *none*: the widget is placed on the page where the shortcode is located, and no formatting is applied to it. This is useful if you need to use the *style* and/or *class* attributes to define your own widget layout. When you choose *none*, attributes *width*, *maxwidth* and *height* are also ignored.

= How do I add sound effects to my post?=
Use the *[aetag fga]* shortcode. The shortcode takes the following options:

* **href**: (required) the URL of the recorded sound.
* **pausebefore**: a pause to be added before starting sound playback, in seconds (e.g. "0.5").
* **pauseafter**: a pause to be added at the end of sound playback, before resuming the text. In seconds.
* **title**: the title of the sound.
* **attribution**: any additional information about the sound.

*title* and *attribution* are not used by the allEars player, but they're available for compliance with royalty-free audio licenses.

= Where can I find royalty-free sound effects?=
Royalty-free sound effects are readily available for download on websites like [soundbible.com](http://soundbible.com/royalty-free-sounds-1.html) or [audioblocks.com](https://www.audioblocks.com/royalty-free-audio/sound-effects).

= I've used the [aetag fga] but I see an error =
The allEars web player and the allEars widget operate under the [getallears.com](https://getallears.com) domain. When you add an audio file to your site, you need to make sure [getallears.com](https://getallears.com) is allowed to download the file (by your site's CORS configuration). If your CORS configuration is incomplete, the allEars player might not be allowed to download the audio file. You can confirm this by checking your browser's console. On the Chrome browser, the error will look like this:

	Access to XMLHttpRequest at '<url>' from origin 'https://getallears.com' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.

= Can I control how text is being read? =
You can use the *[aetag sub]*, *[aetag lang]* and *[aetag as]* to control reading pronunciation. These shortcodes require one extra parameter, and they enclose the text they need to control.

*Examples*

* **[aetag sub "Colorado"]CO[/aetag]**: read the word "Colorado", but show the word "CO" on the webpage and in the allEars player text captions.
* **[aetag as "verb"]attribute[/aetag]**: use the "verb" form of the word "attribute" (different stress than the noun). The valid options for the parameter of *[aetag as]* are *verb*, *past* (for words that have different pronunciations in past tense) or *alt* (for words that have multiple pronunciations).
* **[aetag lang "it"]Piazza Navona[/aetag]**: pronounce the text in italian, regardless of the language of the rest of the post.


== Changelog ==

= 1.1.1 =
*Release Date - 11/16/2018*

* Fixed typo in readme.txt


= 1.1.0 =
*Release Date - 11/15/2018*

* New per-post configuration
	- Voice (including alternate male/female voices)
	- Language
	- Background audio (URL, gain, title and attribution)
* Added new shortcode "[aetag]" to allow embedding of any AEC tag in HTML ("aetags")
	- Initially supporting the following aetags for end users:
		* fga, p, voice, sub, ipa, lang, as, ignore


= 1.0.0 =
*Release Date - 10/27/2018*

* Initial release