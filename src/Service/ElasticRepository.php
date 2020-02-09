<?php


namespace App\Service;

use Elasticsearch\ClientBuilder;


class ElasticRepository
{
    public function bulkInsert($indexName, $indexType, $data_lines)
    {
        $client = $this->getClient();

        $json_body = [];

        foreach ($data_lines as $line)
        {
            $json_body[] = ['index' => [
                    '_index' => $indexName,
                    '_type' => $indexType
                ]
            ];

            $json_body[] = $line;
        }

        $responses = $client->bulk(['body' => $json_body]);
    }

    private function getClient()
    {
        return ClientBuilder::create()
            ->setHosts(['127.0.0.1:9200'])
            ->build();
    }

    public function searchQuery($indexName, $query)
    {
        $client = $this->getClient();
        $params = [
            'index' => $indexName,
            'body' => $query
        ];
        return $this->parseResponse($client->search($params));
    }

    private function parseResponse($response)
    {
        $count = $response['hits']['total'];
        $data = array_map(function ($match) {
            if (!empty($match['sort'])) {
                return array_merge($match['_source'], ['distance' => $match['sort'][0]]);
            }
            return $match['_source'];
        },
            $response['hits']['hits']
        );

        return ['total' => $count, 'data' => $data];
    }















    public function index($indexName, $indexType, $location)
    {
        $client = $this->getClient();
        $params = [
            'index' => $indexName,
            'type' => $indexType,
            'body' => (array)$location
        ];

        return $client->index($params);
    }

    public function update($indexName, $indexType, $id, $changedFields)
    {
        $client = $this->getClient();
        $params = [
            'index' => $indexName,
            'type' => $indexType,
            'id' => $id,
            'body' => [
                'doc' => $changedFields
            ]
        ];
        $client->update($params);
    }

    public function get($indexName, $indexType, $id)
    {
        $client = $this->getClient();
        $params = [
            'index' => $indexName,
            'type' => $indexType,
            'id'    => $id
        ];
        $doc = $client->get($params);
        return $doc['_source'];
    }

}