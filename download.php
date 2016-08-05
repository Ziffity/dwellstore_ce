<?php

$loginSuccessful = false;

// Check username and password:
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    if ($username == 'dwell' && $password == 'apple') {
        $loginSuccessful = true;
    }
}

// Login passed successful?
if (!$loginSuccessful) {

    // The text inside the realm section will be visible for the 
    // user in the login box
    header('WWW-Authenticate: Basic realm="Authentication Required"');
    header('HTTP/1.0 401 Unauthorized');

    print "Login failed!\n";
    exit;
}


$file = "var/log/google_base_feed_default.log";

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
   ob_end_flush();
   $handle = fopen($file, "rb");
   while (!feof($handle)) {
     echo fread($handle, 1000);
   }
exit;
}
?>