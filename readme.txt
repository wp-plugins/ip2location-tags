=== IP2Location Tags ===
Contributors: IP2Location
Donate link: http://www.ip2location.com
Tags: targeted content, geolocation
Requires at least: 2.0
Tested up to: 3.5.2
Stable tag: 2.0

Description: Easily get/display the visitor's location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it.

== Description ==

IP2Location Tag provides a solution to easily get the visitor's location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it. Depending on the BIN file that you are using, this plugin is able to provide you the information of country, region or state, city, latitude and longitude, US ZIP code, time zone, Internet Service Provider (ISP) or company name, domain name, net speed, area code, weather station code, weather station name, mobile country code (MCC), mobile network code (MNC) and carrier brand, elevation and usage type of origin for an IP address.  

BIN file download: [IP2Location Commercial database](http://ip2location.com "IP2Location commercial database") | [IP2Location LITE database (free edition)](http://lite.ip2location.com "IP2Location LITE database (free edition)")  

= Get visitor's location information with Variable Tag =
*Usage example*  
  
Display visitor's IP address, country name, region name and city name.  
*Your IP is {ip:ipAddress}*  
*You are came from {ip:countryName}, {ip:regionName}, {ip:cityName}*  

= Customize the post content with IP2Location Tag =
**Syntax to show content for specific country**  
*&lt;ip:XX[,XX]..[,XX]&gt;You content here.&lt;/ip&gt;*  
Note: XX is a two-digit ISO-3166 country code.  

*Example*  
To show the content for United States and Canada visitors only.  
*&lt;ip:US,CA&gt;Only visitors from United States and Canada can view this line.&lt;/ip&gt;*  

**Syntax to hide the content from specific country**  
*&lt;ip:\*,-XX[,-XX]..[,-XX]&gt;You content here.&lt;/ip&gt;*  
Note: XX is a two-digit ISO-3166 country code.  

*Example*  
All visitors will be able to see the line except visitors from Vietnam.  
*&lt;ip:\*,-VN&gt;All visitors will be able to see this line except visitors from Vietnam.&lt;/ip&gt;*  

= More Information =
Please visit us at [http://www.ip2location.com](http://www.ip2location.com/tutorials/wordpress-ip2location-tag "http://www.ip2location.com")  

== Installation ==

1. Upload `ip2location` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. You can now start using IP2Location tag to customize your post content.