<?php


namespace App\Services;
require_once '../vendor/autoload.php';
use Elastic\Elasticsearch\ClientBuilder;
use App\Models\Books;


class ElasticsearchService
{
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(['0.0.0.0:9200'])->build();
    }

    public function searchBooks($params)
    {
        
        $queryBuilder = Books::query();

        
        if (!empty($params['query'])) {
            $queryBuilder->where(function ($query) use ($params) {
                $query->where('title', 'like', '%' . $params['query'] . '%')
                    ->orWhere('author', 'like', '%' . $params['query'] . '%')
                    ->orWhere('isbn', 'like', '%' . $params['query'] . '%')
                    ->orWhere('genre', 'like', '%' . $params['query'] . '%')
                    ->orWhere('published', 'like', '%' . $params['query'] . '%');

            });
        }
        if (!empty($params['filter'])) {
            $queryBuilder->where($params['filter'], $params['query']);
        }        
        
        $results = $queryBuilder->get();

        return $results;
    }
}
