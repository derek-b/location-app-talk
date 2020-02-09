<?php


namespace App\Model;


use App\Service\ElasticRepository;

class LocationList
{
    private $repository;
    private $locations = [];

    public function __construct()
    {
        $this->repository = new ElasticRepository();
    }

    public function addLocation(Location $location)
    {
        $this->locations[] = $location;
        if (count($this->locations) >= 100)
        {
            $this->repository->bulkInsert('location-list', 'location', $this->prepareBulkJson($this->locations));
            $this->locations = [];
        }
    }

    public function flush()
    {
        $this->repository->bulkInsert('location-list', 'location', $this->prepareBulkJson($this->locations));
        $this->locations = [];
    }

    private function prepareBulkJson()
    {
       return array_map(function($location) {
           return [
               'geonameid' => $location->geonameid,
               'name' => $location->name,
               'asciiname' => $location->asciiname,
               'alternatenames' => $location->alternatenames,
               'location_point' => $location->latitude . ", " . $location->longitude,
               'feature_class' => $location->featureClass,
               'feature_code' => $location->featureCode,
               'elevation' => $location->elevation,
               'timezone' => $location->timezone
           ];
       }, $this->locations);
    }
}