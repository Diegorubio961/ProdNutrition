<?php
namespace Core;

use Core\Env;

/**
 * Lightweight JWT handler supporting:
 *  - Access‑tokens (short‑lived)
 *  - Refresh‑tokens (long‑lived)
 * Both tokens are JWTs signed with independent secrets.
 */
class JWT
{
    /* ───── Configuración dinámica ───── */
    private static string $ACCESS_SECRET;
    private static string $REFRESH_SECRET;

    private const ACCESS_TTL  = 900;       // 15 minutos
    private const REFRESH_TTL = 1209600;   // 14 días

    /**
     * Inicializa los secretos desde el archivo .env
     * Debes llamarlo una vez antes de generar/verificar tokens.
     */
    public static function init(): void
    {
        self::$ACCESS_SECRET  = Env::get('ACCESS_SECRET');
        self::$REFRESH_SECRET = Env::get('REFRESH_SECRET');
    }

    /* ───── API Pública ───── */

    public static function makeAccessToken(array $user): string
    {
        $payload = [
            'sub' => $user['id'],
            'name'=> $user['name'] ?? null,
            'lastname' => $user['lastname'] ?? null,
            'fullname' => $user['name']  . ' ' . $user['lastname'],
            'email' => $user['email'],
            'roles' => $user['roles'] ?? [],
            'permissions' => $user['permissions'] ?? [],
            'iat' => time(),
            'exp' => time() + self::ACCESS_TTL
        ];
        return self::encode($payload, self::$ACCESS_SECRET);
    }

    public static function makeRefreshToken(int|string $userId): string
    {
        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + self::REFRESH_TTL,
            'typ' => 'refresh'
        ];
        return self::encode($payload, self::$REFRESH_SECRET);
    }

    public static function verifyAccess(string $jwt): ?array
    {
        return self::decode($jwt, self::$ACCESS_SECRET);
    }

    public static function verifyRefresh(string $jwt): ?array
    {
        $data = self::decode($jwt, self::$REFRESH_SECRET);
        return ($data && ($data['typ'] ?? '') === 'refresh') ? $data : null;
    }

    /* ───── Internos ───── */

    private static function encode(array $payload, string $secret): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body   = self::base64UrlEncode(json_encode($payload));
        $sigRaw = hash_hmac('sha256', "$header.$body", $secret, true);
        $sig    = self::base64UrlEncode($sigRaw);
        return "$header.$body.$sig";
    }

    private static function decode(string $jwt, string $secret): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        [$header64, $body64, $sig64] = $parts;
        $expected = self::base64UrlEncode(hash_hmac('sha256', "$header64.$body64", $secret, true));
        if (!hash_equals($expected, $sig64)) return null;

        $payload = json_decode(self::base64UrlDecode($body64), true);
        if (!$payload || ($payload['exp'] ?? 0) < time()) return null;

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
