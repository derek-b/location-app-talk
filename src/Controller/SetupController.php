<?php


namespace App\Controller;
use App\Model\Location;
use App\Model\LocationList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SetupController
{
    /**
     * @Route("/api/setup", name="setup_talk")
     */
    public function index()
    {
        set_time_limit(6000);
        $count = 0;
        $locations = new LocationList();
        $fp = fopen('/Users/derek/workspace/talks/elastic-location/data/US.txt', 'r');
        while(($row = fgetcsv($fp, 1000, "\t")) !== false)
        {
            $location = new Location();
            if (count($row) >= 18) {
                $location->geonameid = $row[0];
                $location->name = $row[1];
                $location->asciiname = $row[2];
                $location->alternatenames = $row[3];
                $location->latitude = $row[4];
                $location->longitude = $row[5];
                $location->featureClass = $row[6];
                $location->featureCode = $row[7];
                $location->elevation = $row[15];
                $location->timezone = $row[17];
                $locations->addLocation($location);
                $count++;
            }
        }
        $locations->flush();
        return new JsonResponse(['success_count' => $count]);
    }
}