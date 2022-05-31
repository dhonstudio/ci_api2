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
        $this->dhonmigrate->field('created_at', 'DATETIME');
        $this->dhonmigrate->field('updated_at', 'DATETIME');
        $this->dhonmigrate->add_key('id_user');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->insert(['username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT)]);

        if ($this->dev == false) $this->_dev('up');
    }

    private function _dev(string $next)
    {
        $this->dhonmigrate = new DhonMigrate(['database' => $this->database . '_dev', 'database_dev' => true]);
        $this->dev = true;
        $next == 'up' ? $this->up()
            : ($next == 'change' ? $this->change()
                : ($next == 'drop' ? $this->drop()
                    : $this->relate()
                )
            );
    }

    public function change()
    {
        # code...

        if ($this->dev == false) $this->_dev('change');
    }

    public function drop()
    {
        # code...

        if ($this->dev == false) $this->_dev('drop');
    }

    public function relate()
    {
        $table_indexed  = 'indexed_table';
        $relations      = [
            [
                'foreign_key' => 'foreign_key',
                'relation_table' => 'relation_table',
                'primary_key' => 'primary_key'
            ],
        ];

        foreach ($relations as $key => $value) {
            $this->dhonmigrate->table = $table_indexed;
            $this->dhonmigrate->relate($key + 1, $value['foreign_key'], $value['relation_table'], $value['primary_key']);
        }

        if ($this->dev == false) $this->_dev('relate');
    }
}
