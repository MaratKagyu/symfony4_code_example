<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 8/19/2018
 * Time: 7:50 PM
 */

namespace App\Service;

use App\Entity\GoogleMaps\GeoCodeResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;


class GoogleMaps
{

    /**
     * @var string
     */
    private $mapsApiKey = "";

    /**
     * @var string
     */
    private $geoCodeApiKey = "";

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->mapsApiKey = $container->getParameter("app.googleCloud.mapsApiKey");
        $this->geoCodeApiKey = $container->getParameter("app.googleCloud.geoCodeApiKey");
    }

    /**
     * @return string
     */
    public function getMapsApiKey(): string
    {
        return $this->mapsApiKey;
    }

    /**
     * @return string
     */
    public function getGeoCodeApiKey(): string
    {
        return $this->geoCodeApiKey;
    }

    /**
     * @param string $fullAddressString
     * @return GeoCodeResult
     */
    public function getGeoData(string $fullAddressString): ?GeoCodeResult
    {
        $resultJson = @file_get_contents(
            "https://maps.googleapis.com/maps/api/geocode/json"
            . "?address=" . urlencode($fullAddressString)
            . "&key=" . $this->geoCodeApiKey
        );

        $resultData = @json_decode($resultJson ?: "", true);

        switch (mb_strtolower($resultData['status'] ?? '')) {
            case 'ok':
                return new GeoCodeResult($resultData);

            case 'zero_results':
                return null;

            case '':
                throw new HttpException(503, 'Google Maps API returned an empty result');

            default:
                throw new HttpException(
                    503,
                    (
                        'Google Maps API returned an unexpected result: '
                        . $resultJson
                    )
                );

        }
    }


    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return int
     */
    function distanceBwCoordinates(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadiusKm = 6371;

        $dLat = ($lat2 - $lat1) * M_PI/180;
        $dLon = ($lon2 - $lon1) * M_PI/180;

        $lat1 = $lat1 * M_PI/180;
        $lat2 = $lat2 * M_PI/180;

        $a = (
            (sin($dLat/2) * sin($dLat/2)) +
            (sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2))
        );
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadiusKm * $c);
    }
}


