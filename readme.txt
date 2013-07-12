=== IP2Location Tags ===
Contributors: IP2Location
Donate link: http://www.ip2location.com
Tags: targeted content, geolocation
Requires at least: 2.0
Tested up to: 3.5.2
Stable tag: 2.0

Description: Customize blog content by visitor's location.

== Description ==

Use the special tags to display geolocation information of your visitors. You also can use this plugin to hide/show post content as well.

Here is an example to display visitor origin country name.

Example:
You are coming from {ip:countryName}.

Result:
You are coming from United States.



To customize your post content, follow the example below.

To display content for United States and Canada visitors only, specify the ISO-3166 country code in the <ip> tag:

<ip:US,CA>Only visitors from United States and Canada can view this line.</ip>



To hide a content from visitors from Nigeria:

<ip:-ng>Visitors from Nigeria will not able to view this line.</ip>



Also, you can use this tag to display correct currency in your blog post.

Exmaple:

I bought a Apple computer for $100 <ip:GB>(£66)</ip><ip:JP>(¥9940)</ip>.



== Installation ==

1. Upload `ip2location` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can now start using IP2Location tag to customize your post content.