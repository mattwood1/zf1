<?php
class Coda_Google_Address
{	
	/**
	 * Uses Google API to resolve a partial address to a full address
	 * @param array $addressArray
	 * @return array | false
	 */
	public static function lookupAddress(array $addressArray)
	{
		$client = new Zend_Http_Client('http://maps.googleapis.com/maps/api/geocode/json');
		
		$urlencodedAddress = urlencode(implode(" ", $addressArray));
		
		$client->setParameterGet('sensor', 'false'); // Do we have a GPS sensor? Probably not on most servers.
		$client->setParameterGet('address', $urlencodedAddress); // Should now be '1600+Amphitheatre+Parkway,+Mountain+View,+CA'
		
		$response = $client->request('GET'); // We must send our parameters in GET mode, not POST
			
		$results = Zend_Json::decode($response->getBody());
		
		//$key = (count($results['results']) - 1);
		$addressComponents = $results['results'][0]['address_components'];
		$geometryComponents = $results['results'][0]['geometry']['location'];
		
		if ($addressComponents && $geometryComponents) {
			$address = array();
			$geometry = array();
			foreach ($addressComponents as $element) {
				if (! $element[types][0]) {
					$element[types][0] = "premise";
				}
				$address[$element[types][0]] = $element['long_name'];
			}
		
			if ($address['locality'] == $address['postal_town']) {
				$address['locality'] = "";
			}
			if ($address['administrative_area_level_2'] == $address['postal_town']) {
				$address['administrative_area_level_2'] = "";
			}
			
			$geometry['lat'] = $geometryComponents['lat'];
			$geometry['lng'] = $geometryComponents['lng'];
			return array_merge($address, $geometry);
		}
		return array();
	}
}