<?php

class Migration_Hit
{
    protected $dhonmigrate;

    public function __construct(array $params)
    {
        $root_path = $params['root_path'];
        require_once __DIR__ . $root_path . 'assets/ci_libraries/DhonMigrate-1.0.0.php';
        $this->dhonmigrate = new DhonMigrate();
    }

    public function up()
    {
        $this->dhonmigrate->table = 'dhonstudio_hit';
        $this->dhonmigrate->ai()->field('id_hit', 'INT');
        $this->dhonmigrate->field('address', 'INT', 'nullable');
        $this->dhonmigrate->field('entity', 'INT', 'nullable');
        $this->dhonmigrate->field('session', 'INT', 'nullable');
        $this->dhonmigrate->field('source', 'INT', 'nullable');
        $this->dhonmigrate->field('page', 'INT', 'nullable');
        $this->dhonmigrate->field('created_at', 'DATETIME');
        $this->dhonmigrate->add_key('id_hit');
        $this->dhonmigrate->add_index('address');
        $this->dhonmigrate->add_index('entity');
        $this->dhonmigrate->add_index('session');
        $this->dhonmigrate->add_index('source');
        $this->dhonmigrate->add_index('page');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->table = 'dhonstudio_source';
        $this->dhonmigrate->ai()->field('id_source', 'INT');
        $this->dhonmigrate->constraint('200')->unique()->field('source', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_source');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->table = 'dhonstudio_address';
        $this->dhonmigrate->ai()->field('id_address', 'INT');
        $this->dhonmigrate->constraint('50')->unique()->field('ip_address', 'VARCHAR', 'nullable');
        $this->dhonmigrate->constraint('1500')->field('ip_info', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_address');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->table = 'dhonstudio_entity';
        $this->dhonmigrate->ai()->field('id', 'INT');
        $this->dhonmigrate->constraint('1000')->unique()->field('entity', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->table = 'dhonstudio_page';
        $this->dhonmigrate->ai()->field('id_page', 'INT');
        $this->dhonmigrate->constraint('100')->unique()->field('page', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_page');
        $this->dhonmigrate->create_table();

        $this->dhonmigrate->table = 'dhonstudio_session';
        $this->dhonmigrate->ai()->field('id_session', 'INT');
        $this->dhonmigrate->constraint('100')->unique()->field('session', 'VARCHAR', 'nullable');
        $this->dhonmigrate->add_key('id_session');
        $this->dhonmigrate->create_table();
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
        $table_indexed  = 'dhonstudio_hit';
        $relations      = [
            [
                'foreign_key' => 'address',
                'relation_table' => 'dhonstudio_address',
                'primary_key' => 'id_address'
            ],
            [
                'foreign_key' => 'entity',
                'relation_table' => 'dhonstudio_entity',
                'primary_key' => 'id'
            ],
            [
                'foreign_key' => 'session',
                'relation_table' => 'dhonstudio_session',
                'primary_key' => 'id_session'
            ],
            [
                'foreign_key' => 'source',
                'relation_table' => 'dhonstudio_source',
                'primary_key' => 'id_source'
            ],
            [
                'foreign_key' => 'page',
                'relation_table' => 'dhonstudio_page',
                'primary_key' => 'id_page'
            ],
        ];

        foreach ($relations as $key => $value) {
            $this->dhonmigrate->table = $table_indexed;
            $this->dhonmigrate->relate($key + 1, $value['foreign_key'], $value['relation_table'], $value['primary_key']);
        }
    }
}
