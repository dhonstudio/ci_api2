<?php
$ci = get_instance();

$ci->root_path = ENVIRONMENT == 'development' ? "/../../../"
    : (ENVIRONMENT == 'testing' ? "/../../../../../" : "/../../../../");

require_once __DIR__ . $ci->root_path . 'assets/ci_libraries/DhonJSON.php';
$ci->dhonjson = new DhonJson;

/*
| -------------------------
|  Set up API Auth, and API User Database
| -------------------------
*/
$ci->dhonjson->basic_auth   = true; // true | false
$ci->dhonjson->api_db       = 'project'; // api_db filled by api_users for auth
