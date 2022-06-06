<?php
$ci = get_instance();

require_once APPPATH . 'libraries/DhonJson.php';
$ci->dhonjson = new DhonJson;

/*
| -------------------------
|  Set up API Auth, and API User Database
| -------------------------
*/
$ci->dhonjson->basic_auth   = true; // true | false
$ci->dhonjson->api_db       = 'project'; // api_db filled by api_users for auth
