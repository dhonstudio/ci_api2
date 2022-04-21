<?php

class DhonMigrate
{
    public $version;
    protected $database;
    protected $db;
    protected $dbforge;
    public $table;
    protected $constraint;
    protected $unique;
    protected $ai;
    protected $default;
    protected $fields = [];

    public function __construct(array $params)
    {
        $this->dhonmigrate = &get_instance();

        $this->database = $params['database'];
        $this->load     = $this->dhonmigrate->load;
        $this->db       = $this->load->database($this->database, TRUE);
        $this->dbforge  = $this->load->dbforge($this->db, TRUE);
    }

    /**
     * Initialize maxlenght of column
     *
     * @param	string|int	$value
     * @return	$this
     */
    public function constraint($value)
    {
        $this->constraint = $value;
        return $this;
    }

    /**
     * Initialize auto-increment column
     *
     * @return	$this
     */
    public function ai()
    {
        $this->ai = TRUE;
        return $this;
    }

    /**
     * Initialize unique column
     *
     * @return	$this
     */
    public function unique()
    {
        $this->unique = TRUE;
        return $this;
    }

    /**
     * Initialize default value of column
     *
     * @param	string|int	$value
     * @return	$this
     */
    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Initialize one field
     *
     * @param	string|array	$field_name send array if want to change field [0 => 'old_fieldname', 1 => 'new_fieldname']
     * @param	string	        $type
     * @param	string	        $nullable optional ('nullable')
     * @return	void
     */
    public function field($field_name, string $type, string $nullable = '')
    {
        $field_data['type'] = $type;

        if ($this->constraint !== '')   $field_data['constraint']       = $this->constraint;
        if ($this->unique === TRUE)     $field_data['unique']           = $this->unique;
        if ($this->ai === TRUE)         $field_data['auto_increment']   = $this->ai;
        if ($this->default !== '')      $field_data['default']          = $this->default;
        if ($nullable === 'nullable')   $field_data['null']             = TRUE;

        if (is_array($field_name)) {
            $field_data['name'] = $field_name[1];

            $field_element = [
                $field_name[0] => $field_data
            ];
        } else {
            $field_element = [
                $field_name => $field_data
            ];
        }

        $this->fields = array_merge($this->fields, $field_element);
        $this->constraint = '';
        $this->unique = FALSE;
        $this->ai = FALSE;
        $this->default = '';
    }

    /**
     * Initialize primary key
     *
     * @param	string  $field_name
     * @return	void
     */
    public function add_key(string $field_name)
    {
        $this->dbforge->add_key($field_name, TRUE);
    }

    /**
     * To add new field on existing table
     *
     * @return	void
     */
    public function add_field()
    {
        $this->dbforge->add_column($this->table, $this->fields);

        $this->fields = [];
    }

    /**
     * To change field on existing table
     *
     * @return	void
     */
    public function change_field()
    {
        $this->dbforge->modify_column($this->table, $this->fields);

        $this->fields = [];
    }

    /**
     * To delete field on existing table
     *
     * @return	void
     */
    public function drop_field(string $field)
    {
        $this->dbforge->drop_column($this->table, $field);
    }

    /**
     * Create a table
     *
     * @param	string  $force optional ('force')
     * @return	void
     */
    public function create_table(string $force = '')
    {
        if ($this->db->table_exists($this->table)) {
            if ($force == 'force') {
                $this->dbforge->drop_table($this->table);
            } else {
                $response   = "failed";
                $status     = '304';
                $data       = ["Table `{$this->table}` exist"];

                $this->send($response, $status, $data);
            }
        }
        $this->dbforge->add_field($this->fields);
        $this->dbforge->create_table($this->table);

        $this->fields = [];
    }

    /**
     * Insert data to table
     *
     * @param	array  $value multidimentional array
     * @return	void
     */
    public function insert(array $value)
    {
        $fields = $this->db->list_fields($this->table);
        $values = in_array('created_at', $fields) ? array_merge($value, ['created_at' => time()]) : $value;
        $this->db->insert($this->table, $values);
    }

    /**
     * Do Migrate
     *
     * @param	string  $classname
     * @param	string  $action optional ('change', 'drop')
     * @return	void
     */
    public function migrate(string $classname, string $action = '')
    {
        // $path = ENVIRONMENT == 'testing' || ENVIRONMENT == 'development' ? "\\" : "/";
        require APPPATH . "migrations/{$this->version}_{$classname}.php";
        $migration_name = "Migration_{$classname}";
        $migration      = new $migration_name(['database' => $this->database]);

        $this->table = 'migrations';
        $this->constraint(20)->field('version', 'BIGINT');
        if (!$this->db->table_exists($this->table)) {
            $this->create_table();
        }
        $this->db->insert($this->table, ['version' => date('YmdHis', time())]);

        $action == 'change' ? $migration->change() : ($action == 'drop' ? $migration->drop() : $migration->up());

        $response   = 'Migration success';
        $status     = '200';
        $this->send($response, $status);
    }

    /**
     * Create migration file
     *
     * @param	string  $migration_name
     * @return	void
     */
    public function create(string $migration_name)
    {
        $this->load->helper('file');

        $folder_location = APPPATH . 'migrations/';
        if (!is_dir($folder_location)) {
            mkdir($folder_location, 0777, true);
        }
        $timestamp      = date('YmdHis_', time());
        // $timestamp      = '2022_';
        $file_location  = $folder_location . $timestamp . $migration_name . '.php';
        fopen($file_location, "w");

        $data = "<?php

class Migration_" . ucfirst($migration_name) . "
{
    public function __construct(array \$params)
    {
        \$this->database = \$params['database'];
        \$this->dev      = false;

        require_once APPPATH . 'libraries/DhonMigrate.php';
        \$this->dhonmigrate = new DhonMigrate(['database' => \$this->database]);
    }
    
    public function up()
    {
        \$this->dhonmigrate->table = 'api_users';
        \$this->dhonmigrate->ai()->field('id_user', 'INT');
        \$this->dhonmigrate->constraint('100')->unique()->field('username', 'VARCHAR');
        \$this->dhonmigrate->constraint('200')->field('password', 'VARCHAR');
        \$this->dhonmigrate->field('created_at', 'INT');
        \$this->dhonmigrate->field('updated_at', 'INT');
        \$this->dhonmigrate->add_key('id_user');
        \$this->dhonmigrate->create_table();

        \$this->dhonmigrate->insert(['username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT)]);

        \$this->_dev();
    }

    private function _dev()
    {
        \$this->dhonmigrate = new DhonMigrate(['database' => \$this->database . '_dev']);
        \$this->dev = true;
        \$this->up();
    }

    public function change()
    {
        # code...
    }

    public function drop()
    {
        # code...
    }
}
        ";
        write_file($file_location, $data, 'r+');
    }

    /**
     * Send return as JSON
     *
     * @param	string  $response
     * @param	int     $status
     * @param	array   $data optional
     * @return	echo json_encode
     */
    private function send(string $response, int $status, array $data = [])
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $json_response = ['response' => $response, 'status' => $status];
        if (count($data) > 0) $json_response['data'] = $data;
        echo json_encode($json_response, JSON_NUMERIC_CHECK);
        exit;
    }
}
