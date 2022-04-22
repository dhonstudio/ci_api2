<?php
date_default_timezone_set('Asia/Jakarta');

class Migrate extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /*
        | ------------------------------------------------------------------
        |  Set up Migration Database, Method, File, and Version
        | ------------------------------------------------------------------
        */
        $database                   = 'project';
        $this->migration_method     = 'one'; // one | all
        $this->migration_file       = 'api_users'; // same with migration file created
        $this->migration_version    = '20220422145307'; // same with migration file created

        require_once APPPATH . 'libraries/DhonMigrate.php';
        $this->dhonmigrate = new DhonMigrate(['database' => $database]);
    }

    public function index()
    {
        $this->dhonmigrate->version = $this->migration_version;
        $this->migration_method == 'one' ? $this->dhonmigrate->migrate($this->migration_file) : '';
    }

    public function create(string $migration_name, string $dev = '')
    {
        $this->dhonmigrate->create($migration_name, $dev);
    }
}
