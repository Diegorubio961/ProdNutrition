<?php
namespace Core;

class RSAKeyGenerator
{
    private string $keysDir;
    private string $privateKeyPath;
    private string $publicKeyPath;

    public function __construct(string $keysDir = __DIR__ . '/../keys')
    {
        $this->keysDir = $keysDir;
        $this->privateKeyPath = $this->keysDir . '/private.pem';
        $this->publicKeyPath  = $this->keysDir . '/public.pem';
    }

    public function generate(): void
    {
        if (!is_dir($this->keysDir)) {
            mkdir($this->keysDir, 0755, true);
            echo "📁 Carpeta '{$this->keysDir}' creada.\n";
        }

        if (file_exists($this->privateKeyPath) && file_exists($this->publicKeyPath)) {
            echo "🔐 Las llaves ya existen. No se generó nada.\n";
            return;
        }

        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if (!$res) {
            echo "❌ Error al generar claves RSA.\n";
            return;
        }

        openssl_pkey_export($res, $privateKey);
        file_put_contents($this->privateKeyPath, $privateKey);

        $keyDetails = openssl_pkey_get_details($res);
        file_put_contents($this->publicKeyPath, $keyDetails['key']);

        echo "✅ Claves RSA generadas exitosamente:\n";
        echo "🔑 {$this->privateKeyPath}\n";
        echo "🔓 {$this->publicKeyPath}\n";
    }
}
