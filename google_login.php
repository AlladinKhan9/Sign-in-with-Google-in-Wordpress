<?php 
// Load Google API Client Library
require_once 'vendor/autoload.php'; //change this path where your vendor folder created.

$clientID = '<YOUR_CLIENT_ID>';
$clientSecret = '<YOUR_CLIENT_SECRET>';
$redirectUri = '<REDIRECT_URI>'; //http://example.com/?googleAccount=google 

// Create a new Google Client
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");

// Handle Google logauthUrlin callback
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token['access_token']);
        // Get user information
        $service = new Google_Service_Oauth2($client);
        $user = $service->userinfo->get();   
        // Here, you can handle the user data returned from Google login.
        // You may want to create or authenticate the user in your WordPress database.
        $user_details = get_user_by('email', $user->email);
       
        if ( !is_wp_error( $user_details ) )
        {           
            wp_clear_auth_cookie();
            wp_set_current_user ( $user_details->ID );
            wp_set_auth_cookie  ( $user_details->ID );
            wp_redirect(home_url()); exit;
        } else {
            echo json_encode(array('error_code'=>1));
            exit();
        }

    } catch (Exception $e) {
    
        $error = PHP_EOL.PHP_EOL.'----------------Error----------------'.PHP_EOL.PHP_EOL;
        $error.= 'Error Messages Date : '.  date('d-m-Y H:i:s').PHP_EOL;
        $error.= 'Error Messages : '.  $e->getMessage().PHP_EOL;
        $error.= 'Error Line : '. $e->getLine().PHP_EOL;
        $error.= 'Error File : '. $e->getFile().PHP_EOL.PHP_EOL;
        $error.='----------------Error----------------';

        error_log( $error , 3, 'errors.log');
        // Handle any exceptions that occur during the login process
    }
}

// Generate Google login URL 
$authUrl = $client->createAuthUrl();
