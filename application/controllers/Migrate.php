<?php
date_default_timezone_set('Asia/Jakarta');

class Migrate extends CI_Controller
{
    /*
    | ------------------------------------------------------------------
    |  Set up Migration Database and Action
    | ------------------------------------------------------------------
    */
    public $migration_database  = 'project'; // fill with migration database
    protected $migration_action = 'up'; // 'up' | 'change' | 'drop'

    public $root_path;
    protected $dhonmigrate;

    public function __construct()
    {
        parent::__construct();

        require_once __DIR__ . $this->root_path . 'assets/ci_libraries/DhonMigrate-1.0.0.php';
        $this->dhonmigrate = new DhonMigrate();
    }

    public function create(string $migration_name)
    {
        $this->dhonmigrate->create($migration_name);
    }

    public function index()
    {
        $migration_file     = 'hit';
        $migration_version  = '20220610100646';
        $this->dhonmigrate->migrate($migration_version, $migration_file, $this->migration_action);
    }
}
