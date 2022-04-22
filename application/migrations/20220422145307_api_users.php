<?php

class Migration_Api_users
{
    public function __construct(array $params)
    {
        $this->database = $params['database'];
        $this->dev      = false;

        require_once APPPATH . 'libraries/DhonMigrate.php';
        $this->dhonmigrate = new DhonMigrate(['database' => $this->database]);
    }
    
    public function up()
    {
        $this->dhonmigrate->table = 'api_users';
        $this->dhonmigrate->ai()->field('id_user', 'INT');
        $this->dhonmigrate->constraint('100')->unique()->field('username', 'VARCHAR');
        $this->dhonmigrate->constraint('200')->field('password', 'VARCHAR');
        $this->dhonmigrate->field('created_at', 'INT');
        $this->dhonmigrate->field('updated_at', 'INT');
        $this->dhonmigrate->add_key('id_user');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->insert(['username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT)]);

        if ($this->dev == false) $this->_dev();
    }

    private function _dev()
    {
        $this->dhonmigrate = new DhonMigrate(['database' => $this->database . '_dev', 'database_dev' => true]);
        $this->dev = true;
        $this->up();
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
        