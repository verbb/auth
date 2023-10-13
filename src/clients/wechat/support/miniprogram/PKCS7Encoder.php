<?php


namespace verbb\auth\clients\wechat\support\miniprogram;

use verbb\auth\clients\wechat\support\common\AESEncoder;

/**
 * Class PKCS7Encoder
 *
 * 提供基于PKCS7算法的加解密接口
 * @package Oakhope\OAuth2\Client\SDK\MiniProgram
 */
class PKCS7Encoder
{
    public const BLOCK_SIZE = 16;

    /**
     * 对密文进行解密
     *
     * @param string $encrypted 需要解密的密文
     * @param string $key
     * @param string $iv 解密的初始向量
     * @return array 解密得到的明文
     */
    public function decrypt(string $encrypted, string $key, string $iv): array
    {
        $decrypted = AESEncoder::decrypt(
            base64_decode($encrypted, true),
            base64_decode($key, true),
            base64_decode($iv, true),
            OPENSSL_NO_PADDING
        );

        $result = $this->decode($decrypted);

        return array(0, json_decode($result));
    }

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param string $text 需要进行填充补位操作的明文
     * @return string 补齐明文字符串
     */
    private function encode(string $text): string
    {
        $text_length = strlen($text);
        // 计算需要填充的位数
        $amount_to_pad = self::BLOCK_SIZE - ($text_length % self::BLOCK_SIZE);
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::BLOCK_SIZE;
        }
        // 获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = str_repeat($pad_chr, $amount_to_pad);

        return $text.$tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param string $text 解密后的明文
     * @return string 删除填充补位后的明文
     */
    private function decode(string $text): string
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        return substr($text, 0, (strlen($text) - $pad));
    }
}
