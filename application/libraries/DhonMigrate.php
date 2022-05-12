<?php

class DhonMigrate
{
    protected $database;
    public $version;
    protected $db;
    protected $dbforge;
    public $table;
    protected $constraint;
    protected $ai;
    protected $unique;
    protected $default;
    protected $fields = [];

    public function __construct(array $params)
    {
        $this->dhonmigrate = &get_instance();

        require_once APPPATH . 'libraries/DhonJson.php';
        $this->dhonjson = new DhonJson;

        $this->database = $params['database'];
        include APPPATH . "config/production/database.php";

        if (!in_array($this->database, array_keys($db))) {
            $status     = 404;
            $message    = 'Database name not found';
            $this->dhonjson->send(['status' => $status, 'message' => $message]);
        }

        $this->load = $this->dhonmigrate->load;

        $this->load->dbutil();
        if (ENVIRONMENT == 'development' && !$this->dhonmigrate->dbutil->database_exists($db[$this->database]['database'])) {
            if (isset($params['database_dev'])) {
                $status     = 417;
                $message    = 'Migration success, but development database migration not success';
            } else {
                $status     = 404;
                $message    = 'Database not found';
            }
            $this->dhonjson->send(['status' => $status, 'message' => $message]);
        }

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
        if ($this->ai === TRUE)         $field_data['auto_increment']   = $this->ai;
        if ($this->unique === TRUE)     $field_data['unique']           = $this->unique;
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
        $this->ai = FALSE;
        $this->unique = FALSE;
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
     * Initialize key
     *
     * @param	string  $field_name
     * @return	void
     */
    public function add_index(string $field_name)
    {
        $this->dbforge->add_key($field_name);
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
                $status     = 406;
                $message    = "Table `{$this->table}` exist";

                $this->dhonjson->send(['status' => $status, 'message' => $message]);
            }
        }
        $this->dbforge->add_field($this->fields);
        $this->dbforge->create_table($this->table);

        $this->fields = [];
    }

    /**
     * Relation between table
     *
     * @param	int     $number
     * @param	string  $foreign_key
     * @param	string  $relation_table
     * @param	string  $primary_key
     * @return	void
     */
    public function relate(int $number, string $foreign_key, string $relation_table, string $primary_key)
    {
        $this->db->query("ALTER TABLE `{$this->table}` ADD CONSTRAINT `{$this->table}_ibfk_{$number}` FOREIGN KEY (`{$foreign_key}`) REFERENCES `{$relation_table}`(`{$primary_key}`) ON DELETE CASCADE ON UPDATE CASCADE");
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
     * @param	string  $action optional ('' | 'change' | 'drop')
     * @return	void
     */
    public function migrate(string $classname, string $action = '')
    {
        // $path = ENVIRONMENT == 'testing' || ENVIRONMENT == 'development' ? "\\" : "/";
        $migration_file = APPPATH . "migrations/{$this->version}_{$classname}.php";

        if (!file_exists($migration_file)) {
            $status     = 404;
            $message    = 'Migration file not found';
            $this->dhonjson->send(['status' => $status, 'message' => $message]);
        }

        require $migration_file;

        $migration_name = "Migration_{$classname}";
        $migration      = new $migration_name(['database' => $this->database]);

        $this->table = 'migrations';
        $this->constraint(20)->field('version', 'BIGINT');
        if (!$this->db->table_exists($this->table)) {
            $this->create_table();
        }
        $this->db->insert($this->table, ['version' => date('YmdHis', time())]);

        $action == 'change' ? $migration->change() : ($action == 'drop' ? $migration->drop() : ($action == 'relate' ? $migration->relate() : $migration->up()));

        $status     = 200;
        $message    = 'Migration success';
        $this->dhonjson->send(['status' => $status, 'message' => $message]);
    }

    /**
     * Create migration file
     *
     * @param	string  $migration_name
     * @param	string  $dev optional ('dev' | '')
     * @return	void
     */
    public function create(string $migration_name, string $dev = '')
    {
        $this->load->helper('file');

        $folder_location = APPPATH . 'migrations/';
        if (!is_dir($folder_location)) {
            mkdir($folder_location, 0777, true);
        }
        $timestamp      = date('YmdHis_', time());
        $file_location  = $folder_location . $timestamp . $migration_name . '.php';
        fopen($file_location, "w");

        $create_dev = $dev == 'dev' ? "false" : "true";

        $data = "<?php

class Migration_" . ucfirst($migration_name) . "
{
    public function __construct(array \$params)
    {
        \$this->database = \$params['database'];
        \$this->dev      = $create_dev;

        require_once APPPATH . 'libraries/DhonMigrate.php';
        \$this->dhonmigrate = new DhonMigrate(['database' => \$this->database]);
    }
    
    public function up()
    {
        \$this->dhonmigrate->table = '$migration_name';
        \$this->dhonmigrate->ai()->field('id_user', 'INT');
        \$this->dhonmigrate->constraint('100')->unique()->field('username', 'VARCHAR');
        \$this->dhonmigrate->constraint('200')->field('password', 'VARCHAR');
        \$this->dhonmigrate->field('created_at', 'INT');
        \$this->dhonmigrate->field('updated_at', 'INT');
        \$this->dhonmigrate->add_key('id_user');
        \$this->dhonmigrate->create_table();

        \$this->dhonmigrate->insert(['username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT)]);

        if (\$this->dev == false) \$this->_dev('up');
    }

    private function _dev(string \$next)
    {
        \$this->dhonmigrate = new DhonMigrate(['database' => \$this->database . '_dev', 'database_dev' => true]);
        \$this->dev = true;
        \$next == 'up' ? \$this->up()
            : (\$next == 'change' ? \$this->change()
                : (\$next == 'drop' ? \$this->drop()
                    : \$this->relate()
                )
            );
    }

    public function change()
    {
        # code...

        if (\$this->dev == false) \$this->_dev('change');
    }

    public function drop()
    {
        # code...

        if (\$this->dev == false) \$this->_dev('drop');
    }

    public function relate()
    {
        # code...

        if (\$this->dev == false) \$this->_dev('relate');
    }
}
        ";
        write_file($file_location, $data, 'r+');

        $status     = 200;
        $message    = 'Migration file successfully created';
        $this->dhonjson->send(['status' => $status, 'message' => $message]);
    }
}
