=== Contact Form 7 to REST Call ===
Contributors: Nikolay Chankov
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NKQSUPRS36B6J
Tags: Contact Form 7, REST, Web Service
Requires at least: Contact Form 7
Tested up to: 1.0
Stable tag: 1.0
License: GPLv2 or later



Extension to the Contact Form 7 plugin that send data to REST Call.

== Description ==

First a disclaimer: I am not the maker of Contact Form 7 or associated with it's author.

That being said, I think Contact Form 7 is great...except for one thing. It does not save or post its information.
This plugin allows to save history of submits and send XML REST call to web service.
It can easily be modified to make other than XML data calls.

== Installation ==

1. Be sure that Contact Form 7 is installed and activated (this is an extension to it)
1. Import contact-form-7-REST.zip via the 'Plugins' menu in WordPress
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Adds an Admin menu item for seeing the stored data
1. Edit conf.php file to set call settings

Notes:
* Installing this plugin creates its own table. If you uninstall it, it will delete its table and any data you have in it. (But you can deactivate it without loosing any data).
* Tested on WP 3.0, PHP 5.2.13, MySQL 5.0 (Using 1and1 for hosting)

== Changelog ==

= 1.0 =
* Initial Revision.

== Screenshots ==
http://chankov.info/blog/temp/screen1-cf7REST.png

