<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => '298825430560300',
  'app_secret' => '4ac8cfdbd7eb8f655606244856d7e610',
  'default_graph_version' => 'v2.9',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email','publish_actions']; // optional
	
try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }
if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		// getting short-lived access token
		$_SESSION['facebook_access_token'] = (string) $accessToken;
	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
		// setting default access token to be used in script
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
    // validating the access token
	try {
		$request = $fb->get('/me');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		if ($e->getCode() == 190) {
		 	unset($_SESSION['facebook_access_token']);
			$helper = $fb->getRedirectLoginHelper();
			$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
			echo "<script>window.top.location.href='".$loginUrl."'</script>";
			exit;
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
// posting on user timeline using publish_actins permission       can chane for testing only $message to my message or what evereverything after the link is optional
	try {
		// message must come from the user-end
		$data = ['link' => 'http://livewebsite.org/sitebuilder/index.php', 'message' => 'link with your message', 'description' => 'link with discription here you can use to put optimize your link for google'];
		$request = $fb->post('/me/feed', $data);
		$response = $request->getGraphNode()->asArray;
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	echo $response['id']; 				

  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper('http://livewebsite.org/Facebook/index.php/');
	$loginUrl = $helper->getLoginUrl('http://livewebsite.org/Facebook/index.php/', $permissions);
	echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
	
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}



