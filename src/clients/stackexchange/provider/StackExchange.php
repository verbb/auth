<?php

namespace verbb\auth\clients\stackexchange\provider;

use verbb\auth\clients\stackexchange\provider\exception\StackExchangeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class StackExchange extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @inheritDoc
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->urlAuthorize;
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        if (empty($params['code'])) {
            $params['code'] = '';
        }

        return $this->urlAccessToken . '?' .
            $this->buildQueryString($params);
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->urlApi . 'me?' .
            $this->buildQueryString([
                'access_token' => (string) $token,
                'key'          => $this->key,
                'site'         => $this->site,
            ]);
    }

    /**
     * @var string
     */
    protected $urlApi = 'https://api.stackexchange.com/2.2/';

    /**
     * @var string
     */
    protected $urlAuthorize = 'https://stackexchange.com/oauth';

    /**
     * @var string
     */
    protected $urlAccessToken = 'https://stackexchange.com/oauth/access_token';

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $site = 'stackoverflow';

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getAuthorizationParameters(array $options)
    {
        $options['response_type'] = 'code';
        $options['client_id'] = $this->clientId;

        if (empty($options['state'])) {
            $options['state'] = $this->state;
        }

        if (empty($options['scope'])) {
            $options['scope'] = $this->scope;
        }

        if (empty($options['redirect_uri'])) {
            $options['redirect_uri'] = $this->redirectUri;
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $type = $this->getContentType($response);

        if (\strpos($type, 'plain') !== false) {
            $content = (string) $response->getBody();
            \parse_str($content, $parsed);

            return $parsed;
        }

        return parent::parseResponse($response);
    }

    /**
     * @inheritDoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw StackExchangeException::errorResponse($response, $data);
        }
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new StackExchangeResourceOwner($response);
    }
}
