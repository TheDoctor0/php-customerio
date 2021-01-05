<?php

namespace Customerio;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use stdClass;

class Request
{

    /**
     * An HTTP Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Basic Auth Credentials
     * ['user', 'password']
     *
     * @var array
     */
    protected $auth = [];

    /**
     * Create a new Request
     * Bootlegness for testing
     *
     * @param null $client
     */
    public function __construct($client = null)
    {
        if (! is_null($client)) {
            $this->client = $client;

            return;
        }

        $this->client = new Client([
            'base_uri' => 'https://track.customer.io',
        ]);
    }

    /**
     * Send Create/Update Customer Request
     *
     * @param $id
     * @param $email
     * @param $attributes
     *
     * @return Response
     */
    public function customer($id, $email, $attributes)
    {
        $body = array_merge(['email' => $email], $attributes);

        try {
            $response = $this->client->put('/api/v1/customers/' . $id, [
                'auth' => $this->auth,
                'json' => $body,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (RequestException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        return new Response($response->getStatusCode(), $response->getReasonPhrase());
    }

    /**
     * Send Delete Customer Request
     *
     * @param $id
     *
     * @return Response
     */
    public function deleteCustomer($id)
    {
        try {
            $response = $this->client->delete('/api/v1/customers/' . $id, [
                'auth' => $this->auth,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (RequestException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        return new Response($response->getStatusCode(), $response->getReasonPhrase());
    }

    /**
     * Send and Event to Customer.io
     *
     * @param $id
     * @param $name
     * @param $data
     *
     * @return Response
     */
    public function event($id, $name, $data)
    {
        $body = array_merge(['name' => $name], $this->parseData($data));

        try {
            $response = $this->client->post('/api/v1/customers/' . $id . '/events', [
                'auth' => $this->auth,
                'json' => $body,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        } catch (RequestException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        return new Response($response->getStatusCode(), $response->getReasonPhrase());
    }

    /**
     * Set Authentication credentials
     *
     * @param $apiKey
     * @param $apiSecret
     *
     * @return $this
     */
    public function authenticate($apiKey, $apiSecret)
    {
        $this->auth[0] = $apiKey;
        $this->auth[1] = $apiSecret;

        return $this;
    }

    /**
     * Parse data as specified by customer.io
     *
     * @link http://customer.io/docs/api/rest.html
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseData(array $data)
    {
        if (empty($data)) {
            return ['data' => new stdClass()];
        }

        return ['data' => $data];
    }

}
