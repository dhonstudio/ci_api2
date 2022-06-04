<?php
date_default_timezone_set('Asia/Jakarta');

class Migrate extends CI_Controller
{
    /*
    | ------------------------------------------------------------------
    |  Set up Migration Database, Method, File, and Version
    | ------------------------------------------------------------------
    */
    protected $migration_database   = 'project';
    protected $migration_method     = 'one'; // one | all
    protected $migration_file       = 'api_users'; // same with migration file created
    protected $migration_version    = '20220602143026'; // same with migration file created
    protected $migration_action     = ''; // '' | 'change' | 'drop' | 'relate'
    protected $dhonmigrate;

    public function __construct()
    {
        parent::__construct();

        require_once APPPATH . 'libraries/DhonMigrate.php';
        $this->dhonmigrate = new DhonMigrate(['database' => $this->migration_database]);
        $this->dhonmigrate->version = $this->migration_version;
    }

    public function index()
    {
        $this->migration_method == 'one' ? $this->dhonmigrate->migrate($this->migration_file, $this->migration_action) : false;
    }

    public function create(string $migration_name, string $dev = '')
    {
        $this->dhonmigrate->create($migration_name, $dev);
    }
}
