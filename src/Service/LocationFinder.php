<?php


namespace App\Service;


class LocationFinder
{
    private $repository;

    public function __construct(ElasticRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findByName(string $name)
    {
        $query = [
            'query' => [
                'match' => [
                    'name' => $name
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }

    public function findByNameWithFilters(string $name, array $filters)
    {
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['name' => $name]]
                    ],
                    'filter' => [
                        ['term' => ['feature_class' => $filters['feature_class']]]
                    ]
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }

    public function findByNameWithPoint(string $name, string $lat, string $lon, string $distance)
    {
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['name' => $name]]
                    ],
                    'filter' => [
                        ['geo_distance' => ['distance' => $distance, 'location_point' => ['lat' => $lat, 'lon' => $lon]]]
                    ]
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }

    public function findByNameSortByDistance(string $name, string $lat, string $lon)
    {
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['name' => $name]]
                    ]
                ]
            ],
            'sort' => [
                '_geo_distance' => [
                    'location_point' => [
                        'lat' => $lat,
                        'lon' => $lon
                    ],
                    'order' => 'asc',
                    'unit' => 'mi',
                    'mode' => 'min',
                    'distance_type' => 'arc'
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }

    /**
     * @param $points - an array of strings formatted as lat,lon
     * @return array
     */
    public function findByPolygon($points)
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'geo_polygon' => [
                            'location_point' => ['points' => $points]
                        ]
                    ]
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }


    public function save($location)
    {
        return $this->repository->index('location-list', 'location', $location);
    }

    public function findById($id)
    {
        return $this->repository->get('location-list', 'location', $id);
    }

    public function addComment($id, $comment)
    {
        return $this->repository->update('location-list', 'location', $id, $comment);
    }

    public function findByText(string $name)
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        ["match" => ['name' => $name]],
                        ["match" => ['comment' => $name]]
                    ]
                ]
            ]
        ];

        return $this->repository->searchQuery('location-list', $query);
    }


}