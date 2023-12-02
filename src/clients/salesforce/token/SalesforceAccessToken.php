<?php
namespace verbb\auth\clients\salesforce\token;

use League\OAuth2\Client\Token\AccessToken;

class SalesforceAccessToken extends AccessToken
{
    /**
     * All Salesforce Organisation IDs start with this Prefix
     */
    public const ORG_ID_PREFIX = '00D';

    /**
     * Instance URL
     *
     * @var string
     */
    private mixed $instanceUrl;

    /**
     * Constructs an access token.
     *
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->instanceUrl = $options['instance_url'];
    }

    /**
     * Returns Salesforce instance URL related to Access Token
     *
     * @return string
     */
    public function getInstanceUrl(): string
    {
        return $this->instanceUrl;
    }

    /**
     * Returns Organisation ID related to Access Token
     *
     * @return string|null
     */
    public function getOrgId(): ?string
    {
        return preg_match('/' . self::ORG_ID_PREFIX .  '(\w{15}|\w{12})/', $this->getResourceOwnerId(), $result)
            ? $result[0]
            : null;
    }
}
