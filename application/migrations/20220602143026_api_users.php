<?php

class Migration_Api_users
{
    public function __construct(array $params)
    {
        $this->database = $params['database'];

        require_once APPPATH . 'libraries/DhonMigrate.php';
        $this->dhonmigrate = new DhonMigrate(['database' => $this->database]);
    }

    public function up()
    {
        $this->dhonmigrate->table = 'api_users';
        $this->dhonmigrate->ai()->field('id_user', 'INT');
        $this->dhonmigrate->constraint('100')->unique()->field('username', 'VARCHAR');
        $this->dhonmigrate->constraint('200')->field('password', 'VARCHAR');
        $this->dhonmigrate->default(0)->field('level', 'INT');
        $this->dhonmigrate->field('created_at', 'DATETIME');
        $this->dhonmigrate->field('updated_at', 'DATETIME');
        $this->dhonmigrate->add_key('id_user');
        $this->dhonmigrate->create_table('force');

        $this->dhonmigrate->insert(['username' => 'admin', 'password' => password_hash('admin', PASSWORD_DEFAULT), 'level' => 4]);
        $this->dhonmigrate->insert(['username' => 'no_access', 'password' => password_hash('admin', PASSWORD_DEFAULT), 'level' => 0]);
        $this->dhonmigrate->insert(['username' => 'only_get', 'password' => password_hash('admin', PASSWORD_DEFAULT), 'level' => 1]);
        $this->dhonmigrate->insert(['username' => 'only_getpost', 'password' => password_hash('admin', PASSWORD_DEFAULT), 'level' => 2]);
        $this->dhonmigrate->insert(['username' => 'only_getpostput', 'password' => password_hash('admin', PASSWORD_DEFAULT), 'level' => 3]);

        $this->dhonmigrate->table = 'api_log';
        $this->dhonmigrate->ai()->field('id_log', 'INT');
        $this->dhonmigrate->field('id_user', 'INT');
        $this->dhonmigrate->field('address', 'INT');
        $this->dhonmigrate->field('entity', 'INT');
        $this->dhonmigrate->field('session', 'INT');
        $this->dhonmigrate->field('endpoint', 'INT');
        $this->dhonmigrate->field('action', 'INT');
        $this->dhonmigrate->field('success', 'INT');
        $this->dhonmigrate->field('error', 'INT');
        $this->dhonmigrate->constraint('200')->field('message', 'VARCHAR');
        $this->dhonmigrate->field('created_at', 'DATETIME');
        $this->dhonmigrate->add_key('id_log');
        $this->dhonmigrate->add_index('id_user');
        $this->dhonmigrate->add_index('address');
        $this->dhonmigrate->add_index('entity');
        $this->dhonmigrate->add_index('session');
        $this->dhonmigrate->add_index('endpoint');
        $this->dhonmigrate->create_table('force');

        $this->dhonmigrate->table = 'api_address';
        $this->dhonmigrate->ai()->field('id_address', 'INT');
        $this->dhonmigrate->constraint('50')->unique()->field('ip_address', 'VARCHAR', 'nullable');
        $this->dhonmigrate->constraint('1500')->field('ip_info', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_address');
        $this->dhonmigrate->create_table('force');

        $this->dhonmigrate->table = 'api_entity';
        $this->dhonmigrate->ai()->field('id', 'INT');
        $this->dhonmigrate->constraint('1000')->unique()->field('entity', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id');
        $this->dhonmigrate->create_table('force');

        $this->dhonmigrate->table = 'api_session';
        $this->dhonmigrate->ai()->field('id_session', 'INT');
        $this->dhonmigrate->constraint('100')->unique()->field('session', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_session');
        $this->dhonmigrate->create_table('force');

        $this->dhonmigrate->table = 'api_endpoint';
        $this->dhonmigrate->ai()->field('id_endpoint', 'INT');
        $this->dhonmigrate->constraint('500')->unique()->field('endpoint', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_endpoint');
        $this->dhonmigrate->create_table('force');
    }

    public function change()
    {
        # code...
    }

    public function drop()
    {
        # code...
    }

    public function relate()
    {
        $table_indexed  = 'api_log';
        $relations      = [
            [
                'foreign_key' => 'id_user',
                'relation_table' => 'api_users',
                'primary_key' => 'id_user'
            ],
            [
                'foreign_key' => 'address',
                'relation_table' => 'api_address',
                'primary_key' => 'id_address'
            ],
            [
                'foreign_key' => 'entity',
                'relation_table' => 'api_entity',
                'primary_key' => 'id'
            ],
            [
                'foreign_key' => 'session',
                'relation_table' => 'api_session',
                'primary_key' => 'id_session'
            ],
            [
                'foreign_key' => 'endpoint',
                'relation_table' => 'api_endpoint',
                'primary_key' => 'id_endpoint'
            ],
        ];

        foreach ($relations as $key => $value) {
            $this->dhonmigrate->table = $table_indexed;
            $this->dhonmigrate->relate($key + 1, $value['foreign_key'], $value['relation_table'], $value['primary_key']);
        }
    }
}
