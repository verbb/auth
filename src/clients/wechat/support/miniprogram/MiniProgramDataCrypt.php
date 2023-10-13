<?php


namespace verbb\auth\clients\wechat\support\miniprogram;

/**
 * 对微信小程序用户加密数据的解密
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */
class MiniProgramDataCrypt
{
    public const OK = 0;
    public const ILLEGAL_AES_KEY = -41001;
    public const ILLEGAL_IV = -41002;
    public const ILLEGAL_BUFFER = -41003;
    public const DECODE_BASE64_ERROR = -41004;

    private string $appid;
    private string $sessionKey;

    /**
     * 构造函数
     *
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct(string $appid, string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     *
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData(string $encryptedData, string $iv, string &$data): int
    {
        if (strlen($this->sessionKey) != 24) {
            return self::ILLEGAL_AES_KEY;
        }

        if (strlen($iv) != 24) {
            return self::ILLEGAL_IV;
        }

        $encoder = new PKCS7Encoder();
        $result = $encoder->decrypt($encryptedData, $this->sessionKey, $iv);

        if ($result[0] != 0) {
            return $result[0];
        }

        if ($result[1] == null) {
            return self::ILLEGAL_BUFFER;
        }
        if ($result[1]->watermark->appid != $this->appid) {
            return self::ILLEGAL_BUFFER;
        }
        $data = $result[1];

        return self::OK;
    }
}
