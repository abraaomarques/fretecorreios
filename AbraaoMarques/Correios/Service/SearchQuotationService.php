<?php

namespace AbraaoMarques\Correios\Service;

use AbraaoMarques\Correios\Api\SearchQuotationServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SearchQuotationService implements SearchQuotationServiceInterface
{
    /**
     * @param $url
     * @return object|null
     * @throws \Exception
     */
    public function search($url): ?object
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/xml'
        ];
        $request = new Request('GET', $url, $headers);
        $res = $client->sendAsync($request)->wait();
        $result = $res->getBody()->getContents();
        $data = new \SimpleXMLElement($result);

        return $data->cServico;
    }
}
