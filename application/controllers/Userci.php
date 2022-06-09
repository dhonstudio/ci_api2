<?php
defined('BASEPATH') or exit('No direct script access allowed');

date_default_timezone_set('Asia/Jakarta');

class Userci extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->dhonjson->db_name    = 'project';
        $this->dhonjson->table      = 'user_ci';
    }

    public function getAllUsers()
    {
        $this->dhonjson->sort   = true;
        $this->dhonjson->filter = true;
        $this->dhonjson->limit  = true;
        $this->dhonjson->collect();
    }

    public function getApiUsersByUsername()
    {
        $this->dhonjson->sort   = true;
        $this->dhonjson->filter = true;
        $this->dhonjson->limit  = true;
        $this->dhonjson->method = 'GET';
        $this->dhonjson->collect();
    }

    public function insert()
    {
        $this->dhonjson->method = 'POST';
        $this->dhonjson->error_duplicate = 'Email has been registered';
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
