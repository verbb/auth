<?php
/**
 * Created by alexkeramidas for Authentiq B.V.
 * Authentiq Access Token
 * User: alexkeramidas
 * Date: 14/3/2017
 * Time: 8:34 μμ
 */

namespace verbb\auth\clients\authentiq\token;

use Exception;
use InvalidArgumentException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class AccessToken extends \League\OAuth2\Client\Token\AccessToken
{
    protected mixed $idToken;
    protected ?array $idTokenClaims;

    /**
     * Authentiq Access Token constructor that extends the original Access token constructor and gives back user info through the id token.
     */

    public function __construct(array $options = [], $provider, $clientSecret)
    {
        if (!isset($clientSecret)) {
            throw new InvalidArgumentException('Please use the parent constructor with only one argument as a client_secret is needed for this one');
        }

        parent::__construct($options);


        JWT::$leeway = 60;

        if (!empty($options['id_token'])) {
            $this->idToken = $options['id_token'];
            $this->idTokenClaims = null;
            try {
                $tokens = explode('.', $this->idToken);
                // Check if the id_token contains signature and try to decode it.
                if (count($tokens) == 3 && !empty($tokens[2])) {
                    $providerAlgorithms = $provider->getProviderAlgorithm();
                    $algorithm = $providerAlgorithms[0] ?? 'HS256';

                    // firebase/php-jwt >= 6 expects a Key object, older versions expect key + allowed algorithms.
                    if (class_exists(Key::class)) {
                        $idTokenClaims = (array)JWT::decode($this->idToken, new Key($clientSecret, $algorithm));
                    } else {
                        $idTokenClaims = (array)JWT::decode($this->idToken, $clientSecret, [$algorithm]);
                    }
                }
            } catch (Exception $e) {
                throw new RuntimeException("Unable to decode the id_token! The secret or the encryption algorithm used is incorrect");
            }

        }

        /**
         * Authentiq validations for the jwt (audience, sub and issuer)
         * The JWT library used also checks for
         * Empty key
         * Not allowed, unsupported or empty algorithm
         * Incorrect number of segments
         * Incorrect header encoding
         * Signature verification
         * If the nbf, iat, exp in conjunction with the leeway are defined and valid.
         */

        if (is_array($idTokenClaims['aud'])) {
            if (!str_contains(implode(" ", $idTokenClaims['aud']), $provider->getClientId())) {
                throw new RuntimeException('Invalid audience');
            }
        } else if ($provider->getClientId() != $idTokenClaims['aud']) {
            throw new RuntimeException('Invalid audience');
        }

        if ($idTokenClaims['sub'] == null) {
            throw new RuntimeException("The id token's sub is invalid!");
        }

        if ($idTokenClaims['iss'] == null || $idTokenClaims['iss'] != $provider->getDomain()) {
            throw new RuntimeException("The id token's issuer is invalid!");
        }

        $this->idTokenClaims = $idTokenClaims;
    }

    public function getIdTokenClaims(): ?array
    {
        return $this->idTokenClaims;
    }
}