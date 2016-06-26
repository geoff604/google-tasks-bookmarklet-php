<?
require_once '/home/gpeters/tasksapiapp/vendor/autoload.php';

define('APPLICATION_NAME', 'Geoff Tasks');
define('CREDENTIALS_PATH', '/home/gpeters/.credentials/tasks-php-gpeters.json');
define('CLIENT_SECRET_PATH', '/home/gpeters/tasksapiapp/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at CREDENTIALS_PATH.
define('SCOPES', implode(' ', array(
  Google_Service_Tasks::TASKS)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function registerAccessToken($authCode) {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');
  $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/tasks/auth.php');

  $credentialsPath = CREDENTIALS_PATH;
  // Exchange authorization code for an access token.
  $accessToken = $client->authenticate($authCode);

  // Store the credentials to disk.
  if(!file_exists(dirname($credentialsPath))) {
    mkdir(dirname($credentialsPath), 0700, true);
  }
  file_put_contents($credentialsPath, $accessToken);
}

if (isset($_GET['error'])) {
    print("Error");
} else if (isset($_GET['code'])) {
    registerAccessToken($_GET['code']);
    print("Logged in now, please try bookmarklet again");
} else {
    print("Sorry");
}
?>