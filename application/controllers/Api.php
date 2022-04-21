<?php
defined('BASEPATH') or exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*
        | -------------------------
        |  Set up API Auth, and API User Database
        | -------------------------
        */
        $this->api_auth = 'basic';
        $this->api_db   = 'project';

        require_once APPPATH . 'libraries/DhonJson.php';
        $this->dhonjson = new DhonJson;
    }

    public function index()
    {
        // unset($_SERVER['PHP_AUTH_USER']);

        $this->api_auth == 'basic' ? $this->dhonjson->basic_auth($this->api_db) : false;
        $this->dhonjson->collect();
    }
}
