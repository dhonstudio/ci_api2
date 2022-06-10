<?php
defined('BASEPATH') or exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');

class Hit extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->dhonjson->db_name    = 'project';
    }

    public function getAddressByIP()
    {
        $this->dhonjson->table  = 'dhonstudio_address';
        $this->dhonjson->method = 'GET';
        $this->dhonjson->collect();
    }

    public function postAddress()
    {
        $this->dhonjson->table  = 'dhonstudio_address';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function getAllEntities()
    {
        $this->dhonjson->table  = 'dhonstudio_entity';
        $this->dhonjson->collect();
    }

    public function postEntity()
    {
        $this->dhonjson->table  = 'dhonstudio_entity';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function getSession()
    {
        $this->dhonjson->table  = 'dhonstudio_session';
        $this->dhonjson->method = 'GET';
        $this->dhonjson->collect();
    }

    public function postSession()
    {
        $this->dhonjson->table  = 'dhonstudio_session';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function getSource()
    {
        $this->dhonjson->table  = 'dhonstudio_source';
        $this->dhonjson->method = 'GET';
        $this->dhonjson->collect();
    }

    public function postSource()
    {
        $this->dhonjson->table  = 'dhonstudio_source';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function getPage()
    {
        $this->dhonjson->table  = 'dhonstudio_page';
        $this->dhonjson->method = 'GET';
        $this->dhonjson->collect();
    }

    public function postPage()
    {
        $this->dhonjson->table  = 'dhonstudio_page';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function postHit()
    {
        $this->dhonjson->table  = 'dhonstudio_hit';
        $this->dhonjson->method = 'POST';
        $this->dhonjson->collect();
    }

    public function update($id = '')
    {
        if ($id) {
            $this->dhonjson->method = 'PUT';
            $this->dhonjson->id     = $id;
            $this->dhonjson->collect();
        } else {
            $this->dhonjson->send(['status' => 405]);
        }
    }

    public function deleteApiUsers($id = '')
    {
        if ($id) {
            $this->dhonjson->method = 'DELETE';
            $this->dhonjson->id     = $id;
            $this->dhonjson->collect();
        } else {
            $this->dhonjson->send(['status' => 405]);
        }
    }

    public function passwordVerify()
    {
        $this->dhonjson->command = 'password_verify';
        $this->dhonjson->collect();
    }
}
