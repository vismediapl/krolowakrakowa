<?php
if (isset ($DBID) && !function_exists ('geoip_record_by_name') && is_file ($DataDir.'geoip_'.$DBID.'.sqlite') && function_exists ('sqlite_open')) {
function geoip_record_by_name ($IP) {
	$DBLink = sqlite_popen ($GLOBALS['DataDir'].'geoip_'.$GLOBALS['DBID'].'.sqlite');
	if (!$DBLink) return (0);
	$IP = explode ('.', $IP);
	$IP = ((16777216 * $IP[0]) + (65536 * $IP[1]) + (256 * $IP[2]) + $IP[3]);
	$Result = sqlite_array_query ($DBLink, 'SELECT "l"."city" AS "city", "l"."region" AS "region", "l"."country_code" AS "country_code", "l"."latitude" AS "latitude", "l"."longitude" AS "longitude" FROM "blocks" "b", "locations" "l" WHERE '.$IP.' BETWEEN "b"."ipstart" AND "b"."ipend" AND "b"."location" = "l"."location"', SQLITE_ASSOC);
	return (isset ($Result[0])?$Result[0]:0);
	}
}
function e_geo_info_available () {
	return (function_exists ('geoip_record_by_name'));
	}
function e_geo_init () {
	$GLOBALS['CountryToContinent'] = e_data_load ('country-to-continent');
	$GLOBALS['CountryToContinent']['no'] = $GLOBALS['CountryToContinent']['\no'];
	unset ($GLOBALS['CountryToContinent']['\no']);
	$GLOBALS['Continents'] = e_data_load ('continents');
	$GLOBALS['RegionsCorrections'] = e_data_load ('regions-corrections');
	}
function e_geo_info ($IP) {
	if ($IP == '127.0.0.1' || !function_exists ('geoip_record_by_name')) return (0);
	$Data = @geoip_record_by_name ($IP);
	if (!$Data) return (0);
	$Data['country_code'] = strtolower ($Data['country_code']);
	$Data['region'] = (int) $Data['region'];
	if (isset ($GLOBALS['RegionsCorrections'][$Data['country_code']][$Data['region']])) $Data['region'] = $GLOBALS['RegionsCorrections'][$Data['country_code']][$Data['region']];
	return (array (
	'city' => $Data['city'],
	'region' => $Data['region'],
	'country' => $Data['country_code'],
	'continent' => $GLOBALS['CountryToContinent'][$Data['country_code']],
	'latitude' => $Data['latitude'],
	'longitude' => $Data['longitude'],
	));
	}
function e_coordinates ($Latitude, $Longitude) {
	$LatitudeSuffix = (($Latitude < 0)?'S':'N');
	$LongitudeSuffix = (($Longitude < 0)?'W':'E');
	if ($Latitude < 0) $Latitude = ($Latitude * -1);
	if ($Longitude < 0) $Longitude = ($Longitude * -1);
	return (round ($Latitude, 2).'&#176; '.$LatitudeSuffix.' '.round ($Longitude, 2).'&#176; '.$LongitudeSuffix);
	}
?>