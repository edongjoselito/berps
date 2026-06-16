<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Zoho Mail API client (per-user accounts)
 *
 * Wraps Zoho's OAuth 2.0 + Mail REST API.
 * Account credentials & tokens are stored in `zoho_mail_accounts`, keyed by user_id.
 *
 * Docs:
 *   https://www.zoho.com/mail/help/api/
 *   https://www.zoho.com/accounts/protocol/oauth.html
 */
class Zoho_mail
{
    /** @var CI_Controller */
    protected $CI;

    /** Account row (object) for current user */
    protected $account = null;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    // ------------------------------------------------------------------
    // Account management
    // ------------------------------------------------------------------

    /** Get account row for given user_id (or current session user if null). */
    public function getAccount($user_id = null)
    {
        $user_id = (int) ($user_id ?? $this->CI->session->userdata('user_id'));
        if ($user_id <= 0) return null;

        return $this->CI->db
            ->where('user_id', $user_id)
            ->get('zoho_mail_accounts')
            ->row();
    }

    /** Persist a partial update (associative array) for an account. */
    public function saveAccount($user_id, array $data)
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) return false;

        $existing = $this->getAccount($user_id);
        $data['user_id'] = $user_id;

        if (!isset($data['settingsID'])) {
            $data['settingsID'] = (int) $this->CI->session->userdata('settingsID');
        }

        if ($existing) {
            $this->CI->db->where('user_id', $user_id)->update('zoho_mail_accounts', $data);
        } else {
            $this->CI->db->insert('zoho_mail_accounts', $data);
        }
        return true;
    }

    public function deleteAccount($user_id)
    {
        $this->CI->db->where('user_id', (int) $user_id)->delete('zoho_mail_accounts');
        return true;
    }

    // ------------------------------------------------------------------
    // OAuth helpers
    // ------------------------------------------------------------------

    /** Build the Zoho accounts endpoint base for a data center. */
    public function accountsBase($dc = 'com')
    {
        $dc = trim((string) $dc) ?: 'com';
        return 'https://accounts.zoho.' . $dc;
    }

    /** Build the Zoho Mail API base for a data center. */
    public function apiBase($dc = 'com')
    {
        $dc = trim((string) $dc) ?: 'com';
        return 'https://mail.zoho.' . $dc . '/api';
    }

    /** Build authorization URL for OAuth consent. */
    public function buildAuthUrl($user_id)
    {
        $acc = $this->getAccount($user_id);
        if (!$acc || !$acc->client_id || !$acc->redirect_uri) return null;

        $params = [
            'response_type' => 'code',
            'client_id'     => $acc->client_id,
            'scope'         => $acc->scope,
            'redirect_uri'  => $acc->redirect_uri,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => (string) $user_id,
        ];
        return $this->accountsBase($acc->data_center) . '/oauth/v2/auth?' . http_build_query($params);
    }

    /** Exchange auth code for refresh + access tokens. */
    public function exchangeCode($user_id, $code)
    {
        $acc = $this->getAccount($user_id);
        if (!$acc) return ['ok' => false, 'error' => 'Account not configured'];

        $url = $this->accountsBase($acc->data_center) . '/oauth/v2/token';
        $resp = $this->httpRequest('POST', $url, [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $acc->client_id,
                'client_secret' => $acc->client_secret,
                'redirect_uri'  => $acc->redirect_uri,
                'code'          => $code,
            ],
        ]);

        if (!$resp['ok']) return $resp;
        $body = $resp['json'] ?? [];

        if (empty($body['refresh_token']) && empty($body['access_token'])) {
            return ['ok' => false, 'error' => 'Token exchange failed: ' . json_encode($body)];
        }

        $update = [
            'auth_type'              => 'oauth',
            'access_token'           => $body['access_token'] ?? $acc->access_token,
            'access_token_expires_at'=> date('Y-m-d H:i:s', time() + (int) ($body['expires_in'] ?? 3000) - 60),
            'status'                 => 'connected',
            'last_error'             => null,
        ];
        if (!empty($body['refresh_token'])) {
            $update['refresh_token'] = $body['refresh_token'];
        }
        $this->saveAccount($user_id, $update);

        // Fetch primary account info
        $this->refreshAccountInfo($user_id);

        return ['ok' => true];
    }

    /** Get a usable access token, refreshing if needed. */
    public function getAccessToken($user_id)
    {
        $acc = $this->getAccount($user_id);
        if (!$acc || !$acc->refresh_token) return null;

        $expiresAt = $acc->access_token_expires_at ? strtotime($acc->access_token_expires_at) : 0;
        if ($acc->access_token && $expiresAt > time()) {
            return $acc->access_token;
        }

        $url = $this->accountsBase($acc->data_center) . '/oauth/v2/token';
        $resp = $this->httpRequest('POST', $url, [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => $acc->client_id,
                'client_secret' => $acc->client_secret,
                'refresh_token' => $acc->refresh_token,
            ],
        ]);
        if (!$resp['ok'] || empty($resp['json']['access_token'])) {
            $this->saveAccount($user_id, [
                'status'     => 'error',
                'last_error' => 'Refresh failed: ' . json_encode($resp['json'] ?? $resp['error'] ?? null),
            ]);
            return null;
        }
        $body = $resp['json'];
        $this->saveAccount($user_id, [
            'access_token'           => $body['access_token'],
            'access_token_expires_at'=> date('Y-m-d H:i:s', time() + (int) ($body['expires_in'] ?? 3000) - 60),
            'status'                 => 'connected',
            'last_error'             => null,
        ]);
        return $body['access_token'];
    }

    /** Pull primary account_id and email from Zoho accounts endpoint. */
    public function refreshAccountInfo($user_id)
    {
        $resp = $this->api($user_id, 'GET', '/accounts');
        if (!$resp['ok']) return $resp;
        $accounts = $resp['json']['data'] ?? [];
        if (empty($accounts) || !is_array($accounts)) return ['ok' => false, 'error' => 'No accounts returned'];

        $primary = $accounts[0];
        foreach ($accounts as $a) {
            if (!empty($a['isDefault'])) { $primary = $a; break; }
        }
        $this->saveAccount($user_id, [
            'account_id'    => (string) ($primary['accountId'] ?? ''),
            'primary_email' => (string) ($primary['primaryEmailAddress'] ?? ($primary['mailboxAddress'] ?? '')),
            'display_name'  => (string) ($primary['displayName'] ?? ''),
        ]);
        return ['ok' => true];
    }

    // ------------------------------------------------------------------
    // Generic API caller
    // ------------------------------------------------------------------

    /**
     * Call a Zoho Mail API endpoint.
     *
     * @param int    $user_id
     * @param string $method  GET|POST|PUT|DELETE
     * @param string $path    /accounts, /accounts/{id}/messages/view, etc.
     * @param array  $opts    ['query'=>[], 'json'=>[], 'multipart'=>[...], 'headers'=>[], 'binary'=>true]
     * @return array          ['ok'=>bool, 'status'=>int, 'json'=>mixed|null, 'body'=>string, 'error'=>string|null, 'headers'=>array]
     */
    public function api($user_id, $method, $path, array $opts = [])
    {
        $token = $this->getAccessToken($user_id);
        if (!$token) return ['ok' => false, 'error' => 'No access token (account not connected).'];

        $acc = $this->getAccount($user_id);
        $url = $this->apiBase($acc->data_center) . $path;
        $opts['headers'] = array_merge(['Authorization: Zoho-oauthtoken ' . $token], $opts['headers'] ?? []);
        return $this->httpRequest($method, $url, $opts);
    }

    /** Convenience: return the Zoho account_id (auto-fetch if missing). */
    public function accountId($user_id)
    {
        $acc = $this->getAccount($user_id);
        if ($acc && !empty($acc->account_id)) return $acc->account_id;

        $this->refreshAccountInfo($user_id);
        $acc = $this->getAccount($user_id);
        return $acc->account_id ?? null;
    }

    // ------------------------------------------------------------------
    // High-level Mail operations
    // ------------------------------------------------------------------

    public function listFolders($user_id)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'GET', "/accounts/{$aid}/folders");
    }

    /**
     * List messages in a folder (or inbox if null).
     * $params: folderId, limit (default 25), start (1-indexed), status (read|unread|all)
     */
    public function listMessages($user_id, array $params = [])
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];

        $query = [
            'limit' => (int) ($params['limit'] ?? 25),
            'start' => (int) ($params['start'] ?? 1),
        ];
        if (!empty($params['folderId'])) $query['folderId'] = $params['folderId'];
        if (!empty($params['status']))   $query['status']   = $params['status'];

        return $this->api($user_id, 'GET', "/accounts/{$aid}/messages/view", ['query' => $query]);
    }

    public function getMessage($user_id, $folderId, $messageId)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'GET', "/accounts/{$aid}/folders/" . rawurlencode($folderId) . "/messages/" . rawurlencode($messageId));
    }

    public function getMessageContent($user_id, $folderId, $messageId)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'GET', "/accounts/{$aid}/folders/" . rawurlencode($folderId) . "/messages/" . rawurlencode($messageId) . "/content");
    }

    /**
     * Search messages.
     * Zoho search expects searchKey like: "subject:hello" or "from:user@x.com" or just text.
     */
    public function searchMessages($user_id, $searchKey, $limit = 25, $start = 1)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'GET', "/accounts/{$aid}/messages/search", [
            'query' => [
                'searchKey' => $searchKey,
                'limit'     => (int) $limit,
                'start'     => (int) $start,
            ],
        ]);
    }

    /**
     * Send a new email.
     * $payload: ['toAddress'=>'a@b.com', 'ccAddress'=>'', 'bccAddress'=>'', 'subject'=>'', 'content'=>'<html>',
     *            'mailFormat'=>'html'|'plaintext', 'fromAddress'=>'optional override',
     *            'attachments'=>[ ['storeName'=>..., 'attachmentPath'=>..., 'attachmentName'=>...], ... ]]
     */
    public function sendMessage($user_id, array $payload)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];

        $acc = $this->getAccount($user_id);
        if (empty($payload['fromAddress'])) {
            $payload['fromAddress'] = $acc->primary_email;
        }
        if (empty($payload['mailFormat'])) {
            $payload['mailFormat'] = 'html';
        }

        return $this->api($user_id, 'POST', "/accounts/{$aid}/messages", [
            'json' => $payload,
        ]);
    }

    /**
     * Upload an attachment to be referenced in a sendMessage call.
     * Returns Zoho's attachment metadata (storeName, attachmentName, attachmentPath).
     */
    public function uploadAttachment($user_id, $filePath, $fileName = null)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        if (!is_file($filePath)) return ['ok' => false, 'error' => 'File not found: ' . $filePath];

        $fileName = $fileName ?: basename($filePath);
        $token = $this->getAccessToken($user_id);
        $acc = $this->getAccount($user_id);
        $url = $this->apiBase($acc->data_center) . "/accounts/{$aid}/messages/attachments?uploadType=multipart&fileName=" . rawurlencode($fileName);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Zoho-oauthtoken ' . $token,
                'Content-Type: application/octet-stream',
            ],
            CURLOPT_POSTFIELDS     => file_get_contents($filePath),
            CURLOPT_TIMEOUT        => 120,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $json = json_decode($body, true);
        return [
            'ok'     => ($status >= 200 && $status < 300),
            'status' => $status,
            'json'   => $json,
            'body'   => $body,
            'error'  => $err ?: null,
        ];
    }

    /** Download an attachment (returns binary in 'body'). */
    public function downloadAttachment($user_id, $folderId, $messageId, $attachmentId)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'GET', "/accounts/{$aid}/folders/" . rawurlencode($folderId) .
            "/messages/" . rawurlencode($messageId) . "/attachmentinfo/" . rawurlencode($attachmentId), [
            'binary' => true,
        ]);
    }

    /** Mark messages read/unread/flag/etc. mode = markAsRead | markAsUnread | flag | trash | delete | archive */
    public function updateMessage($user_id, array $messageIds, $mode)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'PUT', "/accounts/{$aid}/messages", [
            'json' => [
                'mode'      => $mode,
                'messageId' => array_values($messageIds),
            ],
        ]);
    }

    public function moveMessages($user_id, array $messageIds, $destFolderId)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'PUT', "/accounts/{$aid}/messages", [
            'json' => [
                'mode'         => 'moveMessage',
                'messageId'    => array_values($messageIds),
                'destfolderId' => $destFolderId,
            ],
        ]);
    }

    public function createFolder($user_id, $folderName, $parentFolderId = null)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        $body = ['folderName' => $folderName];
        if ($parentFolderId) $body['parentFolderId'] = $parentFolderId;
        return $this->api($user_id, 'POST', "/accounts/{$aid}/folders", ['json' => $body]);
    }

    public function deleteFolder($user_id, $folderId)
    {
        $aid = $this->accountId($user_id);
        if (!$aid) return ['ok' => false, 'error' => 'No Zoho account id.'];
        return $this->api($user_id, 'DELETE', "/accounts/{$aid}/folders/" . rawurlencode($folderId));
    }

    // ------------------------------------------------------------------
    // HTTP transport
    // ------------------------------------------------------------------

    protected function httpRequest($method, $url, array $opts = [])
    {
        $headers = $opts['headers'] ?? [];
        $query   = $opts['query']   ?? null;
        $form    = $opts['form_params'] ?? null;
        $json    = $opts['json']    ?? null;
        $binary  = !empty($opts['binary']);

        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HEADER, true);

        $body = null;
        if ($form !== null) {
            $body = http_build_query($form);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        } elseif ($json !== null) {
            $body = json_encode($json);
            $headers[] = 'Content-Type: application/json';
        }
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            return ['ok' => false, 'status' => 0, 'json' => null, 'body' => '', 'headers' => [], 'error' => $err ?: 'cURL error'];
        }

        $rawHeaders = substr($resp, 0, $headerSize);
        $bodyOut    = substr($resp, $headerSize);
        $headersOut = $this->parseHeaders($rawHeaders);

        $jsonOut = null;
        if (!$binary) {
            $decoded = json_decode($bodyOut, true);
            if (json_last_error() === JSON_ERROR_NONE) $jsonOut = $decoded;
        }

        return [
            'ok'      => ($status >= 200 && $status < 300),
            'status'  => $status,
            'json'    => $jsonOut,
            'body'    => $bodyOut,
            'headers' => $headersOut,
            'error'   => ($status >= 400) ? ('HTTP ' . $status) : null,
        ];
    }

    protected function parseHeaders($raw)
    {
        $out = [];
        foreach (preg_split("/\r?\n/", trim((string) $raw)) as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = explode(':', $line, 2);
            $out[strtolower(trim($k))] = trim($v);
        }
        return $out;
    }
}
