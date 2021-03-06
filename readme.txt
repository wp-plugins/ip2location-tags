=== IP2Location Tags ===
Contributors: IP2Location
Donate link: http://www.ip2location.com
Tags: targeted content, geolocation
Requires at least: 2.0
Tested up to: 4.3
Stable tag: 2.4.0

Description: Easily get/display the visitor's location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it.

== Description ==

IP2Location Tag provides a solution to easily get the visitor's location information based on IP address and customize the content display for different countries and regions. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it. Depending on the BIN file that you are using, this plugin is able to provide you the information of country, region or state, city, latitude and longitude, US ZIP code, time zone, Internet Service Provider (ISP) or company name, domain name, net speed, area code, weather station code, weather station name, mobile country code (MCC), mobile network code (MNC) and carrier brand, elevation and usage type of origin for an IP address.

BIN file download: [IP2Location Commercial database](http://ip2location.com "IP2Location commercial database") | [IP2Location LITE database (free edition)](http://lite.ip2location.com "IP2Location LITE database (free edition)")

= Get visitor's location information with Variable Tag =
*Usage example*

Display visitor's IP address, country name, region name and city name.
*Your IP is {ip:ipAddress}*
*You are came from {ip:countryName}, {ip:regionName}, {ip:cityName}*

= Customize the post content with IP2Location Tag =
**Syntax to show content for specific country**
*[ip:XX[,XX]..[,XX]]You content here.[/ip]*
Note: XX is a two-digit ISO-3166 country code.

*Example*
To show the content for United States or Canada visitors only.
*[ip:US,CA]Only visitors from United States or Canada can view this line.[/ip]*

**Syntax to show content for specific country and region**
*[ip:XX:YY[,XX:YY]..[,XX:YY]]You content here.[/ip]*
Note: XX is a two-digit ISO-3166 country code and YY is a ISO-3166-2 sub division code.

*Example*
*To show the content for California or New York visitors only.*
[ip:US:CA,US:NY]Only visitors from California or New York can view this line.[/ip]

**Syntax to hide the content from specific country**
*[ip:\*,-XX[,-XX]..[,-XX]]You content here.[/ip]*
Note: XX is a two-digit ISO-3166 country code.

*Example*
All visitors will be able to see the line except visitors from Vietnam.
*[ip:\*,-VN]All visitors will be able to see this line except visitors from Vietnam.[/ip]*

**Syntax to hide the content from specific country and region**
*[ip:*,-XX:YY[,-XX:YY]..[,-XX:YY]]You content here.[/ip]*
Note: XX is a two-digit ISO-3166 country code and YY is a ISO-3166-2 sub division code.

*Example*
All visitors will be able to see the line except visitors from California.
[ip:*,-US:CA]All visitors will be able to see this line except visitors from California.[/ip]

= More Information =
Please visit us at [http://www.ip2location.com](http://www.ip2location.com/tutorials/wordpress-ip2location-tag "http://www.ip2location.com")

== Installation ==

1. Upload `ip2location` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Download the latest BIN database at settings page.
4. You can now start using IP2Location tag to customize your post content.

== Changelog ==

* 2.1.0 Initial release.
* 2.2.0 Support database downloading on settings page.
		Support bracket [] to define the tag rule in addition to &lt;&lgt;, to solve of issue of being treated as script tag by wordpress.
* 2.3.0 Fixed crashed with other IP2Location plugins.
* 2.3.1 Fixed minors issues and WordPress standards.
* 2.3.2 Fixed compatibilities with widgets.
* 2.3.3 Support the customization of the contents based on region/state.
* 2.4.0 Fixed various performance issues. Added IP2Location Web service support.