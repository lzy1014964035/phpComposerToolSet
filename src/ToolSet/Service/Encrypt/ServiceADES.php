<?php

namespace ToolSet\Service\Encrypt;

use ToolSet\Service\ServiceBase;

class ServiceADES extends ServiceBase
{
    private $privateKey;
    private $publicKey;

    public function __construct($privateKey, $publicKey)
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    // 生成密钥对
    public static function generateKeyPair()
    {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        # todo win环境有读不到config的问题。error:0E064002:configuration file routines:CONF_load:system lib
        $res = openssl_pkey_new($config);
        if ($res === false) {
            throw new \Exception(openssl_error_string());
        }
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)["key"];

        return [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey
        ];
    }

    // 使用公钥加密
    public function encrypt($data)
    {
        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        return base64_encode($encrypted);
    }

    // 使用私钥解密
    public function decrypt($data)
    {
        $data = base64_decode($data);
        openssl_private_decrypt($data, $decrypted, $this->privateKey);
        return $decrypted;
    }

    // 使用私钥签名
    public function sign($data)
    {
        openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA512);
        return base64_encode($signature);
    }

    // 使用公钥验证签名
    public function verifySignature($data, $signature)
    {
        $signature = base64_decode($signature);
        return openssl_verify($data, $signature, $this->publicKey, OPENSSL_ALGO_SHA512) === 1;
    }

    public static function demo()
    {
        // 生成密钥对
        $keyPair = self::generateKeyPair();
        $privateKey = $keyPair['privateKey'];
        $publicKey = $keyPair['publicKey'];

        $service = new self($privateKey, $publicKey);

        // 要加密的数据
        $dataToEncrypt = 'Hello, World!';

        // 加密
        $encryptedData = $service->encrypt($dataToEncrypt);
        echo "Encrypted Data: " . $encryptedData . PHP_EOL;

        // 解密
        $decryptedData = $service->decrypt($encryptedData);
        echo "Decrypted Data: " . $decryptedData . PHP_EOL;

        // 签名
        $signature = $service->sign($dataToEncrypt);
        echo "Signature: " . $signature . PHP_EOL;

        // 验证签名
        $isSignatureValid = $service->verifySignature($dataToEncrypt, $signature);
        echo "Signature Verification: " . ($isSignatureValid ? "Valid" : "Invalid") . PHP_EOL;
    }
}