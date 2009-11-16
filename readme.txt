=== IP2Location Tags ===
Contributors: IP2Location
Donate link: http://www.ip2location.com
Tags: targeted content, geolocation
Requires at least: 2.0
Tested up to: 2.8.6
Stable tag: 1.00

Description: Customize post content by visitor's location.

== Description ==

Use the [ip2loc][/ip2loc] tags to dislay geolocation information of your visitors. You can use this plugin to block/display customized post content as well.

Here is an example to display visitor country name:

You are coming from [ip2loc:country].



To customize your post content, display following text only for visitor from United States and Japan:

[ip2loc:us;jp]Only visitor from United States and Japan can view this[/ip2loc]



To hide a text from visitor from Nigeria:

[ip2loc:-ng]Visitors from Nigeria can't see this text.[/ip2loc]



== Installation ==

1. Upload `ip2location` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can now start using IP2Location tag to customize your post content.