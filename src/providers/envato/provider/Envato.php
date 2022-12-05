<?php

namespace verbb\auth\providers\envato\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Envato extends AbstractProvider {

	use BearerAuthorizationTrait;

	/**
	 * Api domain
	 *
	 * @var string
	 */
	public $apiDomain = 'https://api.envato.com';

	/**
	 * Get authorization url to begin OAuth flow
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return "$this->apiDomain/authorization";
	}

	/**
	 * Get access token url to retrieve token
	 *
	 * @param  array $params
	 *
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params ) {
		return "$this->apiDomain/token";
	}

	/**
	 * Get provider url to fetch username
	 *
	 * @param  AccessToken $token
	 *
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl( AccessToken $token ) {
		return "$this->apiDomain/v1/market/private/user/username.json";
	}

	/**
	 * Get provider url to fetch user email
	 *
	 * @return string
	 */
	public function getResourceOwnerEmailUrl() {
		return "$this->apiDomain/v1/market/private/user/email.json";
	}

	/**
	 * Get provider url to fetch the user purchases list
	 *
	 * @param array $extraParams
	 * @return string
	 */
	public function getResourceOwnerPurchasesUrl($extraParams = []) {

		$purchasesEndpoint = "$this->apiDomain/v3/market/buyer/list-purchases";

		if( ! empty( $extraParams['filter_by'] ) && in_array($extraParams['filter_by'], ['wordpress-themes', 'wordpress-plugins']) ){
			$purchasesEndpoint .= "?filter_by={$extraParams['filter_by']}";
		}

		// TODO: ALLOW ITEM PAGINATION

		return $purchasesEndpoint;
	}

	/**
	 * Get the default scopes used by this provider.
	 *
	 * This should not be a complete list of all scopes, but the minimum
	 * required for the provider user interface!
	 *
	 * @return array
	 */
	protected function getDefaultScopes() {
		return [ ];
	}


	/**
	 * Requests and returns the resource owner of given access token.
	 *
	 * @param AccessToken $token
	 * @param string $endpoint
	 * @param array $extraParams
	 *
	 * @return ResourceOwnerInterface
	 */
	public function getResourceOwner( AccessToken $token ) {
		$response = $this->getResourceOwnerDetailsUrl( $token );

		return $this->createResourceOwner( $response, $token );
	}

	/**
	 * Requests resource owner details.
	 *
	 * @param AccessToken $token
	 * @param string $endpoint
	 * @param array $extraParams
	 *
	 * @return mixed
	 */
	protected function fetchResourceOwnerDetails( AccessToken $token ) {

		$url = $this->getResourceOwnerDetailsUrl( $token );

		$request = $this->getAuthenticatedRequest( self::METHOD_GET, $url, $token );

		return $this->getResponse( $request );
	}

	/**
	 * Check a provider response for errors.
	 *
	 * @throws IdentityProviderException
	 *
	 * @param  ResponseInterface $response
	 * @param  string            $data Parsed response data
	 *
	 * @return void
	 */
	protected function checkResponse( ResponseInterface $response, $data ) {
		if ( $response->getStatusCode() >= 400 ) {
			throw new IdentityProviderException(
				$data['message'] ?: $response->getReasonPhrase(),
				$response->getStatusCode(),
				$response
			);
		}
	}

	/**
	 * Generate a user object from a successful user details request.
	 *
	 * @param array       $response
	 * @param AccessToken $token
	 *
	 * @return \Smachi\OAuth2\Client\Provider\EnvatoUser
	 */
	protected function createResourceOwner( array $response, AccessToken $token ) {
		$user = new EnvatoUser( $response );

		return $user->setDomain( $this->apiDomain );
	}
}
