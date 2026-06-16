<?php
session_start();

echo "<h2>Login Status Check</h2>";

echo "<h3>PHP Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>CodeIgniter Session Check:</h3>";
if (file_exists('application/libraries/Session.php')) {
    echo "CodeIgniter Session library exists<br>";
    
    // Try to load CodeIgniter
    if (file_exists('system/core/CodeIgniter.php')) {
        echo "CodeIgniter core exists<br>";
        
        // Try to get CI instance
        try {
            require_once 'system/core/CodeIgniter.php';
            $CI =& get_instance();
            if ($CI) {
                echo "CodeIgniter instance loaded<br>";
                echo "CI Session Data:<br>";
                echo "<pre>";
                print_r($CI->session->all_userdata());
                echo "</pre>";
            } else {
                echo "Could not get CI instance<br>";
            }
        } catch (Exception $e) {
            echo "Error loading CI: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "CodeIgniter core NOT found<br>";
    }
} else {
    echo "CodeIgniter Session library NOT found<br>";
}

echo "<h3>Current Status:</h3>";
if (empty($_SESSION)) {
    echo "<p style='color: red;'><strong>You are NOT logged in!</strong></p>";
    echo "<p>Please log in at: <a href='" . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']) . "login'>Login Page</a></p>";
} else {
    echo "<p style='color: green;'><strong>You appear to be logged in</strong></p>";
    echo "<p>Try accessing: <a href='" . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']) . "CustomerSupport'>Customer Support</a></p>";
}

echo "<br>";
echo "<a href='" . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']) . "login'>Go to Login</a>";
?>
