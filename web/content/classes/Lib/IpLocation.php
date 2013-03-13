<?php
// =======================================================================
//     Class to return Information, given an IP Address
//     by Michael V. Petrovich 2010-06-26
//     Uses free server at: www.ipgp.net

// =======================================================================

/*
NOTE: return XML
<IpLookup>
<Ip>IP_ADDRESS</Ip>
<Code>COUNTRY_CODE</Code>
<Country>COUNTRY</Country>
<Flag>http://www.ipgp.net/flags/COUNTRY_CODE.png</Flag>
<City>CITY</City>
<Region>REGION</Region>
<Isp>INTERNET SERVICE PROVIDER</Isp>
<Lat>LATITUDE</Lat>
<Lng>LONGITUDE</Lng>
</IpLookup>
*/


//     call: Lib_IpLocation::GetLocationRecord($ip)
//     or Lib_IpLocation::GetLocationRecord() to use remote address
class Lib_IpLocation
{
    public static function GetLocationRecord($ip = '') {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($ip) {
            $cookie_name = 'IP_LOCATION_' . $ip;
            if (isset($_COOKIE[$cookie_name])) {
                return unserialize(base64_decode($_COOKIE[$cookie_name]));
            }

            $fields = array(
                'Ip' =>  'ip',
                'Code' => 'country_code',
                'Country' => 'country_name',
                'Flag'   => 'flag_image_url',
                'City'   => 'city',
                'Region' => 'state_region',
                'Isp' => 'isp',
                'Lat' => 'lattitude',
                'Lng' => 'longitute'
            );

            $xml = file_get_contents('http://www.ipgp.net/api/xml/' . $ip);
            $RESULT = array();
            foreach ($fields as $xml_key => $array_key) {
                $RESULT[$array_key] = preg_replace('/^(.|\n)*<' . $xml_key . '>|<\/' . $xml_key . '>(.|\n)*$/', '', $xml);
            }

            $RESULT['country_code'] = strToUpper($RESULT['country_code']);

            $cookie_content = base64_encode(serialize($RESULT));
            setcookie($cookie_name, $cookie_content, 0, '/');
            return $RESULT;
        }
    }


    //http://ws.geonames.org/timezone?lat=47.01&lng=10.2
}