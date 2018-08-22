<?php

namespace EmilMassey\Payum\Przelewy24;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Reply\HttpRedirect;
use Psr\Http\Message\ResponseInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function doRegister(array $order): HttpRedirect
    {
        $response = $this->doRequest('POST', Constants::ACTION_REGISTER, $order);

        if (!isset($response['token'])) {
            throw new \RuntimeException('Token undefined');
        }

        return new HttpRedirect($this->getApiEndpoint() . Constants::ACTION_REDIRECT . '/' . $response['token']);
    }

    public function doVerify(array $details): void
    {
        $this->doRequest('POST', Constants::ACTION_VERIFY, $details);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, string $action, array $fields)
    {
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint() . $action, $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $content = $response->getBody()->getContents();
        $response = [];
        parse_str($content, $response);

        array_map('urldecode', $response);

        if (!isset($response['error']) || 0 != $response['error']) {
            $errorMessage = isset($response['error']) ? $response['error'] . ': ' : '';
            $errorMessage .= isset($response['errorMessage']) ? $response['errorMessage'] : 'Unknown error';

            throw new RuntimeException($errorMessage);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? Constants::SANDBOX_ENDPOINT : Constants::DEFAULT_ENDPOINT;
    }
}
