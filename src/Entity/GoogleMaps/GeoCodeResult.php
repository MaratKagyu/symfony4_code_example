<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 8/19/2018
 * Time: 11:50 PM
 */

namespace App\Entity\GoogleMaps;


class GeoCodeResult
{
    /**
     * @var array
     */
    private $googleApiResponseData = [];

    /**
     * GeoCodeResult constructor.
     * @param $googleApiResponseData
     */
    public function __construct($googleApiResponseData)
    {
        $this->googleApiResponseData = $googleApiResponseData;
    }

    /**
     * @return array
     */
    private function getFirstResult(): array
    {
        return $this->googleApiResponseData['results'][0] ?? [];
    }

    /**
     * @return array
     */
    private function getAddressComponentList(): array
    {
        return $this->getFirstResult()['address_components'] ?? [];
    }

    /**
     * @param string $typeString
     * @return array
     */
    private function getAddressComponentByType(string $typeString): array
    {
        foreach ($this->getAddressComponentList() as $component) {
            if (in_array($typeString, $component['types'])){
                return $component;
            }
        }
        return [];
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->getAddressComponentByType('country')['long_name'] ?? '';
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->getAddressComponentByType('administrative_area_level_1')['short_name'] ?? '';
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->getAddressComponentByType('locality')['long_name'] ?? '';
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        $street = $this->getAddressComponentByType('route')['long_name'] ?? '';
        $streetNumber = $this->getAddressComponentByType('street_number')['long_name'] ?? '';
        return trim($street . " " . $streetNumber);
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        // postal_code
        return $this->getAddressComponentByType('postal_code')['long_name'] ?? '';
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return (float)$this->getFirstResult()['geometry']['location']['lat'] ?? 0;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return (float)$this->getFirstResult()['geometry']['location']['lng'] ?? 0;
    }
}