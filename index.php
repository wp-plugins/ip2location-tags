<?php

/*
Plugin Name: IP2Location Tag
Plugin URI: http://www.ip2location.com/developers
Description: Enable you to use IP2Location tags to customize your post content by country.
Version: 2.0
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
		$_SERVER['REMOTE_ADDR'] = '8.8.8.8';
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

	function parse($content){
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
					Use <b>IP2Location Tag</b> to customize your blog content. You can hide or show your blog content for visitors from specified countries.
				</p>

				<p>&nbsp;</p>';

			if(!file_exists(_ROOT . 'database.bin')){
				echo '
				<p class="red">
					IP2Location database is not found. Please download IP2Location Lite database from <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">http://lite.ip2location.com</a>.
				</p>
				<p class="red">
					After downloaded the package, decompress it and rename the .BIN file in the package to <b>database.bin</b>. Upload <b>database.bin</b> to <i>/wp-content/plugins/ip2location-tags/</i>.
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
						<b>Reminder: </b>Your IP2Location database already outdated, this will lead to inaccurate result. Please download the latest version from <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">http://lite.ip2location.com</a>.
					</p>
					<p class="red">
						After downloaded the package, decompress it and rename the .BIN file in the package to <b>database.bin</b>. Upload <b>database.bin</b> to <i>/wp-content/plugins/ip2location-tags/</i>.
					</p>';
				}
			}

			echo '
				<p>&nbsp;</p>

				<h3>Variable Tags</h3>

				<p>Here is the list of variable tags.</p>

				<p>
					<ul>
						<li><span class="code">{ip:ipAddress}</span> - Visitor IP address.</li>
						<li><span class="code">{ip:countryCode}</span> - Two-character country code based on ISO 3166.</li>
						<li><span class="code">{ip:countryName}</span> - Country name based on ISO 3166.</li>
						<li><span class="code">{ip:regionName}</span> - Region, province or state name.</li>
						<li><span class="code">{ip:cityName}</span> - City name.</li>
						<li><span class="code">{ip:latitude}</span> - Latitude of the city.</li>
						<li><span class="code">{ip:longitude}</span> - Longitude of the city.</li>
						<li><span class="code">{ip:zipCode}</span> - ZIP/Postal code.</li>
						<li><span class="code">{ip:isp}</span><span class="red">*</span> - Internet Service Provider or company\'s name.</li>
						<li><span class="code">{ip:domainName}</span><span class="red">*</span> - Internet domain name associated to IP address range.</li>
						<li><span class="code">{ip:timeZone}</span><span class="red">*</span> - UTC time zone.</li>
						<li><span class="code">{ip:netSpeed}</span><span class="red">*</span> - Internet connection type. DIAL = dial up, DSL = broadband/cable, COMP = company/T1</li>
						<li><span class="code">{ip:iddCode}</span><span class="red">*</span> - The IDD prefix to call the city from another country.</li>
						<li><span class="code">{ip:areaCode}</span><span class="red">*</span> - A varying length number assigned to geographic areas for call between cities.</li>
						<li><span class="code">{ip:weatherStationCode}</span><span class="red">*</span> - The special code to identify the nearest weather observation station.</li>
						<li><span class="code">{ip:weatherStationName}</span><span class="red">*</span> - The name of the nearest weather observation station.</li>
						<li><span class="code">{ip:mcc}</span><span class="red">*</span> - Mobile Country Codes (MCC) as defined in ITU E.212 for use in identifying mobile stations in wireless telephone networks, particularly GSM and UMTS networks.</li>
						<li><span class="code">{ip:mnc}</span><span class="red">*</span> - Mobile Network Code (MNC) is used in combination with a Mobile Country Code (MCC) to uniquely identify a mobile phone operator or carrier.</li>
						<li><span class="code">{ip:mobileCarrierName}</span><span class="red">*</span> - Commercial brand associated with the mobile carrier.</li>
						<li><span class="code">{ip:elevation}</span><span class="red">*</span> - Average height of city above sea level in meters (m).</li>
						<li><span class="code">{ip:usageType}</span><span class="red">*</span> - Usage type classification of ISP or company.</li>
					</ul>
				</p>
				<p class="red">
					* Additional tags only available with commercial database from <a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location.com</a>.
				</p>
				<p>&nbsp;</p>

				<h4>Variable Tags Usage</h4>

				<p>
					<b>Code:</b>
					<pre>Your IP is {ip:ipAddress}
You are came from {ip:countryName}, {ip:regionName}, {ip:cityName}</pre>
					<b>Result:</b>
					<div class="result">Your IP is 8.8.8.8<br>You are came from United States, California, Mountain View</div>
				</p>

				<p>&nbsp;</p>

				<h3>Show/Hide Content with Tags</h3>

				<p>
					<b>Syntax:</b>
					<pre>&lt;ip:XX[,XX]..[,XX]&gt;You content here.&lt;/ip&gt;</pre>

					<div class="red"><b>Note:</b> XX is two digit ISO-3166 country code. Use "*" as wildcard.</div>
				</p>

				<p>&nbsp;</p>

				<h4>Usage</h4>

				<p>Use IP2Location tag to show content for visitors from speficic countries.</p>
				<p>
					<b>Code:</b>
					<pre>&lt;ip:US,CA&gt;Only visitors from United States and Canada can view this line.&lt;/ip&gt;</pre>

					<b>Result for visitor from United Status:</b>
					<div class="result">Only visitors from United States and Canada can view this line.</div>

					<b>Result for visitor from India:</b>
					<div class="result">&nbsp;</div>
				</p>

				<p>&nbsp;</p>

				<p>Use IP2Location tag to hide content for visitors from speficic countries. Add a "-" sign before country code to exclude the country.</p>
				<p>
					<b>Code:</b>
					<pre>&lt;ip:*,-VN&gt;All visitors able to see this line except visitors from Vietnam.&lt;/ip&gt;</pre>

					<b>Result for visitor from United Status:</b>
					<div class="result">All visitors able to see this line except visitors from Vietnam.</div>

					<b>Result for visitor from Vietname:</b>
					<div class="result">&nbsp;</div>
				</p>

				<p>&nbsp;</p>

				<p>Use IP2Location tag to show related currencies.</p>
				<p>
					<b>Code:</b>
					<pre>I\'m selling my computer for $100&lt;ip:GB&gt; (£66)&lt;/ip&gt;&lt;ip:JP&gt; (¥9940)&lt;/ip&gt;.</pre>

					<b>Result for visitor from United Status:</b>
					<div class="result">I\'m selling my computer for $100.</div>

					<b>Result for visitor from United Kingdom:</b>
					<div class="result">I\'m selling my computer for $100 (£66).</div>

					<b>Result for visitor from Japan:</b>
					<div class="result">I\'m selling my computer for $100 (¥9940).</div>

					<b>Result for visitor from China:</b>
					<div class="result">I\'m selling my computer for $100.</div>
				</p>

				<p>&nbsp;</p>

				<h3>References</h3>

				<p>Please refer to ISO website for <a href="http://www.iso.org/iso/home/standards/country_codes/country_names_and_code_elements.htm" target="_blank">country codes</a>.</p>';
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
		add_filter('widget_text', array(&$this, 'parse'));
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