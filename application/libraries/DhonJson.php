<?php

class DhonJson
{
    protected $db_name;
    protected $table;
    protected $id;
    protected $db;
    protected $db_total;
    protected $db_default;
    protected $fields;
    protected $json_response;
    protected $data;

    public function __construct()
    {
        $this->dhonjson = &get_instance();

        $this->load = $this->dhonjson->load;
        $this->uri  = $this->dhonjson->uri;

        $this->db_name  = $this->uri->segment(1);
        $this->table    = $this->uri->segment(2);
        $this->id       = $this->uri->segment(4);
    }

    /**
     * Authorize API User
     *
     * @param	string	$api_db_name
     * @return	void
     */
    public function basic_auth(string $api_db_name)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $api_db = $this->load->database($api_db_name, TRUE);

            if ($api_db->table_exists('api_users')) {
                $user = $api_db->get_where('api_users', ['username' => $_SERVER['PHP_AUTH_USER']])->row_array();
                if (!$user || !password_verify($_SERVER['PHP_AUTH_PW'], $user['password'])) {
                    $this->_unauthorized();
                }
            } else {
                $status     = 404;
                $message    = '';
                $this->send(['status' => $status, 'message' => $message]);
            }
        } else {
            $this->_unauthorized();
        }
    }

    private function _unauthorized()
    {
        $status     = 401;
        $message    = '';
        $this->send(['status' => $status, 'message' => $message]);
    }

    public function collect()
    {
        if ($this->db_name) {
            include APPPATH . "config/production/database.php";

            if (in_array($this->db_name, array_keys($db))) {
                $this->db           = $this->load->database($this->db_name, TRUE);
                $this->db_total     = $this->load->database($this->db_name, TRUE);
                $this->db_default   = $this->load->database($this->db_name, TRUE);

                if ($this->table && $this->db->table_exists($this->table)) {
                    $this->fields   = $this->db->list_fields($this->table);

                    $response   = 'OK';
                    $status     = 200;
                    $this->json_response = ['response' => $response, 'status' => $status];

                    if ($this->uri->segment(3) == 'password_verify') $this->password_verify();
                    else if ($_GET) $this->get_where();
                    else if ($_POST) $this->post();
                    else if ($this->uri->segment(3) == 'delete') $this->delete();
                    else $this->get();

                    $this->send();
                } else {
                    $status     = 404;
                    $message    = 'Not Found';
                }
            } else {
                $status     = 404;
                $message    = '';
            }
        } else {
            $status     = 500;
            $message    = '';
        }
        $this->send(['status' => $status, 'message' => $message]);
    }

    private function get()
    {
        $this->db = $this->db->get($this->table);
        $this->json_response['total']   = $this->db->num_rows();
        $this->json_response['data']    = $this->db->result_array();
    }

    private function get_where()
    {
        foreach ($_GET as $key => $value) {
            if (strpos($key, '__more') !== false) {
                $get_verified = str_replace("__more", "", $key);
                if (in_array($get_verified, $this->fields)) $get_where[$get_verified . ' >'] = $value;
            } else if (strpos($key, '__less') !== false) {
                $get_verified = str_replace("__less", "", $key);
                if (in_array($get_verified, $this->fields)) $get_where[$get_verified . ' <'] = $value;
            } else {
                if (in_array($key, $this->fields)) $get_where[$key] = $value;
            }
        }

        if (array_key_exists('sort_by', $_GET)) {
            $sort_by         = $_GET['sort_by'];
            $sort_method    = $_GET['sort_method'];

            $this->db         = $this->db->order_by($sort_by, $sort_method);
            $this->db_total    = $this->db_total->order_by($sort_by, $sort_method);
        }

        if (array_key_exists('keyword', $_GET)) {
            $keyword = $_GET['keyword'];

            foreach ($this->fields as $key => $value) {
                if ($key == 0) {
                    $this->db       = $this->db->like($value, $keyword);
                    $this->db_total = $this->db_total->like($value, $keyword);
                } else {
                    $this->db       = $this->db->or_like($value, $keyword);
                    $this->db_total = $this->db_total->or_like($value, $keyword);
                }
            }
        }

        if (array_key_exists('limit', $_GET)) {
            $limit       = $_GET['limit'];
            $offset      = $_GET['offset'] * $limit;

            $this->db = $this->db->limit($limit, $offset);
            $this->json_response['paging'] = TRUE;
            $this->json_response['page'] = $_GET['offset'] + 1;
        }

        $this->json_response['total']   = $this->db_total->get_where($this->table, $get_where)->num_rows();
        $this->data                     = $this->db->get_where($this->table, $get_where)->result_array();

        // $this->custom_data();

        $this->json_response['data'] = $this->data;
    }

    private function post()
    {
        foreach ($_POST as $key => $value) {
            $value = strpos($value, 'dansimbol') !== false ? str_replace('dansimbol', '&', $value) : $value;
            if (in_array($key, $this->fields)) $posts[$key] = $value;
        }
        !isset($_POST[$this->fields[0]]) && in_array('stamp', $this->fields) ? $posts['stamp'] = time() : false;
        !isset($_POST[$this->fields[0]]) && in_array('created_at', $this->fields) ? $posts['created_at'] = time() : (in_array('modified_at', $this->fields) ? $posts['modified_at'] = time() : false);
        if (isset($_POST[$this->fields[0]])) {
            $id = $posts[$this->fields[0]];
            $this->db->update($this->table, $posts, [$this->fields[0] => $id]);
        } else {
            $this->db->insert($this->table, $posts);
            $id = $this->db->insert_id();
        }
        $this->json_response['data'] = $this->db->get_where($this->table, [$this->fields[0] => $id])->row_array();
    }

    private function delete()
    {
        if ($this->db->get_where($this->table, [$this->fields[0] => $this->id])->row_array()) {
            $this->db->delete($this->table, [$this->fields[0] => $this->id]);
            $this->json_response['data'] = ['id' => $this->id];
        } else {
            $this->json_response['response']    = 'Not Found';
            $this->json_response['status']      = 404;
        }
    }

    public function password_verify()
    {
        foreach ($_GET as $key => $value) {
            if (in_array($key, $this->fields)) {
                if ($key == 'password' || $key == 'password_hash') {
                    $password_field_name = $key;
                } else {
                    $get_where[$key] = $value;
                }
            }
        }
        $this->data = $this->db->get_where($this->table, $get_where)->row_array();
        $result = $this->data ? (password_verify($_GET[$password_field_name], $this->data[$password_field_name]) ? true : false) : false;
        $this->json_response['data'] = $result;
    }

    /**
     * Send return as JSON
     *
     * @param	array   $params optional ['response' => 'string', 'status' => int, 'data' => array(), 'message' => 'string']
     * @return	echo json_encode
     */
    public function send(array $params = [])
    {
        $status     = isset($params['status']) ? $params['status'] : 0;
        $data       = isset($params['data']) ? $params['data'] : [];
        $message    = isset($params['message']) ? $params['message'] : '';

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $response   =
            $status == 400 ? 'Bad Request'
            : ($status == 401 ? 'Unauthorized'
                : ($status == 404 ? 'Not Found'
                    : ($status == 500 ? 'Internal Server Error'
                        : 'OK'
                    )
                )
            );

        header("HTTP/1.1 {$status} {$response}");

        $json_response = $status ? ['response' => $response, 'status' => $status] : $this->json_response;
        if (count($data) > 0) $json_response['data'] = $data;
        if ($message) $json_response['message'] = $message;

        echo json_encode($json_response, JSON_NUMERIC_CHECK);
        exit;
    }
}
