<?php

namespace IlCleme\Cryptocurrencies\Gateways\Coinmarketcap;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use IlCleme\Cryptocurrencies\Gateways\Gateway;
use Illuminate\Support\Arr;

//TODO: create specific method for every coinmarketcap endpoint to permit custom cache time per request
class CoinmarketcapGateway extends Gateway
{
    /** @var string endpoint for coinmarketcap platform */
    protected $endpoint = '';

    /** @var $name string Name of gateway */
    protected $name = 'coinmarketcap';

    public function __construct(Client $http)
    {
        parent::__construct($http);

        $this->endpoint = config('cryptocurrencies.coinmarketcap.sandbox_mode')
            ? config('cryptocurrencies.coinmarketcap.sandbox_url')
            : config('cryptocurrencies.coinmarketcap.production_url');
    }

    /**
     * {@inheritdoc}
     * TODO: Cache response body as suggested by coinmarketcap
     */
    public function send($endpoint, $method = 'GET', $options = [])
    {
        if (! Arr::get($options, 'headers.X-CMC_PRO_API_KEY')) {
            $options = array_merge(
                $options,
                [
                    'headers' => [
                        'X-CMC_PRO_API_KEY' => config(
                            'cryptocurrencies.coinmarketcap.api_key'
                        )
                    ]
                ]
            );
        }

        try {
            $response = $this->http->request($method, $this->endpoint.$endpoint, $options);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return $response->getBody()->getContents();
    }

    /**
     * Return ids of symbols on coinmarketcap
     */
    public function getCoinmarketcapId(
        string $listingStatus = 'active',
        int $start = 1,
        int $limit = 100,
        string $symbol = ''
    ): mixed {
        return $this->send(
            '/v1/cryptocurrency/map',
            'GET',
            [
                'listing_status' => $listingStatus,
                ...compact('start', 'limit', 'symbol')
            ]
        );
    }
}
