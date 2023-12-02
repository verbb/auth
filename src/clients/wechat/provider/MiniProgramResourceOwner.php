<?php


namespace verbb\auth\clients\wechat\provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use verbb\auth\clients\wechat\token\miniprogram\AccessToken;
use verbb\auth\clients\wechat\support\miniprogram\MiniProgramDataCrypt;
use Exception;

class MiniProgramResourceOwner implements ResourceOwnerInterface
{
    /** @var  AccessToken */
    protected AccessToken $token;

    protected $appid;
    protected array $responseUserInfo = [];
    protected string $decryptData = '';

    public function __construct(array $response, $token, $appid)
    {
        $this->checkSignature($response, $token);
        $this->responseUserInfo = $response;
        $this->token = $token;
        $this->appid = $appid;

        if (!empty($response['encryptedData'])) {
            $this->decryptData = $this->decrypt();
        }
    }


    /**
     * @param $response
     * @param AccessToken $token
     * @throws Exception
     */
    private function checkSignature($response, AccessToken $token): void
    {
        if ($response['signature'] !== sha1(
            $response['rawData'].$token->getSessionKey()
        )) {
            throw new IdentityProviderException('signature error', 0, $response);
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    private function decrypt(): string
    {
        $dataCrypt = new MiniProgramDataCrypt(
            $this->appid,
            $this->token->getSessionKey()
        );
        $errCode = $dataCrypt->decryptData(
            $this->responseUserInfo['encryptedData'],
            $this->responseUserInfo['iv'],
            $data
        );

        if ($errCode == 0) {
            return $data;
        }

        throw new IdentityProviderException('decrypt error', $errCode, $this->responseUserInfo);
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->decryptData ? $this->decryptData['openid'] : null;
    }

    public function getDecryptData(): string
    {
        return $this->decryptData;
    }

    public function getResponseUserInfo(): array
    {
        return $this->responseUserInfo;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->token->getValues();
    }
}
