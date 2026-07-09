<?php

namespace verbb\auth\clients\apple\token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

use League\OAuth2\Client\Token\AccessToken;
use Exception;
use UnexpectedValueException;

class AppleAccessToken extends AccessToken
{
    /**
     * @var string|null
     */
    protected mixed $idToken = null;

    /**
     * @var string|null
     */
    protected mixed $email = null;

    /**
     * @var boolean|null
     */
    protected mixed $isPrivateEmail = null;

    /**
     * Constructs an access token.
     *
     * @param Key[] $keys Valid Apple JWT keys
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     *
     * @throws Exception
     */
    public function __construct(array $keys, array $options = [])
    {
        if (array_key_exists('refresh_token', $options)) {
            if (empty($options['id_token'])) {
                throw new InvalidArgumentException('Required option not passed: "id_token"');
            }

            $decoded = null;
            $last = end($keys);
            foreach ($keys as $key) {
                try {
                    try {
                        $decoded = JWT::decode($options['id_token'], $key);
                    } catch (\UnexpectedValueException $e) {
                        $decodeMethodReflection = new \ReflectionMethod(JWT::class, 'decode');
                        $decodeMethodParameters = $decodeMethodReflection->getParameters();
                        
                        // Backwards compatibility for firebase/php-jwt >=5.2.0 <=5.5.1 supported by PHP 5.6
                        if (array_key_exists(2, $decodeMethodParameters) && 'allowed_algs' === $decodeMethodParameters[2]->getName()) {
                            $decoded = JWT::decode($options['id_token'], $key, ['RS256']);
                        } else {
                            $headers = (object) ['alg' => 'RS256'];
                            $decoded = JWT::decode($options['id_token'], $key, $headers);
                        }
                    }
                    break;
                } catch (Exception $exception) {
                    if ($last === $key) {
                        throw $exception;
                    }
                }
            }
            if (null === $decoded) {
                throw new Exception('Got no data within "id_token"!');
            }
            $payload = json_decode(json_encode($decoded), true);

            $options['resource_owner_id'] = $payload['sub'];

            if (isset($payload['email_verified']) && $payload['email_verified']) {
                $options['email'] = $payload['email'];
            }

            if (isset($payload['is_private_email'])) {
                $this->isPrivateEmail = $payload['is_private_email'];
            }
        }

        parent::__construct($options);

        if (isset($options['id_token'])) {
            $this->idToken = $options['id_token'];
        }

        if (isset($options['email'])) {
            $this->email = $options['email'];
        }
    }

    /**
     * @return string
     */
    public function getIdToken(): string
    {
        return $this->idToken;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return boolean
     */
    public function isPrivateEmail(): bool
    {
        return (bool) $this->isPrivateEmail;
    }
}
