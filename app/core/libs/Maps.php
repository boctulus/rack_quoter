<?php declare(strict_types=1);

namespace boctulus\SW\core\libs;

/*
	@author boctulus
*/

class Maps
{
    /*
        La direccon que debe pasarse es por ejemplo:
        
        'Diego de Torres 5, Acala de Henaes, Madrid'
    */
    static function getCoord(string $address)
    {   
        $apiKey = env('GMAPS_API_KEY'); 

        // Get JSON results from this request

        $client = new ApiClient('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);

        $geo_res = $client
        ->disableSSL()
        ->get();

        if ($geo_res->getStatus() != 200){
            return null;
        }

        $geo = $geo_res->getResponse()['data'];

        if (isset($geo['status']) && ($geo['status'] == 'OK')) {
            $lat = $geo['results'][0]['geometry']['location']['lat']; // Latitude
            $lon = $geo['results'][0]['geometry']['location']['lng']; // Longitude

            return [
                'lat' => $lat,
                'lon' => $lon
            ];
        }
    }

}
   