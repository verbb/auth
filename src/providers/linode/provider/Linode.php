<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace verbb\auth\providers\linode\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Linode OAuth provider.
 */
class Linode extends AbstractProvider
{
    use BearerAuthorizationTrait;

    // Available scopes.
    const SCOPE_ACCOUNT_READ_ONLY        = 'account:read_only';
    const SCOPE_ACCOUNT_READ_WRITE       = 'account:read_write';
    const SCOPE_DOMAINS_READ_ONLY        = 'domains:read_only';
    const SCOPE_DOMAINS_READ_WRITE       = 'domains:read_write';
    const SCOPE_EVENTS_READ_ONLY         = 'events:read_only';
    const SCOPE_EVENTS_READ_WRITE        = 'events:read_write';
    const SCOPE_IMAGES_READ_ONLY         = 'images:read_only';
    const SCOPE_IMAGES_READ_WRITE        = 'images:read_write';
    const SCOPE_IPS_READ_ONLY            = 'ips:read_only';
    const SCOPE_IPS_READ_WRITE           = 'ips:read_write';
    const SCOPE_LINODES_READ_ONLY        = 'linodes:read_only';
    const SCOPE_LINODES_READ_WRITE       = 'linodes:read_write';
    const SCOPE_LONGVIEW_READ_ONLY       = 'longview:read_only';
    const SCOPE_LONGVIEW_READ_WRITE      = 'longview:read_write';
    const SCOPE_NODEBALANCERS_READ_ONLY  = 'nodebalancers:read_only';
    const SCOPE_NODEBALANCERS_READ_WRITE = 'nodebalancers:read_write';
    const SCOPE_STACKSCRIPTS_READ_ONLY   = 'stackscripts:read_only';
    const SCOPE_STACKSCRIPTS_READ_WRITE  = 'stackscripts:read_write';
    const SCOPE_VOLUMES_READ_ONLY        = 'volumes:read_only';
    const SCOPE_VOLUMES_READ_WRITE       = 'volumes:read_write';

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://login.linode.com/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://login.linode.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.linode.com/v4/account';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            $errors = isset($data['errors']) ? $data['errors'] : [['reason' => 'Unknown error']];
            throw new IdentityProviderException($errors[0]['reason'], $response->getStatusCode(), $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LinodeResourceOwner($response);
    }
}
