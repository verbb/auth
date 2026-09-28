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

    protected ?bool $emailVerified = null;

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
    public function __construct(array $keys, array $options = [], ?string $expectedAudience = null)
    {
        if (empty($options['id_token'])) {
            throw new InvalidArgumentException('Required option not passed: "id_token"');
        }

        $decoded = null;
        $last = end($keys);
        foreach ($keys as $key) {
            try {
                try {
                    $decoded = JWT::decode($options['id_token'], $key);
                } catch (UnexpectedValueException $e) {
                    $decodeMethodReflection = new \ReflectionMethod(JWT::class, 'decode');
                    $decodeMethodParameters = $decodeMethodReflection->getParameters();

                    if (array_key_exists(2, $decodeMethodParameters) && 'allowed_algs' === $decodeMethodParameters[2]->getName()) {
                        $decoded = JWT::decode($options['id_token'], $key, ['RS256']);
                    } else {
                        $headers = (object)['alg' => 'RS256'];
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

        if ($decoded === null) {
            throw new Exception('Got no data within "id_token"!');
        }

        $payload = json_decode(json_encode($decoded), true);

        if (($payload['iss'] ?? null) !== 'https://appleid.apple.com') {
            throw new UnexpectedValueException('The Apple identity token has an invalid issuer.');
        }

        $audience = $payload['aud'] ?? null;

        if ($expectedAudience !== null && !(is_array($audience) ? in_array($expectedAudience, $audience, true) : $audience === $expectedAudience)) {
            throw new UnexpectedValueException('The Apple identity token has an invalid audience.');
        }

        if (empty($payload['sub']) || !is_string($payload['sub'])) {
            throw new UnexpectedValueException('The Apple identity token has no subject.');
        }

        $options['resource_owner_id'] = $payload['sub'];
        $this->emailVerified = $this->normalizeBoolean($payload['email_verified'] ?? null);

        if ($this->emailVerified === true && isset($payload['email']) && is_string($payload['email'])) {
            $options['email'] = $payload['email'];
        }

        if (array_key_exists('is_private_email', $payload)) {
            $this->isPrivateEmail = $this->normalizeBoolean($payload['is_private_email']);
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEmailVerified(): ?bool
    {
        return $this->emailVerified;
    }

    /**
     * @return boolean
     */
    public function isPrivateEmail(): bool
    {
        return (bool) $this->isPrivateEmail;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === false || $value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        return null;
    }
}
