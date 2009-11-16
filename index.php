<?php

/*
Plugin Name: IP2Location Tag
Plugin URI: http://www.ip2location.com/developer.aspx
Description: Enable you to use IP2Location tags to customize your post content by country.
Version: 1.00
Author: IP2Location
Author URI: http://www.ip2location.com
*/

define('DS', DIRECTORY_SEPARATOR);
define('_ROOT', dirname(__FILE__) . DS);

class IP2LocationTag{
	var $countryShort;
	var $countryLong;
	var $region;
	var $city;
	var $isp;
	var $latitude;
	var $longitude;
	var $domain;
	var $zipCode;
	var $timeZone;
	var $netSpeed;
	var $iddCode;
	var $areaCode;
	var $weatherStationCode;
	var $weatherStationName;
	var $ipAddress;
	var $ipNumber;

	function getLocation() {
		global $cbc_visitor_location;
		if(!is_admin()) {
			// Add required IP2Location class
			require_once(_ROOT .  'database' . DS . 'ip2location.class.php');

			// Create IP2Location object
			$ip2loc = new IP2Location();

			// Open IP2Location BIN database
			$ip2loc->open(_ROOT . 'database' . DS . 'ip2location.bin');

			// Get geolocation information based on client IP
			$record = $ip2loc->getAll($this->getIP());

			// Assign the result
			$this->ipAddress = (preg_match('/not supported/', $record->ipAddress)) ? 'N/A' : $record->ipAddress;
			$this->ipNumber = (preg_match('/not supported/', $record->ipNumber)) ? 'N/A' : $record->ipNumber;
			$this->countryShort = (preg_match('/not supported/', $record->countryShort)) ? 'N/A' : $record->countryShort;
			$this->countryLong = (preg_match('/not supported/', $record->countryLong)) ? 'N/A' : $record->countryLong;
			$this->region = (preg_match('/not supported/', $record->region)) ? 'N/A' : $record->region;
			$this->city = (preg_match('/not supported/', $record->city)) ? 'N/A' : $record->city;
			$this->isp = (preg_match('/not supported/', $record->isp)) ? 'N/A' : $record->isp;
			$this->latitude = (preg_match('/not supported/', $record->latitude)) ? 'N/A' : $record->latitude;
			$this->longitude = (preg_match('/not supported/', $record->longitude)) ? 'N/A' : $record->longitude;
			$this->domain = (preg_match('/not supported/', $record->domain)) ? 'N/A' : $record->domain;
			$this->zipCode = (preg_match('/not supported/', $record->zipCode)) ? 'N/A' : $record->zipCode;
			$this->timeZone = (preg_match('/not supported/', $record->timeZone)) ? 'N/A' : $record->timeZone;
			$this->netSpeed = (preg_match('/not supported/', $record->netSpeed)) ? 'N/A' : $record->netSpeed;
			$this->iddCode = (preg_match('/not supported/', $record->iddCode)) ? 'N/A' : $record->iddCode;
			$this->areaCode = (preg_match('/not supported/', $record->areaCode)) ? 'N/A' : $record->areaCode;
			$this->weatherStationCode = (preg_match('/not supported/', $record->weatherStationCode)) ? 'N/A' : $record->weatherStationCode;
			$this->areaCode = (preg_match('/not supported/', $record->areaCode)) ? 'N/A' : $record->areaCode;
			return true;
		}
		return false;
	}

	function getIP(){
		$ipAddress[] = $_SERVER['REMOTE_ADDR'];

		if(isset($_SERVER['HTTP_CLIENT_IP'])) $ip = trim($_SERVER['HTTP_CLIENT_IP']);
		
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = trim($_SERVER['HTTP_X_FORWARDED_FOR']);

			if(preg_match('/,/', $ip)){
				$tmp = explode(',', $ip);
				foreach($tmp as $t) $ipAddresss[] = trim($t);
			}
			else{
				$ipAddress[] = $ip;
			}
		}

		for($i=(sizeof($ipAddress)-1); $i>=0; $i--){
			if((long2ip(ip2long($ipAddress[$i])) == $ipAddress[$i])){
				return $ipAddress[$i];
			}
			return $ipAddress[0];
		}
	}

	function parseTag($string, $start, $end) {
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if($ini == 0) return '';
		$ini += strlen($start);   
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}

	function parse($content) {
		if(!empty($this->countryShort)) {
			$this->countryShort = strtolower($this->countryShort);
			$finish = 1;
			for($z=1; $z <= $finish; $z++) {
				$loc = $this->parseTag($content, '[ip2loc', ']');
				if(!empty($loc)) {
					$find = array('[ip2loc:ip]', '[ip2loc:countryCode]', '[ip2loc:country]', '[ip2loc:region]', '[ip2loc:city]', '[ip2loc:latitude]', '[ip2loc:longitude]', '[ip2loc:isp]');
					$replace = array($this->ipAddress, $this->countryShort, $this->countryLong, $this->region, $this->city, $this->latitude, $this->longitude, $this->isp);

					$content = str_replace($find, $replace, $content);

					$text = $this->parseTag($content, '[ip2loc' . $loc . ']', '[/ip2loc]');

					$from = '[ip2loc' . $loc . ']' . $text . '[/ip2loc]';
					if(strpos($loc, ':'.$this->countryShort) !== false) {
						$to = $text;
					} elseif(strpos($loc, ';' . $this->countryShort) !== false) {
						$to = $text;
					} elseif(strpos($loc, ':-' . $this->countryShort) !== false) {
						$to = '';
					} elseif(strpos($loc, ';-') !== false) {
						$to = $text;
					} else {
						$to = '';
					}
					$content = str_replace($from, $to, $content);
					$finish++;
				}
			}
			$content = str_replace("<p><br>\n", "<p>", $content);
			$content = str_replace("<p></p>\n", "", $content);
		}
		return $content;
	}

	function admin_options() {
		if(is_admin()) {
			$arrCountry = array(
				'AF'=>'AFGHANISTAN',
				'AL'=>'ALBANIA',
				'DZ'=>'ALGERIA',
				'AS'=>'AMERICAN SAMOA',
				'AD'=>'ANDORRA',
				'AO'=>'ANGOLA',
				'AI'=>'ANGUILLA',
				'AG'=>'ANTIGUA AND BARBUDA',
				'AR'=>'ARGENTINA',
				'AM'=>'ARMENIA',
				'AU'=>'AUSTRALIA',
				'AT'=>'AUSTRIA',
				'AZ'=>'AZERBAIJAN',
				'BS'=>'BAHAMAS',
				'BH'=>'BAHRAIN',
				'BD'=>'BANGLADESH',
				'BB'=>'BARBADOS',
				'BY'=>'BELARUS',
				'BE'=>'BELGIUM',
				'BZ'=>'BELIZE',
				'BJ'=>'BENIN',
				'BM'=>'BERMUDA',
				'BO'=>'BOLIVIA',
				'BA'=>'BOSNIA AND HERZEGOVINA',
				'BW'=>'BOTSWANA',
				'BR'=>'BRAZIL',
				'BN'=>'BRUNEI DARUSSALAM',
				'BG'=>'BULGARIA',
				'BF'=>'BURKINA FASO',
				'KH'=>'CAMBODIA',
				'CM'=>'CAMEROON',
				'CA'=>'CANADA',
				'CV'=>'CAPE VERDE',
				'KY'=>'CAYMAN ISLANDS',
				'CF'=>'CENTRAL AFRICAN REPUBLIC',
				'CL'=>'CHILE',
				'CN'=>'CHINA',
				'CO'=>'COLOMBIA',
				'CG'=>'CONGO',
				'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
				'CK'=>'COOK ISLANDS',
				'CR'=>'COSTA RICA',
				'CI'=>'COTE D\'IVOIRE',
				'HR'=>'CROATIA',
				'CU'=>'CUBA',
				'CY'=>'CYPRUS',
				'CZ'=>'CZECH REPUBLIC',
				'DK'=>'DENMARK',
				'DM'=>'DOMINICA',
				'DO'=>'DOMINICAN REPUBLIC',
				'EC'=>'ECUADOR',
				'EG'=>'EGYPT',
				'SV'=>'EL SALVADOR',
				'ER'=>'ERITREA',
				'EE'=>'ESTONIA',
				'ET'=>'ETHIOPIA',
				'FO'=>'FAROE ISLANDS',
				'FI'=>'FINLAND',
				'FR'=>'FRANCE',
				'PF'=>'FRENCH POLYNESIA',
				'GA'=>'GABON',
				'GM'=>'GAMBIA',
				'DE'=>'GERMANY',
				'GH'=>'GHANA',
				'GR'=>'GREECE',
				'GL'=>'GREENLAND',
				'GD'=>'GRENADA',
				'GU'=>'GUAM',
				'GT'=>'GUATEMALA',
				'GN'=>'GUINEA',
				'GY'=>'GUYANA',
				'HT'=>'HAITI',
				'VA'=>'HOLY SEE (VATICAN CITY STATE)',
				'HN'=>'HONDURAS',
				'HK'=>'HONG KONG',
				'HU'=>'HUNGARY',
				'IS'=>'ICELAND',
				'IN'=>'INDIA',
				'ID'=>'INDONESIA',
				'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
				'IQ'=>'IRAQ',
				'IE'=>'IRELAND',
				'IL'=>'ISRAEL',
				'IT'=>'ITALY',
				'JM'=>'JAMAICA',
				'JP'=>'JAPAN',
				'JO'=>'JORDAN',
				'KZ'=>'KAZAKHSTAN',
				'KE'=>'KENYA',
				'KR'=>'KOREA, REPUBLIC OF',
				'KW'=>'KUWAIT',
				'KG'=>'KYRGYZSTAN',
				'LA'=>'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
				'LV'=>'LATVIA',
				'LB'=>'LEBANON',
				'LS'=>'LESOTHO',
				'LR'=>'LIBERIA',
				'LY'=>'LIBYAN ARAB JAMAHIRIYA',
				'LI'=>'LIECHTENSTEIN',
				'LT'=>'LITHUANIA',
				'LU'=>'LUXEMBOURG',
				'MO'=>'MACAO',
				'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
				'MG'=>'MADAGASCAR',
				'MW'=>'MALAWI',
				'MY'=>'MALAYSIA',
				'MV'=>'MALDIVES',
				'ML'=>'MALI',
				'MT'=>'MALTA',
				'MH'=>'MARSHALL ISLANDS',
				'MU'=>'MAURITIUS',
				'MX'=>'MEXICO',
				'FM'=>'MICRONESIA, FEDERATED STATES OF',
				'MD'=>'MOLDOVA, REPUBLIC OF',
				'MC'=>'MONACO',
				'MN'=>'MONGOLIA',
				'MS'=>'MONTSERRAT',
				'MA'=>'MOROCCO',
				'MZ'=>'MOZAMBIQUE',
				'MM'=>'MYANMAR',
				'NA'=>'NAMIBIA',
				'NP'=>'NEPAL',
				'NL'=>'NETHERLANDS',
				'AN'=>'NETHERLANDS ANTILLES',
				'NC'=>'NEW CALEDONIA',
				'NZ'=>'NEW ZEALAND',
				'NI'=>'NICARAGUA',
				'NE'=>'NIGER',
				'NG'=>'NIGERIA',
				'NF'=>'NORFOLK ISLAND',
				'MP'=>'NORTHERN MARIANA ISLANDS',
				'NO'=>'NORWAY',
				'OM'=>'OMAN',
				'PK'=>'PAKISTAN',
				'PA'=>'PANAMA',
				'PG'=>'PAPUA NEW GUINEA',
				'PY'=>'PARAGUAY',
				'PE'=>'PERU',
				'PH'=>'PHILIPPINES',
				'PL'=>'POLAND',
				'PT'=>'PORTUGAL',
				'PR'=>'PUERTO RICO',
				'QA'=>'QATAR',
				'RO'=>'ROMANIA',
				'RU'=>'RUSSIAN FEDERATION',
				'RW'=>'RWANDA',
				'KN'=>'SAINT KITTS AND NEVIS',
				'LC'=>'SAINT LUCIA',
				'PM'=>'SAINT PIERRE AND MIQUELON',
				'VC'=>'SAINT VINCENT AND THE GRENADINES',
				'SM'=>'SAN MARINO',
				'SA'=>'SAUDI ARABIA',
				'SN'=>'SENEGAL',
				'SC'=>'SEYCHELLES',
				'SL'=>'SIERRA LEONE',
				'SG'=>'SINGAPORE',
				'SK'=>'SLOVAKIA',
				'SI'=>'SLOVENIA',
				'SB'=>'SOLOMON ISLANDS',
				'ZA'=>'SOUTH AFRICA',
				'ES'=>'SPAIN',
				'LK'=>'SRI LANKA',
				'SD'=>'SUDAN',
				'SR'=>'SURINAME',
				'SZ'=>'SWAZILAND',
				'SE'=>'SWEDEN',
				'CH'=>'SWITZERLAND',
				'SY'=>'SYRIAN ARAB REPUBLIC',
				'TW'=>'TAIWAN',
				'TJ'=>'TAJIKISTAN',
				'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
				'TH'=>'THAILAND',
				'TG'=>'TOGO',
				'TO'=>'TONGA',
				'TT'=>'TRINIDAD AND TOBAGO',
				'TN'=>'TUNISIA',
				'TR'=>'TURKEY',
				'TM'=>'TURKMENISTAN',
				'UG'=>'UGANDA',
				'UA'=>'UKRAINE',
				'AE'=>'UNITED ARAB EMIRATES',
				'UK'=>'UNITED KINGDOM',
				'US'=>'UNITED STATES',
				'UY'=>'URUGUAY',
				'UZ'=>'UZBEKISTAN',
				'VE'=>'VENEZUELA',
				'VN'=>'VIET NAM',
				'VI'=>'VIRGIN ISLANDS, U.S.',
				'YE'=>'YEMEN',
				'ZM'=>'ZAMBIA',
				'ZW'=>'ZIMBABWE'
			);
			echo '<style type="text/css">
			.example {color:#675D1C;border: 1px solid #FFCC66;background:#FFFFCC;padding:10px;overflow: visible;}
			.country_table{table-layout: fixed;font-size:11px;border:1px solid #3366FF;}
			.country_table thead tr{background:#003399;height:25px;}
			.country_table thead td{padding-left:4px;color:#F3F6F7;font-weight:bold;}
			.country_table thead td a{padding-left:4px;color:#F3F6F7;font-weight:bold;}
			.country_table tbody tr{background:none;height:25px;border-bottom:solid 1px #8994A0;}
			.country_table tbody tr:hover{background:#D8E2F5;}
			.country_table tbody td{padding-left:4px;}
			</style>
			<div class="wrap">
			<h2>IP2Location Tag</h2>
			Use IP2Location Tag to customize your blog content. You can hide your blog content for visitors from some countries, or show only to visitor from some countries.
			<br><br><br>
			<b>Available Tags:</b>
			<br><br>
			<i>[ip2loc:XX]your_text_here[/ip2loc]</i><br>
			<i>[ip2loc:-XX]your_text_here[/ip2loc]</i><br>
			<i>[ip2loc:ip]</i><br>
			<i>[ip2loc:countryCode]</i><br>
			<i>[ip2loc:country]</i><br>
			<i>[ip2loc:region]</i><font color="red">*</font><br>
			<i>[ip2loc:city]</i><font color="red">*</font><br>
			<i>[ip2loc:latitude]</i><font color="red">*</font><br>
			<i>[ip2loc:longitude]</i><font color="red">*</font><br>
			<i>[ip2loc:isp]</i><font color="red">*</font>
			<br><Br>
			<font color="red">*</font> You will need to purchase a IP2Location database to use these tags. For more information, please visit <a href="http://www.ip2location.com/" target="_blank">http://www.ip2location.com/</a>.
			<br><br><br>
			<b>Usage Examples:</b>
			<br><br>
			<div class="example"><b>Show text only for visitors from the list</b><br>' .
				htmlentities('[ip2loc:us;jp;gb;ca]Only visitors from United States, Japan, United Kingdom and Canada can view this text.[/ip2loc]') . '
			</div>
			<br>
			<div class="example"><b>Hide text for visitors from the list</b><br>' .
			htmlentities('[ip2loc:-ng;-vn]Visitors from Nigeria and Viet Nam cannot view this text.[/ip2loc]') . '
			</div>
			<br>
			<div class="example"><b>Display visitor IP address</b><br><i>' .
				htmlentities('Your IP address is [ip2loc:ip].') . '</i>
				<br>
				Result: Your IP address is 74.125.45.100.
			</div>
			<br>
			<div class="example"><b>Display country code</b><br><i>' .
				htmlentities('Your country code is [ip2loc:countryCode].') . '</i>
				<br>
				Result: Your country code is US.
			</div>
			<br>
			<div class="example"><b>Display visitor location</b><br><i>' .
				htmlentities('Welcome, visitor from [ip2loc:city], [ip2loc:region], [ip2loc:country].') . '</i>
				<br>
				Result: Welcome, visitor from MOUNTAIN VIEW, CALIFORNIA, UNITED STATES.
			</div>
			<br>
			<br><br>
			<b>Country Code:</b>
			<br><br>
			<table>
			<tr valign="top">
				<td>

				<table cellspacing="0" class="country_table">
				<thead>
				<tr>
					<td width="60" align="center"><b>Code</b></td>
					<td width="200"><b>Country Name</b></td>
				</tr>
				</thead>
				<tbody>';

				$index = 0;
				foreach($arrCountry as $key=>$value){
					if($index >= 96) break;
					echo '<tr>
						<td width="60" align="center">' . $key . '</td>
						<td width="200">' . $value . '</td>
					</tr>';
					$index++;
				}
				echo '</tbody>
				</table>

				</td>
				<td>&nbsp;</td>
				<td>

				<table cellspacing="0" class="country_table">
				<thead>
				<tr>
					<td width="60" align="center"><b>Code</b></td>
					<td width="200"><b>Country Name</b></td>
				</tr>
				</thead>
				<tbody>';

				$index = 0;
				foreach($arrCountry as $key=>$value){
					if($index >= 96){
						echo '<tr>
							<td width="60" align="center">' . $key . '</td>
							<td width="200">' . $value . '</td>
						</tr>';
					}
					$index++;
				}
				echo '</tbody>
				</table>

				</td>
			</tr>
			</table>';
		}
	}

	function admin_page() {
		add_management_page('IP2Location Tag', 'IP2Location Tag', 8, 'ip2location-tag', array(&$this, 'admin_options'));
	}

	function activate() {
		header("Location: edit.php?page=ip2location-content");
		exit();
	}
	
	function init() {
		add_action('wp', array(&$this, 'getLocation'), 101);
		add_action('admin_menu', array(&$this, 'admin_page'));
		add_filter('the_content', array(&$this, 'parse'));
	}
	
}

//init class
$ip2loc = new IP2LocationTag;
$ip2loc->init();

//activation
register_activation_hook(__FILE__, array(&$cbc, 'activate'));

?>