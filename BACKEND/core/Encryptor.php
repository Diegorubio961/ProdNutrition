<?php

namespace Core;

use Core\Env;

class Encryptor
{
    private const METHOD = 'aes-256-cbc';

    /**
     * Encripta texto usando APP_SECRET (o la clave que pases).
     */
    public static function encrypt(string $data, ?string $secret = null): string
    {
        $secret = $secret ?? Env::get('APP_SECRET');
        $key    = hash('sha256', $secret, true);
        $iv     = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::METHOD));

        $encrypted = openssl_encrypt($data, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

        // Retorna base64 con IV + datos encriptados
        return base64_encode($iv . $encrypted);
    }

    /**
     * Desencripta texto usando APP_SECRET (o la clave que pases).
     */
    public static function decrypt(string $encryptedData, ?string $secret = null): ?string
    {
        $secret = $secret ?? Env::get('APP_SECRET');

        $data     = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(self::METHOD);
        $iv       = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        $key      = hash('sha256', $secret, true);

        $decrypted = openssl_decrypt($encrypted, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted === false ? null : $decrypted;
    }

    public static function decryptWithPrivateKey(string $encryptedBase64): ?string
    {
        $privateKeyPath = __DIR__ . '/../keys/private.pem';

        if (!file_exists($privateKeyPath)) {
            error_log('❌ Clave privada no encontrada en: ' . $privateKeyPath);
            return null;
        }

        $privateKeyContent = file_get_contents($privateKeyPath);
        $privateKey = openssl_pkey_get_private($privateKeyContent);

        if (!$privateKey) {
            error_log('❌ No se pudo cargar la clave privada: ' . openssl_error_string());
            return null;
        }

        $encryptedData = base64_decode($encryptedBase64);

        $success = openssl_private_decrypt($encryptedData, $decrypted, $privateKey);

        if (!$success) {
            error_log('❌ Error al desencriptar con clave privada: ' . openssl_error_string());
            return null;
        }

        return $decrypted;
    }
}
