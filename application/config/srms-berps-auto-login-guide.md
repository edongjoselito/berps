# SRMS → BERPS Auto Login Guide
# Folers : srms-college/ and berps/ 
## Goal
Allow a logged-in user in **SRMS Admin** to click a link and open **BERPS** directly for:
- Payments
- Support
- Invoice pages

Both systems are separate applications with separate databases, but they are hosted under the **same domain / cPanel account**.

---

## Recommended Approach
Use a **one-time SSO token**.

This is the safest and most practical setup for your case.

### Flow
1. Admin User logs in to **SRMS**
2. Admin User clicks **Open BERPS Payments** or **Open BERPS Support** from Admin sidebar
3. SRMS generates a secure random token
4. SRMS inserts that token into the **BERPS database**
5. SRMS redirects the browser to BERPS with the token
6. BERPS validates the token
7. BERPS creates its own session
8. BERPS redirects the user to the target page

Example redirect:

```php
https://yourdomain.com/berps/sso/login?token=xxxxxxxx
```
we will test using localhost first.
---

## Why this is best
Because the systems are:
- separate apps
- separate databases
- same hosting / same domain

This avoids:
- sharing raw passwords
- trying to force shared sessions across two CI3 apps
- weak URL-based login hacks

---

## BERPS Database Table

Create this table in the **BERPS database**:

```sql
CREATE TABLE sso_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(128) NOT NULL,
    source_system VARCHAR(30) NOT NULL,
    source_user_id VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    target_url VARCHAR(255) NOT NULL,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    INDEX(token)
);
```

---

# SRMS Side

## 1. Add BERPS database connection
In `application/config/database.php`

```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'srms_db_user',
    'password' => 'srms_db_pass',
    'database' => 'srms_db',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
);

$db['berps'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'berps_db_user',
    'password' => 'berps_db_pass',
    'database' => 'berps_db',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
);
```

---

## 2. SRMS model
File: `application/models/Berps_sso_model.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Berps_sso_model extends CI_Model
{
    protected $berps_db;

    public function __construct()
    {
        parent::__construct();
        $this->berps_db = $this->load->database('berps', TRUE);
    }

    public function insert_token($data)
    {
        return $this->berps_db->insert('sso_tokens', $data);
    }
}
```

---

## 3. SRMS controller
File: `application/controllers/Sso.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sso extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Berps_sso_model');
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function berps_payments()
    {
        if (!$this->session->userdata('username')) {
            show_error('Unauthorized access', 401);
        }

        $token = bin2hex(random_bytes(32));

        $username = $this->session->userdata('username');
        $email    = $this->session->userdata('email');
        $idNumber = $this->session->userdata('IDNumber');

        $save = [
            'token'          => $token,
            'source_system'  => 'srms',
            'source_user_id' => !empty($idNumber) ? $idNumber : $username,
            'username'       => $username,
            'email'          => $email,
            'target_url'     => 'Page/clientProfile?tab=invoices',
            'is_used'        => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'expires_at'     => date('Y-m-d H:i:s', strtotime('+2 minutes')),
        ];

        $this->Berps_sso_model->insert_token($save);

        redirect('https://yourdomain.com/berps/sso/login?token=' . urlencode($token));
    }

    public function berps_support()
    {
        if (!$this->session->userdata('username')) {
            show_error('Unauthorized access', 401);
        }

        $token = bin2hex(random_bytes(32));

        $username = $this->session->userdata('username');
        $email    = $this->session->userdata('email');
        $idNumber = $this->session->userdata('IDNumber');

        $save = [
            'token'          => $token,
            'source_system'  => 'srms',
            'source_user_id' => !empty($idNumber) ? $idNumber : $username,
            'username'       => $username,
            'email'          => $email,
            'target_url'     => 'Support',
            'is_used'        => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'expires_at'     => date('Y-m-d H:i:s', strtotime('+2 minutes')),
        ];

        $this->Berps_sso_model->insert_token($save);

        redirect('https://yourdomain.com/berps/sso/login?token=' . urlencode($token));
    }
}
```

---

# BERPS Side

## 1. BERPS model
File: `application/models/Sso_model.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sso_model extends CI_Model
{
    public function get_valid_token($token)
    {
        $this->db->where('token', $token);
        $this->db->where('is_used', 0);
        $this->db->where('expires_at >=', date('Y-m-d H:i:s'));
        return $this->db->get('sso_tokens')->row();
    }

    public function mark_used($id)
    {
        return $this->db->where('id', $id)->update('sso_tokens', [
            'is_used' => 1,
            'used_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

---

## 2. BERPS controller
File: `application/controllers/Sso.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sso extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sso_model');
        $this->load->model('User_model'); // adjust to your actual user model
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function login()
    {
        $token = $this->input->get('token', TRUE);

        if (empty($token)) {
            show_error('Missing token', 400);
        }

        $row = $this->Sso_model->get_valid_token($token);

        if (!$row) {
            show_error('Invalid or expired token', 401);
        }

        $user = $this->User_model->get_by_username_or_email($row->username, $row->email);

        if (!$user) {
            show_error('No matching BERPS account found', 403);
        }

        $this->Sso_model->mark_used($row->id);

        $this->session->set_userdata([
            'user_id'   => $user->id,
            'username'  => $user->username,
            'name'      => $user->name,
            'logged_in' => TRUE
        ]);

        redirect($row->target_url);
    }
}
```

---

## 3. BERPS user lookup
Example function in your BERPS user model:

```php
public function get_by_username_or_email($username, $email)
{
    $this->db->group_start();
    $this->db->where('username', $username);
    if (!empty($email)) {
        $this->db->or_where('email', $email);
    }
    $this->db->group_end();
    return $this->db->get('users')->row();
}
```

If both systems use the same username, that is enough.

---

# SRMS Buttons

## Admin-Only Access

The BERPS SSO links are restricted to **SRMS Admin users only**. Non-Admin users will get a 403 error.

## Sidebar Links Added

The following links were added to `application/views/includes/sidebar.php` (Admin section only):

```php
<li class="menu-title">BERPS INTEGRATION</li>

<li>
    <a href="javascript: void(0);" class="waves-effect">
        <i class="fas fa-external-link-alt"></i>
        <span> Open BERPS </span>
        <span class="menu-arrow"></span>
    </a>
    <ul class="nav-second-level" aria-expanded="false">
        <li><a href="<?= base_url('sso/berps_payments'); ?>" target="_blank">Payments</a></li>
        <li><a href="<?= base_url('sso/berps_support'); ?>" target="_blank">Support</a></li>
        <li><a href="<?= base_url('sso/berps_invoices'); ?>" target="_blank">Invoices</a></li>
    </ul>
</li>
```

These links appear in the Admin sidebar menu and open BERPS in a new tab.

---

# Important Notes

## Do not do this
Do **not**:
- pass username/password in URL
- pass plain user id only
- use permanent tokens
- use weak hashes like `md5(username)`
- allow token reuse

## Best practice
- token expiry = 1 to 2 minutes
- one-time use only
- use HTTPS
- validate destination pages
- validate BERPS permissions after login

---

# Shared Session Warning
Even if both apps are on the same domain, **do not rely on shared sessions** unless both apps are intentionally built to share:
- same session cookie name
- same encryption key
- same session driver
- same session storage

For legacy CI3 apps, token-based SSO is the safer method.

---

# Best Final Setup
For your exact setup:

- SRMS and BERPS are separate systems
- separate databases
- same domain / same cPanel

### Best solution:
**SRMS inserts a one-time token into BERPS DB, then redirects to BERPS login bridge.**

That is the cleanest and safest implementation.

---

# Optional next step
Later, you can extend this to:
- open a specific invoice page
- open support tickets page
- open student billing page
- log audit trail of cross-system login

Example target:

```php
'target_url' => 'Page/invoice?id=1968'
```

Just make sure BERPS still verifies that the logged-in user is allowed to view that page.

---

# Access Control

## Only SRMS Admin Can Access BERPS

The SSO links are restricted to **SRMS Admin users only**.

```php
// In SRMS Sso.php controller
private function _is_admin()
{
    $level = $this->session->userdata('level');
    return strtolower(trim((string) $level)) === 'admin';
}
```

If a non-Admin user tries to access BERPS SSO, they will see:
> "Only SRMS Admin users can access BERPS"

---

# SSO Debug Tools

## SRMS Debug Tool

Access: `http://localhost/srms-college/sso_debug.php`

Shows:
- Current SRMS session data (username, email, level, etc.)
- What will be sent to BERPS for SSO
- Test SSO links
- Fix suggestions

## BERPS Debug Tool

Access: `http://localhost/berps/sso_debug.php`

Shows:
- BERPS users table
- Search for specific username/email
- Recent SSO tokens

---

# Troubleshooting

## "No matching BERPS account found" Error

This error occurs when the username/email from SRMS doesn't match any user in BERPS.

### Causes & Solutions:

1. **Username mismatch**
   - SRMS username: `john.doe`
   - BERPS username: `john.doe@school.edu`
   - **Solution**: Update BERPS user to match SRMS username, or vice versa

2. **Case sensitivity**
   - The matching is now case-insensitive (updated in User_model.php)

3. **Extra spaces**
   - The matching now trims whitespace (updated in User_model.php)

4. **Different email addresses**
   - SRMS email might be different from BERPS email
   - **Solution**: Ensure both systems have the same email for the user

### Debug Tool

Use the debug tool to check user matching:
```
http://localhost/berps/sso_debug.php
```

This tool shows:
- All BERPS users
- Search for specific username/email
- Recent SSO tokens

### Check Error Logs

If SSO fails, check the CodeIgniter error logs:
```
/berps/application/logs/log-YYYY-MM-DD.php
```

The SSO controller now logs failed matching attempts with details.

### Manual User Lookup Test

Run this SQL in phpMyAdmin to check if a user exists:

```sql
-- Check BERPS for a user
SELECT user_id, username, email, IDNumber, position
FROM users
WHERE LOWER(TRIM(username)) = 'john.doe'
   OR LOWER(TRIM(email)) = 'john.doe@school.edu'
   OR LOWER(TRIM(IDNumber)) = 'john.doe';
```

### Common Fixes

1. **Update BERPS username to match SRMS:**
```sql
UPDATE users SET username = 'john.doe' WHERE email = 'john.doe@school.edu';
```

2. **Update SRMS user email to match BERPS:**
```sql
-- In SRMS database
UPDATE o_users SET email = 'john.doe@school.edu' WHERE username = 'john.doe';
```

3. **Check email matching:**
   - If username doesn't match, the system also tries email
   - Ensure both systems have the same email for the user

---

# User Matching Summary

For successful SSO, at least ONE of these must match between SRMS and BERPS:

| Field | SRMS Table | BERPS Table | Notes |
|-------|-----------|-------------|-------|
| Username | `o_users.username` | `users.username` | Case-insensitive, trimmed |
| Email | `o_users.email` | `users.email` | Case-insensitive, trimmed |

If none match, SSO will fail with "No matching BERPS account found."

**Note:** IDNumber matching is not used because the BERPS `users` table does not have an `IDNumber` column.

---

# BERPS-Specific School Settings

To avoid conflicts with SRMS, BERPS now has its own `settings` table separate from SRMS's `o_srms_settings`.

## Database Table

Run this SQL to create the BERPS settings table:

```sql
-- File: /berps/database/2026_04_19_create_settings_table.sql
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(255) NOT NULL DEFAULT 'My School',
    school_address TEXT,
    school_contact VARCHAR(100),
    school_email VARCHAR(150),
    login_banner_image VARCHAR(255),
    letterhead_image VARCHAR(255),
    active_semester VARCHAR(50),
    active_school_year VARCHAR(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Files Created

| File | Purpose |
|------|---------|
| `application/models/Settings_model.php` | Model to access BERPS settings |
| `application/controllers/Settings.php` | Added `berps_school_info()` method |
| `application/views/settings_berps_school_info.php` | Settings form view |

## Access URL

Access BERPS school settings at:
```
http://localhost/berps/Settings/berps_school_info
```

## Usage in Code

```php
$this->load->model('Settings_model');

// Get school name
$school_name = $this->Settings_model->get_school_name();

// Get all settings
$settings = $this->Settings_model->get_settings();

// Get active semester/school year
$term = $this->Settings_model->get_active_term();
echo $term['semester']; // e.g., "1st Semester"
echo $term['school_year']; // e.g., "2025-2026"
```

This keeps BERPS settings completely separate from SRMS, avoiding any conflicts.
