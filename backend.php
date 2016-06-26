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
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');
  $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/tasks/auth.php');

  // Load previously authorized credentials from a file.
  $credentialsPath = CREDENTIALS_PATH;
  if (file_exists($credentialsPath)) {
    $accessToken = file_get_contents($credentialsPath);
  } else {
    // Request authorization from the user.
    $client->setApprovalPrompt('force');
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
  }
  
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}

function getTaskLists($service) {
    $results = $service->tasklists->listTasklists();
    $taskLists = array();
    $currentItems = $results->getItems();

    $times = 0;
    while(count($currentItems) > 0 && $times < 10) {
        $taskLists = array_merge($taskLists, $currentItems);
        if (property_exists($results, 'nextPageToken') && !empty($results->nextPageToken)) {
            $optParams = array(
                'pageToken' => $results->nextPageToken
            );
            $results = $service->tasklists->listTasklists($optParams);
            $currentItems = $results->getItems();
        } else {
            break;
        }
        $times++;
    }

    $resultArray = array();
    foreach ($taskLists as $taskList) {
        $arrItem = array(
            'title' => $taskList->getTitle(),
            'id' => $taskList->getId()
        );
        array_push($resultArray, $arrItem);
    }

    function sort_by_order($a, $b) {
        if ($a['title'] < $b['title']) {
          return -1;
        }
        if ($a['title'] > $b['title']) {
          return 1;
        }
        // a must be equal to b
        return 0;
    }
    usort($resultArray, 'sort_by_order');

    header('Content-type: application/json');
    echo json_encode($resultArray);
    return;
}

// taskDate is a string in form: 2010-10-15
function addTask($service, $taskListId, $title, $note, $taskDate) {
    $task = new Google_Service_Tasks_Task();
    $task->setTitle($title);
    $task->setNotes($note);
    if (!empty($taskDate)) {
        $task->setDue($taskDate . 'T12:00:00.000Z');
    }
    $tasks = $service->tasks;
    $result = $tasks->insert($taskListId, $task);
    header('Content-type: application/json');
    echo json_encode($result);
    return;
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Tasks($client);

if (!isset($_GET['method'])) {
    print("Logged in, but no method provided");
    exit;
}
$method = $_GET['method'];
if ($method == 'getTaskLists') {
    getTaskLists($service);
} else if ($method == 'addTask') {
    addTask($service, $_GET['taskListId'], $_GET['title'], $_GET['note'], $_GET['taskDate']);
}
?>