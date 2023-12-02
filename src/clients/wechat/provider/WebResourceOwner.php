<?php


namespace verbb\auth\clients\wechat\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class WebResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * 普通用户的标识，对当前开发者帐号唯一
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['openid'] ?: null;
    }

    /**
     * 普通用户昵称
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->response['nickname'] ?: null;
    }

    /**
     * 普通用户性别，1为男性，2为女性
     *
     * @return string|null
     */
    public function getSex(): ?string
    {
        return $this->response['sex'] ?: null;
    }

    /**
     * 普通用户个人资料填写的省份
     *
     * @return string|null
     */
    public function getProvince(): ?string
    {
        return $this->response['province'] ?: null;
    }

    /**
     * 普通用户个人资料填写的城市
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->response['city'] ?: null;
    }

    /**
     * 国家，如中国为CN
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->response['country'] ?: null;
    }

    /**
     * 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     *
     * @return string|null
     */
    public function getHeadImgUrl(): ?string
    {
        return $this->response['headimgurl'] ?: null;
    }

    /**
     * 用户特权信息，json数组，如微信沃卡用户为（chinaunicom）
     *
     * @return string|null
     */
    public function getPrivilege(): ?string
    {
        return $this->response['privilege'] ?: null;
    }

    /**
     * 用户统一标识。针对一个微信开放平台帐号下的应用，同一用户的unionid是唯一的
     *
     * @return string|null
     */
    public function getUnionId(): ?string
    {
        return $this->response['unionid'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
