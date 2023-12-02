<?php

namespace verbb\auth\clients\stackexchange\provider;

use verbb\auth\clients\stackexchange\provider\exception\StackExchangeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

use function parse_str;
use function strpos;

class StackExchange extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return $this->urlAuthorize;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        if (empty($params['code'])) {
            $params['code'] = '';
        }

        return $this->urlAccessToken . '?' .
            $this->buildQueryString($params);
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
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
    protected string $urlApi = 'https://api.stackexchange.com/2.2/';

    /**
     * @var string
     */
    protected string $urlAuthorize = 'https://stackexchange.com/oauth';

    /**
     * @var string
     */
    protected string $urlAccessToken = 'https://stackexchange.com/oauth/access_token';

    /**
     * @var string
     */
    protected string $scope = '';

    /**
     * @var string
     */
    protected string $key = '';

    /**
     * @var string
     */
    protected string $site = 'stackoverflow';

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $redirectUri;

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function getAuthorizationParameters(array $options): array
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

    protected function parseResponse(ResponseInterface $response): array|string
    {
        $type = $this->getContentType($response);

        if (str_contains($type, 'plain')) {
            $content = (string) $response->getBody();
            parse_str($content, $parsed);

            return $parsed;
        }

        return parent::parseResponse($response);
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw StackExchangeException::errorResponse($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): StackExchangeResourceOwner
    {
        return new StackExchangeResourceOwner($response);
    }
}
