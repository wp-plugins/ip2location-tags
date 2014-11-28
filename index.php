<?php

/*
Plugin Name: IP2Location Tags
Plugin URI: http://ip2location.com/tutorials/wordpress-ip2location-tag
Description: Enable you to use IP2Location tags to customize your post content by country.
Version: 2.3.3
Author: IP2Location
Author URI: http://www.ip2location.com
*/

!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
define('IP2LOCATION_TAGS_ROOT', dirname(__FILE__) . DS);

class IP2LocationTags {

	function get_location( $ip ) {
		// Make sure IP2Location database is exist.
		if ( !is_file( IP2LOCATION_TAGS_ROOT . get_option( 'ip2location_tags_database' ) ) ) {
			return;
		}

		if ( ! class_exists( 'IP2LocationRecord' ) && ! class_exists( 'IP2Location' ) ) {
			require_once( IP2LOCATION_TAGS_ROOT . 'ip2location.class.php' );
		}

		// Create IP2Location object.
		$geo = new IP2Location( IP2LOCATION_TAGS_ROOT . get_option( 'ip2location_tags_database' ) );

		// Get geolocation by IP address.
		$response = $geo->lookup( $ip );

		return array(
			'ipAddress' => $ip,
			'countryCode' => $response->countryCode,
			'countryName' => IP2LocationTags::set_case( $response->countryName ),
			'regionName' => IP2LocationTags::set_case( $response->regionName ),
			'cityName' => IP2LocationTags::set_case( $response->cityName ),
			'latitude' => $response->latitude,
			'longitude' => $response->longitude,
			'isp'=> $response->isp,
			'domainName' => $response->domainName,
			'zipCode' => $response->zipCode,
			'timeZone' => $response->timeZone,
			'netSpeed' => $response->netSpeed,
			'iddCode' => $response->iddCode,
			'areaCode' => $response->areaCode,
			'weatherStationCode' => $response->weatherStationCode,
			'weatherStationName' =>IP2LocationTags::set_case( $response->weatherStationName ) ,
			'mcc' => $response->mcc,
			'mnc' => $response->mnc,
			'mobileCarrierName' => IP2LocationTags::set_case( $response->mobileCarrierName ),
			'elevation' => $response->elevation,
			'usageType' => $response->usageType,
		);
	}

	function parse_tag( $s, $start, $end ) {
		$s = ' ' . $s;
		$data = strpos( $s, $start );

		if ( $data == 0 ) {
			return '';
		}

		$data += strlen( $start );
		$len = strpos( $s, $end, $data ) - $data;

		return substr( $s, $data, $len );
	}

	function parse_widget( $content ) {
		// Escape tags
		$content = str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $content );

		// Parse widget content
		$content = IP2LocationTags::parse( $content, true );

		// Restore tags and return value
		return str_replace( array( '&lt;', '&gt;' ), array( '<', '>' ), $content );
	}

	//115 : converting XX:YY code into region name
	function code_to_region($countries,$regionCode,$regionName){
		if($regionName == "This Field Is Not Supported In Db1. Please Upgrade Your Ip2location Database."){
			for($i = 0 ; $i < count($countries); $i++){
				if(strlen($countries[$i])>3){
					if($countries[$i][0] == '-'){
						$countries[] = substr($countries[$i],0,3);
						$countries[$i] = '-' . $regionCode[str_replace('-','',$countries[$i])];
					}
					else{
						$countries[] = substr($countries[$i],0,2);
						$countries[$i] = $regionCode[$countries[$i]];
					}
				}
			}
		}
		else{
			for($i = 0 ; $i < count($countries); $i++){
				if(strlen($countries[$i])>3){
					if($countries[$i][0] == '-'){
						if($regionCode[str_replace('-','',$countries[$i])] == ""){
							$countries[] = substr($countries[$i],0,3);
						}
						$countries[$i] = '-' . $regionCode[str_replace('-','',$countries[$i])];
					}
					else{
						if($regionCode[$countries[$i]]==""){
							$countries[] = substr($countries[$i],0,2);
						}
						$countries[$i] = $regionCode[$countries[$i]];
					}
				}
			}
		}
		return $countries;
	}
	//115 : Retrieve ISO3166-2 code from the csv file
	function retrieve_ISO3316(){
		$handle = fopen(IP2LOCATION_TAGS_ROOT . "IP2LOCATION-ISO3166-2.CSV",'r');
		$input = fgetcsv($handle,0,"\r\n")!="";
		while(($input = fgetcsv($handle,0,"\r\n"))!=""){
			$input = implode("",str_replace("\"","", $input));
			$input = explode(",",$input);
			$input[2] = str_replace("-",":",$input[2]);
			$regionCode[$input[2]] = $input[1];
			$countryRegion[$input[0]][] = $input[2];
		}
		return array($regionCode,$countryRegion);
	}

	function parse( $content, $widget = false ) {
		$ipAddress = $_SERVER['REMOTE_ADDR'];

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	
		$ipAddress = '1.9.64.1';
		$result = IP2LocationTags::get_location( $ipAddress );

		if ( ! $result ) {
			$content;
		}
		
		//115 : Retrieve ISO3166-2 code from the csv file
		$temp = IP2LocationTags::retrieve_ISO3316();
		$regionCode = $temp[0];
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
			$result['ipAddress'],
			$result['countryCode'],
			$result['countryName'],
			$result['regionName'],
			$result['cityName'],
			$result['latitude'],
			$result['longitude'],
			$result['isp'],
			$result['domainName'],
			$result['zipCode'],
			$result['timeZone'],
			$result['netSpeed'],
			$result['iddCode'],
			$result['areaCode'],
			$result['weatherStationCode'],
			$result['weatherStationName'],
			$result['mcc'],
			$result['mnc'],
			$result['mobileCarrierName'],
			$result['elevation'],
			$result['usageType'],
		);

		// Replace geolocation variables
		$content = str_replace( $find, $replace, $content );

		// Parse IP2Location tags
		do {
			// Get country list from tag
			$data = IP2LocationTags::parse_tag( $content, '&lt;ip:', '&gt;' );

			// Get protected text from tag
			$text = IP2LocationTags::parse_tag( $content, '&lt;ip:' . $data . '&gt;', '&lt;/ip&gt;' );

			// Get the whole tag
			$from = '&lt;ip:' . $data . '&gt;' . $text . '&lt;/ip&gt;';

			$countries = explode( ',', str_replace( ' ', '', strtoupper( $data ) ) );

			$to = '';

			//115 : converting XX:YY code into region name
			$countries = IP2LocationTags::code_to_region($countries,$regionCode,$result['regionName']);
		
			// Show text for listed country
			//115 : added another condition to test for region name
			if ( in_array( $result['countryCode'], $countries ) || in_array( strtoupper($result['regionName']), $countries ) ){
				$to = $text;
			}

			// Show text if wildcard defined
			if ( in_array('*', $countries ) ){
				$to = $text;
			}

			// Hide text for prohibited country
			//115 : added another condition to test for region name
			if ( in_array( '-' . $result['countryCode'], $countries ) || in_array( '-' . strtoupper($result['regionName']), $countries ) ){
				$to = '';
			}

			$content = str_replace( $from, $to, $content );

		} while( !empty( $data ) );

		do{
			// Get country list from tag
			$data2 = IP2LocationTags::parse_tag( $content, '[ip:', ']' );

			// Get protected text from tag
			$text2 = IP2LocationTags::parse_tag( $content, '[ip:' . $data2 . ']', '[/ip]' );

			// Get the whole tag
			$from2 = '[ip:' . $data2 . ']' . $text2 . '[/ip]';
			
			$countries2 = explode( ',', str_replace( ' ', '', strtoupper( $data2 ) ) );
			
			$to2 = '';
			
			//115 : converting XX:YY code into region name
			$countries2 = IP2LocationTags::code_to_region($countries2,$regionCode,$result['regionName']);
			
			// Show text for listed country
			//115 : added another condition to test for region name
			if( in_array( $result['countryCode'], $countries2)|| in_array( strtoupper($result['regionName']), $countries2 ) ){
				$to2 = $text2;
			}

			// Show text if wildcard defined
			if( in_array( '*', $countries2 ) ) {
				$to2 = $text2;
			}

			// Hide text for prohibited country
			//115 : added another condition to test for region name
			if( in_array( '-' . $result['countryCode'], $countries2 ) || in_array( '-' . strtoupper($result['regionName']), $countries2 ) ) {
				$to2 = '';
			}

			$content = str_replace( $from2, $to2, $content );

		} while( !empty( $data2 ) );

		return $content;
	}

	function admin_options() {
		if(is_admin()) {
			add_action('wp_enqueue_script', 'load_jquery');
			echo '
			<style type="text/css">
				.red{color:#cc0000}
				.code{color:#003399;font-family:\'Courier New\'}
				pre{margin:0 0 20px 0;border:1px solid #c0c0c0;backgroumd:#e4e4e4;color:#535353;font-family:\'Courier New\';padding:8px}
				.result{margin:0 0 20px 0;border:1px solid #006699;backgroumd:#99ffcc;color:#000033;padding:8px}
			</style>
			<div class="wrap">
				<h3>IP2LOCATION TAGS</h3>
				<p>
					IP2Location Tags provides a solution to easily get the visitor\'s location information based on IP address and customize the content display for different countries. This plugin uses IP2Location BIN file for location queries, therefore there is no need to set up any relational database to use it. Depending on the BIN file that you are using, this plugin is able to provide you the information of country, region or state, city, latitude and longitude, US ZIP code, time zone, Internet Service Provider (ISP) or company name, domain name, net speed, area code, weather station code, weather station name, mobile country code (MCC), mobile network code (MNC) and carrier brand, elevation and usage type of origin for an IP address.<br/><br/>
				</p>

				<p>&nbsp;</p>';

			if(!file_exists(IP2LOCATION_TAGS_ROOT . 'database.bin')){
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
				//108 : change the fileietime.
				require_once(IP2LOCATION_TAGS_ROOT . 'ip2location.class.php');
				$dbVersion = new IP2Location(IP2LOCATION_TAGS_ROOT . 'database.bin');
				$dbArray = $dbVersion->dbVersion(IP2LOCATION_TAGS_ROOT . 'database.bin');
				$months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
				echo '
				<p>
					<b>Database Version: </b>
					'. $months[$dbArray['month']] . ' ' . $dbArray['year']
				.'</p>';
				if(strtotime($months[$dbArray['month']] . ' ' . $dbArray['year']) < strtotime('-2 months')){
					echo '
					<p class="red">
						<b>Reminder: </b>Your IP2Location database was outdated. Please download the latest version from <a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location commercial database</a> or <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">IP2Location LITE database (free edition)</a>..
					</p>
					<p class="red">
						After downloaded the package, decompress it and rename the .BIN file inside the package to <strong>database.bin</strong>. The, upload the BIN file,<strong>database.bin</strong>, to <em>/wp-content/plugins/ip2location-tags/</em>.
					</p>';
				}
			}
			//108: forms and script for database update and IP Query
			echo '
				<script>
					jQuery(document).ready(function($) {
						// Code here will be executed on document ready. Use $ as normal.
						jQuery("#download").click(function(){
							var product_code = jQuery("#product_code").val();
							var username = jQuery("#username").val();
							var password = jQuery("#password").val();

							//disable the download button
							jQuery("#download").attr("disabled","disabled");
							jQuery("#download_status").html("<div style=\"padding:10px; border:1px solid #ccc; background-color:#ffa;\">Downloading " + product_code + " BIN database in progress... Please wait...</div>");

							var data = {
								\'action\': \'download_db\',
								\'product_code\':product_code.toString(),
								\'username\':username.toString(),
								\'password\':password.toString()
							};

							$.post(ajaxurl, data, function(result) {
								if (result == "SUCCESS"){
									alert("Downloading completed.");
									jQuery("#download_status").html("<div style=\"padding:10px; border:1px solid #0f0; background-color:#afa;\">Successfully downloaded the " + product_code + " BIN database. Please refresh information by reloading the page.</div>");
								}
								else{
									alert("Downloading failed");
									jQuery("#download_status").html("<div style=\"padding:10px; border:1px solid #f00; background-color:#faa;\">Failed to download " + product_code + " BIN database. Please make sure you correctly enter the product code and login crendential. Please also take note to download the BIN product code only.</a>");
								}
							}).always(function() {
								//clear the entry
								jQuery("#product_code").val("");
								jQuery("#username").val("");
								jQuery("#password").val("");
								jQuery("#download").removeAttr("disabled");
							});
						});
					});
					//jQuery script for getting region list when click 
					jQuery(document).ready(function($) {
						jQuery("#region").click(function(){
							var country_code = jQuery("#region").val();
							var data = {
									\'action\': \'region_list\',
									\'country_code\' :country_code.toString()
							};
							$.post(ajaxurl,data,function(print){
								jQuery("#regionlist").html(print);
							});
						});
					});
				</script>
				<div style="margin-top:10px; padding:10px; border:1px solid #ccc;">
					<span style="display:block; font-weight:bold; margin-bottom:5px;">Download BIN Database</span>
					Product Code: <select id="product_code" type="text" value="" style="margin-right:10px;" >
						<option value="DB1LITEBIN">DB1LITEBIN</option>
						<option value="DB3LITEBIN">DB3LITEBIN</option>
						<option value="DB5LITEBIN">DB5LITEBIN</option>
						<option value="DB9LITEBIN">DB9LITEBIN</option>
						<option value="DB11LITEBIN">DB11LITEBIN</option>
						<option value="DB1BIN">DB1BIN</option>
						<option value="DB2BIN">DB2BIN</option>
						<option value="DB3BIN">DB3BIN</option>
						<option value="DB4BIN">DB4BIN</option>
						<option value="DB5BIN">DB5BIN</option>
						<option value="DB6BIN">DB6BIN</option>
						<option value="DB7BIN">DB7BIN</option>
						<option value="DB8BIN">DB8BIN</option>
						<option value="DB9BIN">DB9BIN</option>
						<option value="DB10BIN">DB10BIN</option>
						<option value="DB11BIN">DB11BIN</option>
						<option value="DB1LITEBINIPV6">DB1LITEBINIPV6</option>
						<option value="DB3LITEBINIPV6">DB3LITEBINIPV6</option>
						<option value="DB5LITEBINIPV6">DB5LITEBINIPV6</option>
						<option value="DB9LITEBINIPV6">DB9LITEBINIPV6</option>
						<option value="DB11LITEBINIPV6">DB11LITEBINIPV6</option>
						<option value="DB1BINIPV6">DB1BINIPV6</option>
						<option value="DB2BINIPV6">DB2BINIPV6</option>
						<option value="DB3BINIPV6">DB3BINIPV6</option>
						<option value="DB4BINIPV6">DB4BINIPV6</option>
						<option value="DB5BINIPV6">DB5BINIPV6</option>
						<option value="DB6BINIPV6">DB6BINIPV6</option>
						<option value="DB7BINIPV6">DB7BINIPV6</option>
						<option value="DB8BINIPV6">DB8BINIPV6</option>
						<option value="DB9BINIPV6">DB9BINIPV6</option>
						<option value="DB10BINIPV6">DB10BINIPV6</option>
						<option value="DB11BINIPV6">DB11BINIPV6</option>
					</select>
					Email: <input id="username" type="text" value="" style="margin-right:10px;" />
					Password: <input id="password" type="password" value="" style="margin-right:10px;" /> <button id="download">Download</button>
					<input id="site_url" type="hidden" value="' . get_site_url() . '" />
					<span style="display:block; font-size:0.8em">Enter the product code, i.e, DB1LITEBIN, (the code in square bracket on your license page) and login credential for the download.</span>

					<div style="margin-top:20px;">
						Note: If you failed to download the BIN database using this automated downloading tool, please follow the below procedures to manually update the database.
						<ol style="list-style-type:circle;margin-left:30px">
							<li>Download the BIN database at <a href="http://www.ip2location.com/?r=wordpress" target="_blank">IP2Location commercial database</a> | <a href="http://lite.ip2location.com/?r=wordpress" target="_blank">IP2Location LITE database (free edition)</a>.</li>
							<li>Decompress the zip file and rename the BIN database to <b>database.bin</b>.</li>
							<li>Upload <b>database.bin</b> to /wp-content/plugins/ip2location-tags/.</li>
							<li>Once completed, please refresh the information by reloading the  page.</li>
						</ol>
					</div>
				</div>
				<div id="download_status" style="margin:10px 0;">

				</div>
			';

			echo '
				<p>&nbsp;</p>
				<a name="ip-query"></a>
				<div style="border-bottom:1px solid #ccc;">
					<h3>Query IP</h3>
				</div>
				<p>
					Enter a valid IP address for checking.
				</p>';
			$ipAddr = (isset($_POST['ipAddr'])) ? $_POST['ipAddr'] : '';
			if(isset($_POST['lookup'])) {

				if(!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
					echo '<p style="color:#cc0000">Invalid IP address.</p>';
				}
				else {
					$LocResult = query_ip($ipAddr);
					echo '<p style="color:#666600">IP address <b>' . $ipAddr . '</b> belongs to <b>' . $LocResult['countryName'] . '</b>.</p>';
				}
			}
			echo '
				<form action="#ip-query" method="post">
					<p>
						<label><b>IP Address: </b></label>
						<input type="text" name="ipAddr" value="' . $ipAddr . '" />
						<input type="submit" name="lookup" value="Lookup" />
					</p>
				</form>

			<p>&nbsp;</p>
			';

			//115 : Display list of subdivision code according to country code
			$temp = IP2LocationTags::retrieve_ISO3316();
			$countryRegion = $temp[1];
			echo '<a name="country_region"></a>
				<div style="border-bottom:1px solid #ccc;">
					<h3>Subdivision Code List</h3>
				</div>
				<p>
					Select a country code for subdivision code list
				</p>';
			if(!file_exists(IP2LOCATION_TAGS_ROOT . "IP2LOCATION-ISO3166-2.CSV")){
				echo '<p class="red">
					IP2Location ISO3166-2 CSV file not found. Please download the CSV file at the following link:
					<a href="http://www.ip2location.com/free/iso3166-2" target="_blank">ISO 3166-2 Subdivision Code</a>.
					<ol class="red" style="list-style-type:circle;margin-left:30px;">
							<li>Download the zip file.</li>
							<li>Decompress the zip file.</li>
							<li>Upload <b>IP2LOCATION-ISO3166-2.CSV</b> to /wp-content/plugins/ip2location-tags/.</li>
							<li>Once completed, please refresh the information by reloading the page.</li>
					</ol>
				</p>';
			}
			echo '
				<form action="#country_region" method="post">
					<select id="region" type="text" size="10" style="height:200px;" >
						<option value="" selected="disabled">Choose a country Code</option>';
			foreach($countryRegion as $key =>$value){
				echo '<option value='.$key;
				echo '>'.$key.'</option>';
			}
			echo '</select>
				<select id="regionlist" size="10" style="height:200px;">
					<option>Country Subdivision Code List</option>
				</select>
				</form>
				<p>
					<strong>Example</strong><br/>
					The subdivision code for California, United States will be US:CA.<br/>
				</p>
			';
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
					<pre>&#91;ip:XX[,XX]..[,XX]&#93;You content here.&#91;/ip&#93;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					To show the content for United States or Canada visitors only.<br/>
					<pre>&#91;ip:US,CA&#93;Only visitors from United States or Canada can view this line.&#91;/ip&#93;</pre>
				</p>
				<p>&nbsp;</p>
				<p>
					<h4>Syntax to show content for specific country and region</h4>
					<pre>&#91;ip:XX:YY[,XX:YY]..[,XX:YY]&#93;You content here.&#91;/ip&#93;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code and YY is a ISO-3166-2 sub division code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					To show the content for California or New York visitors only.<br/>
					<pre>&#91;ip:US:CA,US:NY&#93;Only visitors from California or New York can view this line.&#91;/ip&#93;</pre>
				</p>
				<p>&nbsp;</p>

				<p>
					<h4>Syntax to hide the content from specific country</h4>
					<pre>&#91;ip:*,-XX[,-XX]..[,-XX]&#93;You content here.&#91;/ip&#93;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					All visitors will be able to see the line except visitors from Vietnam.</br>
					<pre>&#91;ip:*,-VN&#93;All visitors will be able to see this line except visitors from Vietnam.&#91;/ip&#93;</pre>
				</p>

				<p>&nbsp;</p>

				<p>
					<h4>Syntax to hide the content from specific country and region</h4>
					<pre>&#91;ip:*,-XX:YY[,-XX:YY]..[,-XX:YY]&#93;You content here.&#91;/ip&#93;</pre>
					<div class="red">Note: XX is a two-digit ISO-3166 country code and YY is a ISO-3166-2 sub division code.</div>
				</p>
				<p>
					<strong>Example</strong><br/>
					All visitors will be able to see the line except visitors from California.</br>
					<pre>&#91;ip:*,-US:CA&#93;All visitors will be able to see this line except visitors from California.&#91;/ip&#93;</pre>
				</p>

				<p>&nbsp;</p>

				<h3>References</h3>

				<p>Please visit <a href="http://www.ip2location.com/free/country-multilingual" target="_blank">http://www.ip2location.com</a> for ISO country codes and names supported.</p>';
				//HJ modified - END
		}
	}
	//108: to enqueue the jquery
	function load_jquery() {
		wp_enqueue_script('jquery');
	}

	function admin_page() {
		add_options_page( 'IP2Location Tags', 'IP2Location Tags', 8, 'ip2location-tags', array( 'IP2LocationTags', 'admin_options' ) );
	}

	function set_defaults() {
		update_option( 'ip2location_tags_database', '' );

		// Find any .BIN files in current directory
		$files = scandir( IP2LOCATION_TAGS_ROOT );

		foreach( $files as $file ){
			if ( substr( $file, -4 ) == '.bin' || substr( $file, -4 ) == '.BIN' ){
				update_option( 'ip2location_tags_database', $file );
				break;
			}
		}
	}

	function uninstall() {
		// Remove all settings
		delete_option( 'ip2location_tags_database' );
	}

	function start() {
		add_action( 'admin_menu', array( 'IP2LocationTags', 'admin_page' ) );
		add_filter( 'the_content', array( 'IP2LocationTags', 'parse' ) );
		add_filter( 'widget_text', array( 'IP2LocationTags', 'parse_widget' ) );
	}

	function set_case( $s ) {
		$s = ucwords( strtolower( $s ) );
		$s = preg_replace_callback( "/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s", create_function( '$matches','return $matches[1].strtoupper($matches[2]);' ),$s );

		return $s;
	}
}

//108 : function to download or update db
function ip2location_tags_download_db() {
	try {
		$product_code = $_POST['product_code'];
		$username = $_POST['username'];
		$password = $_POST['password'];

		if(!class_exists('WP_Http'))
			include_once(ABSPATH . WPINC . '/class-http.php');

		$request = new WP_Http ();
		$result = $request->request ("http://www.ip2location.com/download?productcode=" . strtoupper($product_code) . "&login=" . rawurlencode($username) . "&password=" . rawurlencode($password), array('timeout' => 120));

		if ((isset ($result->errors)) || (! (in_array ('200', $result ['response'])))) die('ERROR');

		$fp = fopen (WP_PLUGIN_DIR . "/" . dirname (plugin_basename (__FILE__)) . "/database.zip", "w");
		fwrite ($fp, $result['body']);
		fclose ($fp);
		// unzip the file
		$zip = zip_open(WP_PLUGIN_DIR . "/" . dirname (plugin_basename (__FILE__)) . "/database.zip");
		// Make sure it is a ZIP resource
		if (is_resource($zip)) {
			$found = false;
			while($zip_entry = zip_read($zip)) {
				// Extract the BIN file only
				$zip_name = zip_entry_name($zip_entry);
				$pos = strpos(strtoupper($zip_name), '.BIN');
				if ($pos !== false) {
					$file_size = zip_entry_filesize($zip_entry);
					$whandle = fopen(WP_PLUGIN_DIR . "/" . dirname (plugin_basename (__FILE__)) . "/database.bin", 'w+');
					fwrite($whandle, zip_entry_read($zip_entry, $file_size));
					fclose($whandle);

					//remove the default sample file upon successfully download the latest copy.
					if (file_exists(DEFAULT_SAMPLE_BIN))
						unlink(DEFAULT_SAMPLE_BIN);

					// success
					$found = true;
				}
			}
			// Only report true upon success unzip
			if ($found)
				echo "SUCCESS";
			else
				echo "ERROR";

			@unlink(WP_PLUGIN_DIR . "/" . dirname (plugin_basename (__FILE__)) . "/database.zip");
		}else
			echo "ERROR";
	}
	catch (Exception $e) {
		echo 'ERROR' . $e . getMessage();
	}

	die;
}

//115 : function to display list region list.
function ip2location_tags_region_list() {
	try{
		$country_code = $_POST['country_code'];
		$temp = IP2LocationTags::retrieve_ISO3316();
		$regionCode = $temp[0];
		$countryRegion = $temp[1];
		if($country_code == ""){
				echo '<option>Country Subdivision Code List</option>';
		}
		else{
			sort($countryRegion[$country_code]);
			foreach($countryRegion[$country_code] as $value){
				echo '<option> ' . $value . '&nbsp&nbsp&nbsp=>&nbsp&nbsp&nbsp' . $regionCode[$value]. '</option>';
			}
		}
	}
	catch(Exception $e){
		echo 'ERROR';
	}

	die;
}

//108 : get_location for ip_query.
function query_ip($ip) {
	// Make sure IP2Location database is exist
	if(!file_exists(IP2LOCATION_TAGS_ROOT . 'database.bin')) return false;

	if ( ! class_exists( 'IP2LocationRecord' ) && ! class_exists( 'IP2Location' ) ) {
		require_once( IP2LOCATION_TAGS_ROOT . 'ip2location.class.php' );
	}

	// Create IP2Location object
	$geo = new IP2Location(IP2LOCATION_TAGS_ROOT . 'database.bin');

	// Get geolocation by IP address
	$LocResult = $geo->lookup($ip);

	return array('countryCode' => $LocResult->countryCode,'countryName' => $LocResult->countryName);
}

// Initial class
$ip2location_tags = new IP2LocationTags();
$ip2location_tags->start();

register_activation_hook( __FILE__, array( 'IP2LocationTags', 'set_defaults' ) );
register_uninstall_hook( __FILE__, array( 'IP2LocationTags', 'uninstall' ) );

add_action( 'wp_ajax_download_db', 'ip2location_tags_download_db' );
//include ip2location_tags_region_list in ajax
add_action( 'wp_ajax_region_list', 'ip2location_tags_region_list' );
?>