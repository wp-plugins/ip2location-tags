=== IP2Location Tags ===
Contributors: IP2Location
Donate link: http://www.ip2location.com
Tags: targeted content, geolocation
Requires at least: 2.0
Tested up to: 3.5.2
Stable tag: 2.0

Description: IP2Location Tag provides a solution to easily get the visitor's location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it.

== Description ==

IP2Location Tag provides a solution to easily get the visitor's location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it. Depending on the BIN file that you are using, this plugin is able to provide you the information of country, region or state, city, latitude and longitude, US ZIP code, time zone, Internet Service Provider (ISP) or company name, domain name, net speed, area code, weather station code, weather station name, mobile country code (MCC), mobile network code (MNC) and carrier brand, elevation and usage type of origin for an IP address.

BIN file download:<br/>
IP2Location Commercial database: http://ip2location.com/buy<br/>
IP2Location LITE database (free edition): http://lite.ip2location.com/<br/>

Example:
You are coming from {ip:countryName}.

Result:
You are coming from United States.



To customize your post content, follow the example below.

To display content for United States and Canada visitors only, specify the ISO-3166 country code in the &lt;ip&gt; tag:

&lt;ip:US,CA&gt;Only visitors from United States and Canada can view this line.&lt;/ip&gt;



To hide a content from visitors from Nigeria:

&lt;ip:-ng&gt;Visitors from Nigeria will not able to view this line.&lt;/ip&gt;



Also, you can use this tag to display correct currency in your blog post.

Exmaple:

I bought a Apple computer for $100 &lt;ip:GB&gt;(£66)&lt;/ip&gt;&lt;ip:JP&gt;(¥9940)&lt;/ip&gt;.



== Installation ==

1. Upload `ip2location` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can now start using IP2Location tag to customize your post content.