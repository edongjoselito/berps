<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Zoho Mail integration controller (per-user accounts).
 *
 *   GET  /ZohoMail/settings        - account/credentials form
 *   POST /ZohoMail/saveSettings    - persist credentials & options
 *   GET  /ZohoMail/connect         - redirect to Zoho OAuth consent
 *   GET  /ZohoMail/callback        - OAuth redirect target
 *   POST /ZohoMail/saveSelfClient  - paste-in refresh token (self-client mode)
 *   GET  /ZohoMail/disconnect      - clear tokens
 *
 *   GET  /ZohoMail/inbox[?folderId=&start=&search=]
 *   GET  /ZohoMail/view/{folderId}/{messageId}
 *   GET  /ZohoMail/compose
 *   POST /ZohoMail/send
 *   GET  /ZohoMail/attachment/{folderId}/{messageId}/{attId}/{name}
 *   POST /ZohoMail/messageAction   - mark/move/trash
 *   POST /ZohoMail/folderAction    - create/delete folder
 */
class ZohoMail extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['zoho_mail', 'session']);
        $this->load->helper(['url', 'form']);

        if (!$this->session->userdata('username')) {
            redirect('Login');
            return;
        }
    }

    protected function _user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    // -------------------------------------------------- Settings -----

    public function settings()
    {
        $data['account'] = $this->zoho_mail->getAccount($this->_user_id());
        $data['default_redirect'] = base_url('ZohoMail/callback');
        $this->load->view('zoho_mail/settings', $data);
    }

    public function saveSettings()
    {
        $uid = $this->_user_id();
        $payload = [
            'auth_type'    => $this->input->post('auth_type') === 'self_client' ? 'self_client' : 'oauth',
            'data_center'  => trim((string) $this->input->post('data_center')) ?: 'com',
            'client_id'    => trim((string) $this->input->post('client_id')),
            'client_secret'=> trim((string) $this->input->post('client_secret')),
            'redirect_uri' => trim((string) $this->input->post('redirect_uri')) ?: base_url('ZohoMail/callback'),
            'scope'        => trim((string) $this->input->post('scope')) ?: 'ZohoMail.messages.ALL,ZohoMail.accounts.READ,ZohoMail.folders.ALL,ZohoMail.attachments.ALL',
        ];
        $this->zoho_mail->saveAccount($uid, $payload);
        $this->session->set_flashdata('success', 'Zoho Mail settings saved.');
        redirect('ZohoMail/settings');
    }

    public function saveSelfClient()
    {
        $uid = $this->_user_id();
        $refresh = trim((string) $this->input->post('refresh_token'));
        if ($refresh === '') {
            $this->session->set_flashdata('danger', 'Refresh token is required.');
            redirect('ZohoMail/settings');
            return;
        }
        $this->zoho_mail->saveAccount($uid, [
            'auth_type'    => 'self_client',
            'refresh_token'=> $refresh,
            'access_token' => null,
            'access_token_expires_at' => null,
            'status'       => 'connected',
            'last_error'   => null,
        ]);
        // Try to fetch account info immediately
        $info = $this->zoho_mail->refreshAccountInfo($uid);
        if (!$info['ok']) {
            $this->session->set_flashdata('danger', 'Saved, but could not fetch account info: ' . ($info['error'] ?? 'unknown'));
        } else {
            $this->session->set_flashdata('success', 'Self-client refresh token saved & verified.');
        }
        redirect('ZohoMail/settings');
    }

    public function connect()
    {
        $url = $this->zoho_mail->buildAuthUrl($this->_user_id());
        if (!$url) {
            $this->session->set_flashdata('danger', 'Configure Client ID, Secret and Redirect URI first.');
            redirect('ZohoMail/settings');
            return;
        }
        redirect($url);
    }

    public function callback()
    {
        $code  = trim((string) $this->input->get('code'));
        $state = (int) $this->input->get('state');
        $err   = trim((string) $this->input->get('error'));

        if ($err !== '') {
            $this->session->set_flashdata('danger', 'Zoho returned: ' . $err);
            redirect('ZohoMail/settings');
            return;
        }
        if ($code === '' || $state <= 0) {
            $this->session->set_flashdata('danger', 'Missing authorization code or state.');
            redirect('ZohoMail/settings');
            return;
        }
        $resp = $this->zoho_mail->exchangeCode($state, $code);
        if (!$resp['ok']) {
            $this->session->set_flashdata('danger', 'Token exchange failed: ' . ($resp['error'] ?? 'unknown'));
        } else {
            $this->session->set_flashdata('success', 'Connected to Zoho Mail.');
        }
        redirect('ZohoMail/settings');
    }

    public function disconnect()
    {
        $this->zoho_mail->saveAccount($this->_user_id(), [
            'access_token' => null,
            'refresh_token'=> null,
            'access_token_expires_at' => null,
            'status'       => 'disconnected',
        ]);
        $this->session->set_flashdata('success', 'Disconnected from Zoho Mail.');
        redirect('ZohoMail/settings');
    }

    // -------------------------------------------------- Inbox & messages -----

    protected function _ensureConnected()
    {
        $acc = $this->zoho_mail->getAccount($this->_user_id());
        if (!$acc || $acc->status !== 'connected' || !$acc->refresh_token) {
            $this->session->set_flashdata('danger', 'Connect your Zoho Mail account first.');
            redirect('ZohoMail/settings');
            return false;
        }
        return $acc;
    }

    public function inbox()
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;

        $uid = $this->_user_id();
        $folderId = trim((string) $this->input->get('folderId'));
        $start    = max(1, (int) $this->input->get('start'));
        $search   = trim((string) $this->input->get('search'));

        $foldersResp = $this->zoho_mail->listFolders($uid);
        $folders = $foldersResp['ok'] ? ($foldersResp['json']['data'] ?? []) : [];

        if ($search !== '') {
            $resp = $this->zoho_mail->searchMessages($uid, $search, 25, $start);
        } else {
            $resp = $this->zoho_mail->listMessages($uid, [
                'folderId' => $folderId,
                'start'    => $start,
                'limit'    => 25,
            ]);
        }
        $messages = $resp['ok'] ? ($resp['json']['data'] ?? []) : [];

        $this->load->view('zoho_mail/inbox', [
            'account'      => $acc,
            'folders'      => $folders,
            'messages'     => $messages,
            'currentFolder'=> $folderId,
            'start'        => $start,
            'search'       => $search,
            'apiError'     => $resp['ok'] ? null : ($resp['error'] ?? json_encode($resp['json'] ?? null)),
        ]);
    }

    public function view($folderId = null, $messageId = null)
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        if (!$folderId || !$messageId) { show_404(); return; }

        $uid = $this->_user_id();
        $meta = $this->zoho_mail->getMessage($uid, $folderId, $messageId);
        $content = $this->zoho_mail->getMessageContent($uid, $folderId, $messageId);

        // Mark as read silently
        $this->zoho_mail->updateMessage($uid, [$messageId], 'markAsRead');

        $this->load->view('zoho_mail/message', [
            'account'   => $acc,
            'folderId'  => $folderId,
            'messageId' => $messageId,
            'meta'      => $meta['ok'] ? ($meta['json']['data'] ?? null) : null,
            'content'   => $content['ok'] ? ($content['json']['data'] ?? null) : null,
            'apiError'  => ($meta['ok'] && $content['ok']) ? null : ($meta['error'] ?? $content['error'] ?? 'API error'),
        ]);
    }

    public function compose()
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        $this->load->view('zoho_mail/compose', [
            'account' => $acc,
            'prefill' => [
                'to'      => trim((string) $this->input->get('to')),
                'subject' => trim((string) $this->input->get('subject')),
                'body'    => trim((string) $this->input->get('body')),
            ],
        ]);
    }

    public function send()
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        $uid = $this->_user_id();

        $payload = [
            'toAddress'  => trim((string) $this->input->post('toAddress')),
            'ccAddress'  => trim((string) $this->input->post('ccAddress')),
            'bccAddress' => trim((string) $this->input->post('bccAddress')),
            'subject'    => trim((string) $this->input->post('subject')),
            'content'    => (string) $this->input->post('content'),
            'mailFormat' => 'html',
        ];
        if ($payload['toAddress'] === '' || $payload['subject'] === '') {
            $this->session->set_flashdata('danger', 'To and Subject are required.');
            redirect('ZohoMail/compose');
            return;
        }

        // Optional file attachments
        $attachments = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $files = $_FILES['attachments'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $up = $this->zoho_mail->uploadAttachment($uid, $files['tmp_name'][$i], $files['name'][$i]);
                if ($up['ok'] && !empty($up['json']['data'][0])) {
                    $attachments[] = $up['json']['data'][0];
                }
            }
        }
        if (!empty($attachments)) $payload['attachments'] = $attachments;

        $resp = $this->zoho_mail->sendMessage($uid, $payload);
        if ($resp['ok']) {
            $this->session->set_flashdata('success', 'Email sent.');
            redirect('ZohoMail/inbox');
        } else {
            $this->session->set_flashdata('danger', 'Send failed: ' . ($resp['error'] ?? json_encode($resp['json'] ?? null)));
            redirect('ZohoMail/compose');
        }
    }

    public function attachment($folderId = null, $messageId = null, $attId = null, $name = null)
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        if (!$folderId || !$messageId || !$attId) { show_404(); return; }

        $resp = $this->zoho_mail->downloadAttachment($this->_user_id(), $folderId, $messageId, $attId);
        if (!$resp['ok']) { show_error('Attachment fetch failed: ' . ($resp['error'] ?? 'unknown')); return; }

        $contentType = $resp['headers']['content-type'] ?? 'application/octet-stream';
        $filename = $name ?: ('attachment-' . $attId);
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . rawurldecode($filename) . '"');
        echo $resp['body'];
    }

    public function messageAction()
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        $uid = $this->_user_id();

        $mode = trim((string) $this->input->post('mode'));
        $ids  = $this->input->post('messageIds');
        if (!is_array($ids)) $ids = array_filter(array_map('trim', explode(',', (string) $ids)));
        if (empty($ids) || $mode === '') {
            $this->session->set_flashdata('danger', 'Pick at least one message and an action.');
            redirect('ZohoMail/inbox');
            return;
        }

        if ($mode === 'move') {
            $dest = trim((string) $this->input->post('destFolderId'));
            $resp = $this->zoho_mail->moveMessages($uid, $ids, $dest);
        } else {
            $resp = $this->zoho_mail->updateMessage($uid, $ids, $mode);
        }

        if ($resp['ok']) {
            $this->session->set_flashdata('success', 'Action completed.');
        } else {
            $this->session->set_flashdata('danger', 'Action failed: ' . ($resp['error'] ?? 'unknown'));
        }
        redirect('ZohoMail/inbox');
    }

    public function folderAction()
    {
        $acc = $this->_ensureConnected();
        if (!$acc) return;
        $uid = $this->_user_id();

        $mode = trim((string) $this->input->post('mode'));
        if ($mode === 'create') {
            $name = trim((string) $this->input->post('folderName'));
            $parent = trim((string) $this->input->post('parentFolderId'));
            if ($name === '') { $this->session->set_flashdata('danger', 'Folder name required.'); redirect('ZohoMail/inbox'); return; }
            $resp = $this->zoho_mail->createFolder($uid, $name, $parent ?: null);
        } elseif ($mode === 'delete') {
            $fid = trim((string) $this->input->post('folderId'));
            if ($fid === '') { $this->session->set_flashdata('danger', 'Folder ID required.'); redirect('ZohoMail/inbox'); return; }
            $resp = $this->zoho_mail->deleteFolder($uid, $fid);
        } else {
            $this->session->set_flashdata('danger', 'Unknown folder action.');
            redirect('ZohoMail/inbox');
            return;
        }

        if ($resp['ok']) $this->session->set_flashdata('success', 'Folder action completed.');
        else             $this->session->set_flashdata('danger', 'Folder action failed: ' . ($resp['error'] ?? 'unknown'));
        redirect('ZohoMail/inbox');
    }
}
