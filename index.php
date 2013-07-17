<?php

/*
Plugin Name: IP2Location Tag
Plugin URI: http://ip2location.com/tutorials/wordpress-ip2location-tag
Description: Enable you to use IP2Location tags to customize your post content by country.
Version: 2.1
Author: IP2Location
Author URI: http://www.ip2location.com
*/

define('DS', DIRECTORY_SEPARATOR);
define('_ROOT', dirname(__FILE__) . DS);

class IP2LocationTag {
	var $result = array(
		'ipAddress'=>'',
		'countryCode'=>'',
		'countryName'=>'',
		'regionName'=>'',
		'cityName'=>'',
		'latitude'=>'',
		'longitude'=>'',
		'isp'=>'',
		'domainName'=>'',
		'zipCode'=>'',
		'timeZone'=>'',
		'netSpeed'=>'',
		'iddCode'=>'',
		'areaCode'=>'',
		'weatherStationCode'=>'',
		'weatherStationName'=>'',
		'mcc'=>'',
		'mnc'=>'',
		'mobileCarrierName'=>'',
		'elevation'=>'',
		'usageType'=>'',
	);

	function getLocation() {
		// Skip geolocation lookup for admin pages
		if(is_admin()) return false;

		// Make sure IP2Location database is exist
		if(!file_exists(_ROOT . 'database.bin')) return false;

		require_once(_ROOT . 'ip2location.class.php');

		// Create IP2Location object
		$geo = new IP2Location(_ROOT . 'database.bin');

		// Get geolocation by IP address
		$result = $geo->lookup($_SERVER['REMOTE_ADDR']);

		$this->result['ipAddress'] = $_SERVER['REMOTE_ADDR'];

		foreach($result as $key=>$value){
			if(isset($this->result[$key])) $this->result[$key] = ((in_array($key, array('countryName', 'regionName', 'cityName'))) ? $this->_case($value) : $value);
		}

		return true;
	}

	function parseTag($s, $start, $end) {
		$s = ' ' . $s;
		$data = strpos($s, $start);
		if($data == 0) return '';
		$data += strlen($start);
		$len = strpos($s, $end, $data) - $data;

		return substr($s, $data, $len);
	}

	function parseWidget($content){
		// Escape tags
		$content = str_replace(array('<', '>'), array('&lt;', '&gt;'), $content);

		// Parse widget content
		$content = $this->parse($content, true);

		// Restore tags and return value
		return str_replace(array('&lt;', '&gt;'), array('<', '>'), $content);
	}

	function parse($content, $widget=false){
		$find = array(
			'{ip:ipAddress}',
			'{ip:countryCode}',
			'{ip:countryName}',
			'{ip:regionName}',
			'{ip:cityName}',
			'{ip:latitude}',
			'{ip:longitude}',
			'{ip:isp}',
			'{ip:domainName}',
			'{ip:zipCode}',
			'{ip:timeZone}',
			'{ip:netSpeed}',
			'{ip:iddCode}',
			'{ip:areaCode}',
			'{ip:weatherStationCode}',
			'{ip:weatherStationName}',
			'{ip:mcc}',
			'{ip:mnc}',
			'{ip:mobileCarrierName}',
			'{ip:elevation}',
			'{ip:usageType}',
		);

		$replace = array(
			$this->result['ipAddress'],
			$this->result['countryCode'],
			$this->result['countryName'],
			$this->result['regionName'],
			$this->result['cityName'],
			$this->result['latitude'],
			$this->result['longitude'],
			$this->result['isp'],
			$this->result['domainName'],
			$this->result['zipCode'],
			$this->result['timeZone'],
			$this->result['netSpeed'],
			$this->result['iddCode'],
			$this->result['areaCode'],
			$this->result['weatherStationCode'],
			$this->result['weatherStationName'],
			$this->result['mcc'],
			$this->result['mnc'],
			$this->result['mobileCarrierName'],
			$this->result['elevation'],
			$this->result['usageType'],
		);

		// Replace geolocation variables
		$content = str_replace($find, $replace, $content);

		// Parse IP2Location tags
		do{
			// Get country list from tag
			$data = $this->parseTag($content, '&lt;ip:', '&gt;');

			// Get protected text from tag
			$text = $this->parseTag($content, '&lt;ip:' . $data . '&gt;', '&lt;/ip&gt;');

			// Get the whole tag
			$from = '&lt;ip:' . $data . '&gt;' . $text . '&lt;/ip&gt;';

			$countries = explode(',', str_replace(' ', '', strtoupper($data)));

			$to = '';

			// Show text for listed country
			if(in_array($this->result['countryCode'], $countries)){
				$to = $text;
			}

			// Show text if wildcard defined
			if(in_array('*', $countries)){
				$to = $text;
			}

			// Hide text for prohibited country
			if(in_array('-' . $this->result['countryCode'], $countries)){
				$to = '';
			}

			$content = str_replace($from, $to, $content);

		} while(!empty($data));

		return $content;
	}

	function admin_options() {
		if(is_admin()) {
			echo '
			<style type="text/css">
				.red{color:#cc0000}
				.code{color:#003399;font-family:\'Courier New\'}
				pre{margin:0 0 20px 0;border:1px solid #c0c0c0;backgroumd:#e4e4e4;color:#535353;font-family:\'Courier New\';padding:8px}
				.result{margin:0 0 20px 0;border:1px solid #006699;backgroumd:#99ffcc;color:#000033;padding:8px}
			</style>
			<div class="wrap">
				<h3>IP2LOCATION TAG</h3>
				<p>
					IP2Location Tag provides a solution to easily get the visitor\'s location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it. Depending on the BIN file that you are using, this plugin is able to provide you the information of country, region or state, city, latitude and longitude, US ZIP code, time zone, Internet Service Provider (ISP) or company name, domain name, net speed, area code, weather station code, weather station name, mobile country code (MCC), mobile network code (MNC) and carrier brand, elevation and usage type of origin for an IP address.<br/><br/>
					BIN file download: <a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location Commercial database</a> | <a href="http://lite.ip2location.com/?r=wordpress" targe="_blank">IP2Location LITE database (free edition)</a>.
				</p>

				<p>&nbsp;</p>';

			if(!file_exists(_ROOT . 'database.bin')){
				echo '
				<p class="red">
					IP2Location BIN file not found. Please download the BIN file at the following links:
					<a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location commercial database</a> | <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">IP2Location LITE database (free edition)</a>.
				</p>
				<p class="red">
					After downloaded the package, decompress it and rename the .BIN file inside the package to <strong>database.bin</strong>. The, upload the BIN file,<strong>database.bin</strong>, to <em>/wp-content/plugins/ip2location-tags/</em>.
				</p>';
			}
			else{
				echo '
				<p>
					<b>Database Version: </b>
					' . date('F Y', filemtime(_ROOT . 'database.bin')) . '
				</p>';

				if(filemtime(_ROOT . 'database.bin') < strtotime('-2 months')){
					echo '
					<p class="red">
						<b>Reminder: </b>Your IP2Location database was outdated. Please download the latest version from <a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location commercial database</a> or <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">IP2Location LITE database (free edition)</a>..
					</p>
					<p class="red">
						After downloaded the package, decompress it and rename the .BIN file inside the package to <strong>database.bin</strong>. The, upload the BIN file,<strong>database.bin</strong>, to <em>/wp-content/plugins/ip2location-tags/</em>.
					</p>';
				}
			}

			//HJ modified - START//
			echo '
				<p>&nbsp;</p>

				<h3>Get visitor\'s location information with Variable Tag</h3>
				<p>
					<strong>Variable Tag List</strong>
					<ul>
						<li><span class="code">{ip:ipAddress}</span> - Visitor IP address.</li>
						<li><span class="code">{ip:countryCode}</span> - Two-character country code based on ISO 3166.</li>
						<li><span class="code">{ip:countryName}</span> - Country name based on ISO 3166.</li>
						<li><span class="code">{ip:regionName}</span> - Region, province or state name.</li>
						<li><span class="code">{ip:cityName}</span> - City name.</li>
						<li><span class="code">{ip:latitude}</span> - Latitude of the city.</li>
						<li><span class="code">{ip:longitude}</span> - Longitude of the city.</li>
						<li><span class="code">{ip:zipCode}</span> - ZIP/Postal code.</li>
						<li><span class="code">{ip:isp}</span> - Internet Service Provider or company\'s name.</li>
						<li><span class="code">{ip:domainName}</span> - Internet domain name associated to IP address range.</li>
						<li><span class="code">{ip:timeZone}</span> - UTC time zone.</li>
						<li><span class="code">{ip:netSpeed}</span> - Internet connection type. DIAL = dial up, DSL = broadband/cable, COMP = company/T1</li>
						<li><span class="code">{ip:iddCode}</span> - The IDD prefix to call the city from another country.</li>
						<li><span class="code">{ip:areaCode}</span> - A varying length number assigned to geographic areas for call between cities.</li>
						<li><span class="code">{ip:weatherStationCode}</span> - The special code to identify the nearest weather observation station.</li>
						<li><span class="code">{ip:weatherStationName}</span> - The name of the nearest weather observation station.</li>
						<li><span class="code">{ip:mcc}</span> - Mobile Country Codes (MCC) as defined in ITU E.212 for use in identifying mobile stations in wireless telephone networks, particularly GSM and UMTS networks.</li>
						<li><span class="code">{ip:mnc}</span> - Mobile Network Code (MNC) is used in combination with a Mobile Country Code (MCC) to uniquely identify a mobile phone operator or carrier.</li>
						<li><span class="code">{ip:mobileCarrierName}</span> - Commercial brand associated with the mobile carrier.</li>
						<li><span class="code">{ip:elevation}</span> - Average height of city above sea level in meters (m).</li>
						<li><span class="code">{ip:usageType}</span> - Usage type classification of ISP or company.</li>
					</ul>
				</p>
				<p>&nbsp;</p>

				<h4>Usage Example</h4>

				<p>
					<b>Display visitor\'s IP address, country name, region name and city name.</b>
					<pre>Your IP is {ip:ipAddress}
You are came from {ip:countryName}, {ip:regionName}, {ip:cityName} </pre>
				</p>

				<p>&nbsp;</p>

				<h3>Customize the post content with IP2Location Tag</h3>
				<p>
					<h4>Syntax to show content for specific country</h4>
					<pre>&lt;ip:XX[,XX]..[,XX]&gt;You content here.&lt;/ip&gt;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					To show the content for United States and Canada visitors only.<br/>
					<pre>&lt;ip:US,CA&gt;Only visitors from United States and Canada can view this line.&lt;/ip&gt;</pre>
				</p>
				<p>&nbsp;</p>
				<p>
					<h4>Syntax to hide the content from specific country</h4>
					<pre>&lt;ip:*,-XX[,-XX]..[,-XX]&gt;You content here.&lt;/ip&gt;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					All visitors will be able to see the line except visitors from Vietnam.</br>
					<pre>&lt;ip:*,-VN&gt;All visitors will be able to see this line except visitors from Vietnam.&lt;/ip&gt;</pre>
				</p>

				<p>&nbsp;</p>

				<h3>References</h3>

				<p>Please visit <a href="http://www.ip2location.com/free/country-multilingual" target="_blank">http://www.ip2location.com</a> for ISO country codes and names supported.</p>';
				//HJ modified - END
		}
	}

	function admin_page(){
		add_management_page('IP2Location Tag', 'IP2Location Tag', 8, 'ip2location-tag', array(&$this, 'admin_options'));
	}

	function activate(){
		die(header('Location: edit.php?page=ip2location-content'));
	}

	function start(){
		add_action('wp', array(&$this, 'getLocation'), 101);
		add_action('admin_menu', array(&$this, 'admin_page'));
		add_filter('the_content', array(&$this, 'parse'));
		add_filter('widget_text', array(&$this, 'parseWidget'));
	}

	function _case($s){
		$s = ucwords(strtolower($s));
		$s = preg_replace_callback("/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s",create_function('$matches','return $matches[1].strtoupper($matches[2]);'),$s);
		return $s;
	}

}

// Initial class
$geo = new IP2LocationTag();
$geo->start();

// Activate
register_activation_hook(__FILE__, array(&$cbc, 'activate'));
?>