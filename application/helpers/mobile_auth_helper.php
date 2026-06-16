<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Pure functions for the BERPS mobile bearer token.
 *
 * Lives in a helper (not a controller) so multiple API controllers can verify
 * tokens without instantiating each other — instantiating a CI_Controller from
 * another CI_Controller re-runs the autoload, which causes "Unable to locate
 * the specified class: Session.php" in this codebase.
 */

const MOBILE_TOKEN_TTL_SECONDS  = 2592000; // 30 days
const MOBILE_TOKEN_SECRET_FALLBACK = 'berps-mobile-secret-change-me';

if (!function_exists('mobile_token_secret')) {
    function mobile_token_secret()
    {
        $env = getenv('BERPS_MOBILE_SECRET');
        if (is_string($env) && $env !== '') {
            return $env;
        }
        $ci = function_exists('get_instance') ? get_instance() : null;
        if ($ci && isset($ci->config)) {
            $key = $ci->config->item('encryption_key');
            if (is_string($key) && $key !== '') {
                return $key;
            }
        }
        return MOBILE_TOKEN_SECRET_FALLBACK;
    }
}

if (!function_exists('mobile_b64url_encode')) {
    function mobile_b64url_encode($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

if (!function_exists('mobile_b64url_decode')) {
    function mobile_b64url_decode($value)
    {
        $remainder = strlen($value) % 4;
        if ($remainder) {
            $value .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($value, '-_', '+/'));
    }
}

if (!function_exists('mobile_build_token')) {
    function mobile_build_token(array $claims)
    {
        $now = time();
        $claims = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + MOBILE_TOKEN_TTL_SECONDS,
        ]);
        $header  = mobile_b64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = mobile_b64url_encode(json_encode($claims));
        $sig     = mobile_b64url_encode(
            hash_hmac('sha256', $header . '.' . $payload, mobile_token_secret(), true)
        );
        return $header . '.' . $payload . '.' . $sig;
    }
}

if (!function_exists('mobile_decode_token')) {
    /**
     * Returns ['ok' => true, 'claims' => array] on success
     * or ['ok' => false, 'message' => string, 'status' => int] on failure.
     */
    function mobile_decode_token($token)
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 3) {
            return ['ok' => false, 'message' => 'Invalid token.', 'status' => 401];
        }
        [$header, $payload, $sig] = $parts;
        $expected = mobile_b64url_encode(
            hash_hmac('sha256', $header . '.' . $payload, mobile_token_secret(), true)
        );
        if (!hash_equals($expected, $sig)) {
            return ['ok' => false, 'message' => 'Invalid token signature.', 'status' => 401];
        }
        $claims = json_decode(mobile_b64url_decode($payload), true);
        if (!is_array($claims)) {
            return ['ok' => false, 'message' => 'Invalid token payload.', 'status' => 401];
        }
        if (!isset($claims['exp']) || (int) $claims['exp'] < time()) {
            return ['ok' => false, 'message' => 'Session expired. Please sign in again.', 'status' => 401];
        }
        return ['ok' => true, 'claims' => $claims];
    }
}

if (!function_exists('mobile_send_cors')) {
    function mobile_send_cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }
}

if (!function_exists('mobile_json')) {
    function mobile_json($data, $status = 200)
    {
        $ci = get_instance();
        $encoded = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($encoded === false) {
            $status = 500;
            $encoded = json_encode([
                'ok' => false,
                'message' => 'Server failed to encode the response payload.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $ci->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output($encoded);
    }
}

if (!function_exists('mobile_api_error_message')) {
    function mobile_api_error_message($message)
    {
        $base = 'Mobile API server error.';
        if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
            $message = trim((string) $message);
            if ($message !== '') {
                return $base . ' ' . $message;
            }
        }
        return $base;
    }
}

if (!function_exists('mobile_register_error_handlers')) {
    function mobile_register_error_handlers()
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        set_exception_handler(static function ($throwable) {
            $message = $throwable instanceof Throwable
                ? $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine()
                : 'Unknown mobile API exception.';

            log_message('error', '[mobile_api_exception] ' . $message);

            if (!headers_sent()) {
                mobile_json([
                    'ok' => false,
                    'message' => mobile_api_error_message(
                        $throwable instanceof Throwable ? $throwable->getMessage() : ''
                    ),
                ], 500);
            }
        });

        register_shutdown_function(static function () {
            $error = error_get_last();
            if ($error === null) {
                return;
            }

            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
            if (!in_array((int) ($error['type'] ?? 0), $fatalTypes, true)) {
                return;
            }

            $message = trim((string) ($error['message'] ?? ''));
            $file = (string) ($error['file'] ?? '');
            $line = (int) ($error['line'] ?? 0);
            log_message('error', '[mobile_api_fatal] ' . $message . ' in ' . $file . ':' . $line);

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok' => false,
                    'message' => mobile_api_error_message($message),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        });
    }
}

if (!function_exists('mobile_avatar_url')) {
    function mobile_avatar_url($avatar)
    {
        $avatar = trim((string) $avatar);
        $base = rtrim(base_url(), '/');
        $fallback = $base . '/assets/images/users/avatar.png';

        if ($avatar === '' || $avatar === 'avatar.png') {
            return $fallback;
        }
        if (preg_match('/^https?:\/\//i', $avatar)) {
            return $avatar;
        }

        $relative = ltrim($avatar, '/');
        $candidates = [
            'upload/profile/' . $relative,
            'profimage/' . $relative,
        ];
        foreach ($candidates as $rel) {
            if (is_file(FCPATH . $rel)) {
                return $base . '/' . $rel;
            }
        }
        return $fallback;
    }
}

if (!function_exists('mobile_require_claims')) {
    /**
     * Reads the Authorization header, validates the token, and returns the
     * claims array. On failure it writes the JSON error and returns null —
     * controllers should `return;` immediately when they get null back.
     */
    function mobile_require_claims()
    {
        $ci = get_instance();
        $auth = trim((string) $ci->input->get_request_header('Authorization', true));
        if ($auth === '' || stripos($auth, 'Bearer ') !== 0) {
            mobile_json(['ok' => false, 'message' => 'Authorization required.'], 401);
            return null;
        }
        $token  = trim(substr($auth, 7));
        $result = mobile_decode_token($token);
        if (empty($result['ok'])) {
            mobile_json(
                ['ok' => false, 'message' => $result['message']],
                (int) ($result['status'] ?? 401)
            );
            return null;
        }
        return $result['claims'];
    }
}
