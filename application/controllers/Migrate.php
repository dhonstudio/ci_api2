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
        $this->migration_file       = 'apiusers'; // same with migration created
        $this->migration_version    = '20220421141007'; // same with migration created

        require_once APPPATH . 'libraries/DhonMigrate.php';
        $this->dhonmigrate = new DhonMigrate(['database' => $database]);
        $this->dhonmigrate->version = $this->migration_version;
    }

    public function index()
    {
        $this->migration_method == 'one' ? $this->dhonmigrate->migrate($this->migration_file) : '';
    }

    public function create(string $migration_name)
    {
        $this->dhonmigrate->create($migration_name);
    }
}
