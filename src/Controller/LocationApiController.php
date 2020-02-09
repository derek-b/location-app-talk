<?php


namespace App\Controller;
use App\Service\LocationFinder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class LocationApiController
{
    private $finder;

    public function __construct(LocationFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @Route("/api/location", name="location_api", methods={"GET"})
     */
    function location(Request $request)
    {
        $q = $request->get('q');
        $fclass = $request->get('feature_class');
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        if (!empty($fclass)) {
            $results = $this->finder->findByNameWithFilters($q, ['feature_class' => $fclass]);
        } else if (!empty($lat) && !empty($lon)) {
            $radius = $request->get('radius');
            if (!empty($radius)) {
                $results = $this->finder->findByNameWithPoint($q, $lat, $lon, $radius);
            } else {
                $results = $this->finder->findByNameSortByDistance($q, $lat, $lon);
            }
        } else {
            $results = $this->finder->findByName($q);
        }
        return new JsonResponse($results);
    }


    /**
     * @Route("/api/polygon", name="location_api")
     */
    function polygon(Request $request)
    {
        $points = json_decode($request->getContent());
        $results = $this->finder->findByPolygon($points->points);
        return new JsonResponse($results);
    }


    /**
     * @Route("/api/location", name="save_location", methods={"POST"})
     */
    function save(Request $request)
    {
        $location = json_decode($request->getContent());
        if (!$this->is_valid($location)) { return new JsonResponse(["message" => "Bad Data"], 422);}

        return new JsonResponse($this->finder->save($location));
    }

    /**
     * @Route("/api/location/{id}/comment", methods={"POST"})
     */
    function addComment(string $id, Request $request)
    {
        // first we get the location's comments
        $location = $this->finder->findById($id);

        // then we append
        if (!empty($location['comment']))
        {
            $comments = is_array($location['comment']) ? $location['comment'] : [$location['comment']];
        }

        $data = json_decode($request->getContent());
        $comments[] = $data->comment;

        // then update
        $this->finder->addComment($id, ['comment' => $comments]);

        return new JsonResponse(['success' => true]);
    }





















    /*function location(Request $request)
    {
        $q = $request->get('q');
        $fclass = $request->get('feature_class');
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        if (!empty($fclass)) {
           $results = $this->finder->findByNameWithFilters($q, ['feature_class' => $fclass]);
        } else if (!empty($lat) && !empty($lon)) {
            $radius = $request->get('radius');
            if (!empty($radius)) {
                $results = $this->finder->findByNameWithPoint($q, $lat, $lon, $radius);
            } else {
                $results = $this->finder->findByNameSortByDistance($q, $lat, $lon);
            }
        } else {
           $results = $this->finder->findByName($q);
        }
        return new JsonResponse($results);
    }*/



    private function is_valid($data) {return true;}

}