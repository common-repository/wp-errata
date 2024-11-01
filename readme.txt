=== Plugin Name ===
Contributors: Jaspreet Chahal
Plugin URI: http://jaspreetchahal.org/wordpress-errata-plugin
Donate link:  http://jaspreetchahal.org/wordpress-errata-plugin
Tags: Errata, user post edits, part post edit, contenteditable, auto edits, errata, article errors, mistakes, corrections, inline post correction
Requires at least: 2.8
Version: 1.0
Tested up to: 3.5
Stable tag: 1.0

This plugin allows you to receive marked post paragraph corrections from your blog readers. You can make make specific containers such as DIV, p etc inside the post editable.

== Description ==

If you would want to make some content in your post editable so that your blog reader can offer edits without really caucing any damage to your post then this plugin is probably right for you.
A video on how it operates is available from my blog post.

<h2>How to use it?</h2>
Activate this plugin and in your post mark any content up for correction  with using class="jcedit"
e.g. make a div to take corrections as follows


<strong>&lt;div class="jcedit">This content can be sent for correction. Make your edits and send us your corrections.&lt;/div></strong>


<h2>Remember</h2>
That you should not have any markup under class="jcedit" container i.e. something like this will not work

&lt;div class="jcedit">
&lt;ul>
	&lt;li>This is correctable&lt;/li>
&lt;/ul>
&lt;/div>

Only works in latest of the browsers those which support contenteditable.


An email will be sent to entered email address.

You can follow me on Twitter https://twitter.com/jschahal or 
like my facebook page http://www.facebook.com/jaspreetchahal.org to get updates on some not so exciting things that I do.

Visit my site http://jaspreetchahal.org to get help on this plugin or more precisely go to http://jaspreetchahal.org/wordpress-errata-plugin


== Installation ==

1. Upload unzipped plugin directory to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the "WP Errata" Options under settings.

== Frequently Asked Questions ==

= Will you be adding more to it? =
I may soon be writing a PRO version of this plugin. There is so much room for improvement. Leave your comments here to features your would like to see http://jaspreetchahal.org/wordpress-errata-plugin


== Changelog == 

= 1.0 =
* Initial Release

== Screenshots ==-

1. A screen shot of the options page
2. Shows content being edited

== Donations ==
If you would like to donate to help support future development of this plugin, please go to [Jaspreet Chahal](http://jaspreetchahal.org/wordpress-errata-plugin)