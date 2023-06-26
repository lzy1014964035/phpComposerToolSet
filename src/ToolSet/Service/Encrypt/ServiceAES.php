<?php

namespace ToolSet\Service\Encrypt;

use ToolSet\Service\ServiceBase;

class ServiceAES extends ServiceBase
{
    private $key;
    private $iv;

    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    function encryptAES($data) {
        $cipher = "AES-256-CBC";
        $options = OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($data, $cipher, $this->key, $options, $this->iv);
        return base64_encode($encrypted);
    }

    function decryptAES($encryptedData) {
        $cipher = "AES-256-CBC";
        $options = OPENSSL_RAW_DATA;
        $decrypted = openssl_decrypt(base64_decode($encryptedData), $cipher, $this->key, $options, $this->iv);
        return $decrypted;
    }
}