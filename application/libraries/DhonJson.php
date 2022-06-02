<?php

class DhonJson
{
    public $db_name;
    public $table;
    public $command;
    public $id;
    protected $db;
    protected $db_total;
    protected $fields;
    protected $json_response;
    protected $data;
    public $basic_auth;
    public $api_db;
    public $method;
    public $sort;
    public $filter;
    public $limit;

    public function __construct()
    {
        $this->dhonjson = &get_instance();

        $this->uri  = $this->dhonjson->uri;

        // $this->db_name  = $this->uri->segment(1); //v1
        // $this->table    = $this->uri->segment(2); //v1
        // $this->command  = $this->uri->segment(3); //v1
        // $this->id       = $this->uri->segment(4); //v1

        $this->load = $this->dhonjson->load;
    }

    /**
     * Authorize API User
     *
     * @param	string	$api_db_name
     * @return	void
     */
    public function basic_auth(string $api_db_name)
    {
        include APPPATH . "config/production/database.php";

        if (in_array($api_db_name, array_keys($db))) {
            $this->load->dbutil();
            if (ENVIRONMENT == 'development' && !$this->dhonjson->dbutil->database_exists($db[$api_db_name]['database'])) {
                $status     = 404;
                $message    = "API db not found";
                $this->send(['status' => $status, 'message' => $message]);
            } else {
                $api_db = $this->load->database($api_db_name, TRUE);

                if ($api_db->table_exists('api_users')) {
                    if (isset($_SERVER['PHP_AUTH_USER'])) {
                        $user = $api_db->get_where('api_users', ['username' => $_SERVER['PHP_AUTH_USER']])->row_array();
                        if (!$user || !password_verify($_SERVER['PHP_AUTH_PW'], $user['password'])) {
                            $this->_unauthorized();
                        }
                    } else {
                        $this->_unauthorized();
                    }
                } else {
                    $status     = 404;
                    $message    = "API table not found";
                    $this->send(['status' => $status, 'message' => $message]);
                }
            }
        } else {
            $status     = 404;
            $message    = "API db name not found";
            $this->send(['status' => $status, 'message' => $message]);
        }
    }

    /**
     * Return Unauthorize
     *
     * @return	void
     */
    private function _unauthorized()
    {
        $status     = 401;
        $this->send(['status' => $status]);
    }

    /**
     * Return Data/Response of Command
     *
     * @return	void
     */
    public function collect()
    {
        $this->basic_auth ? $this->basic_auth($this->api_db) : false;

        if ($this->db_name) {
            include APPPATH . "config/production/database.php";

            if (in_array($this->db_name, array_keys($db))) {
                $this->db           = $this->load->database($this->db_name, TRUE);
                $this->db_total     = $this->load->database($this->db_name, TRUE);

                if ($this->table) {
                    if ($this->db->table_exists($this->table)) {
                        $this->fields   = $this->db->list_fields($this->table);

                        $status = 200;
                        $this->json_response = ['status' => $status];

                        if ($this->method == 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->json_response['status']  = 405;
                            else $this->delete();
                        } else if ($this->command == 'password_verify') {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->json_response['status']  = 405;
                            else $this->password_verify();
                        } else if ($this->command == 'insert') $this->insert();
                        else if ($this->command == '') {
                            if ($this->method == 'GET') {
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->json_response['status']  = 405;
                                else $this->get_where();
                            } else if ($this->method == 'POST' || $this->method == 'PUT') {
                                if ($_SERVER['REQUEST_METHOD'] === 'GET') $this->json_response['status']  = 405;
                                else $this->post();
                            } else {
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') $this->json_response['status']  = 405;
                                else $this->get();
                            }
                        } else {
                            $this->json_response['status']  = 405;
                        }

                        $message = isset($this->json_response['message']) ? $this->json_response['message'] : '';
                        $total = isset($this->json_response['total']) ? $this->json_response['total'] : -1;
                        $result = isset($this->json_response['result']) ? $this->json_response['result'] : '';
                        $paging = isset($this->json_response['paging']) ? $this->json_response['paging'] : '';
                        $page = isset($this->json_response['page']) ? $this->json_response['page'] : '';
                        $data = isset($this->json_response['data']) ? $this->json_response['data'] : '';

                        $this->send(['status' => $this->json_response['status'], 'message' => $message, 'total' => $total, 'result' => $result, 'paging' => $paging, 'page' => $page, 'data' => $data]);
                    } else {
                        $status     = 404;
                        $message    = 'Table not found';
                    }
                } else {
                    $status     = 500;
                    $message    = '';
                }
            } else {
                $status     = 404;
                $message    = 'Database name not found';
            }
        } else {
            $status     = 500;
            $message    = '';
        }
        $this->send(['status' => $status, 'message' => $message]);
    }

    private function get()
    {
        if (($this->sort || $this->filter || $this->limit) && $_GET) {
            $this->get_where();
        } else if ($_GET) $this->json_response['status']  = 405;
        else {
            $this->db   = $this->db->get($this->table);
            $data       = $this->db->result_array();
            $this->json_response['total']   = $this->db->num_rows();
            $this->json_response['data']    = $data;
        }
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

        if ($this->sort) {
            if (array_key_exists('sort_by', $_GET)) {
                $sort_by        = $_GET['sort_by'];
                $sort_method    = isset($_GET['sort_method']) ? $_GET['sort_method'] : 'asc';

                $this->db         = $this->db->order_by($sort_by, $sort_method);
                $this->db_total    = $this->db_total->order_by($sort_by, $sort_method);
            }
        }

        if ($this->filter) {
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
        }

        if ($this->limit) {
            if (array_key_exists('limit', $_GET)) {
                $limit      = $_GET['limit'];
                $get_offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
                $offset     = $get_offset * $limit;

                $this->db = $this->db->limit($limit, $offset);
                $this->json_response['paging']  = TRUE;
                $this->json_response['page']    = $get_offset + 1;
            }
        }

        if (isset($get_where)) {
            $this->json_response['total']   = $this->db_total->get_where($this->table, $get_where)->num_rows();
            $this->json_response['data']    = $this->db->get_where($this->table, $get_where)->result_array();
        } else if (
            (array_key_exists('sort_by', $_GET) ||
                array_key_exists('keyword', $_GET))
            && $this->method != 'GET'
        ) {
            $this->json_response['total']   = $this->db_total->get($this->table)->num_rows();
            $this->json_response['data']    = $this->db->get($this->table)->result_array();
        } else if (array_key_exists('limit', $_GET) && $this->method != 'GET') {
            $total = $this->db_total->get($this->table)->num_rows();
            $this->json_response['total']   = $total;
            $this->json_response['result']  = $total < $limit ? $total : $limit;
            $this->json_response['data']    = $this->db->get($this->table)->result_array();
        } else {
            $this->json_response['status']  = 405;
        }
    }

    private function post()
    {
        $input = $_POST ? $_POST : $_GET;
        foreach ($input as $key => $value) {
            $value = strpos($value, 'dansimbol') !== false ?
                str_replace('dansimbol', '&', $value)
                : ($key == 'password' || $key == 'password_hash' ? password_hash($value, PASSWORD_DEFAULT) : $value);

            if (in_array($key, $this->fields)) $posts[$key] = $value;
        }
        $fields = $this->db->list_fields($this->table);
        // !isset($input[$this->fields[0]]) && in_array('stamp', $this->fields) ?
        $this->method != 'PUT' && in_array('stamp', $this->fields) ?
            $posts['stamp'] = time() : false;
        // !isset($input[$this->fields[0]]) && in_array('created_at', $this->fields) && !isset($input['created_at'])
        $this->method != 'PUT' && in_array('created_at', $this->fields) && !isset($input['created_at'])
            ? ($this->db->field_data($this->table)[array_search('created_at', $fields)]->type == 'INT'
                ? $posts['created_at'] = time()
                : $posts['created_at'] = date('Y-m-d H:i:s', time()))
            : (in_array('modified_at', $this->fields)
                ? ($this->db->field_data($this->table)[array_search('modified_at', $fields)]->type == 'INT'
                    ? $posts['modified_at'] = time()
                    : $posts['modified_at'] = date('Y-m-d H:i:s', time()))
                : (in_array('updated_at', $this->fields)
                    ? ($this->db->field_data($this->table)[array_search('updated_at', $fields)]->type == 'INT'
                        ? $posts['updated_at'] = time()
                        : $posts['updated_at'] = date('Y-m-d H:i:s', time()))
                    : false
                )
            );

        // if (isset($input[$this->fields[0]])) {
        if ($this->method == 'PUT') {
            // $id = $posts[$this->fields[0]];
            $id = $this->id;
            if ($this->db->get_where($this->table, [$this->fields[0] => $id])->row_array()) {
                $this->db->update($this->table, $posts, [$this->fields[0] => $id]);
            } else {
                $this->json_response['status']  = 404;
            }
        } else {
            $id = $this->db->insert($this->table, $posts) ? $this->db->insert_id() : 0;
        }

        if ($id != 0) {
            $this->json_response['data']    = $this->db->get_where($this->table, [$this->fields[0] => $id])->row_array();
        } else {
            $this->json_response['status']  = 406;
            $this->json_response['data']    = [false];
            $this->json_response['message'] = 'Duplicate detected';
        }
    }

    private function delete()
    {
        if ($this->id) {
            if ($this->db->get_where($this->table, [$this->fields[0] => $this->id])->row_array()) {
                $this->db->delete($this->table, [$this->fields[0] => $this->id]);

                $query = "ALTER TABLE $this->table AUTO_INCREMENT = 1";
                $this->db->query($query);

                $this->json_response['data'] = ['id' => $this->id];
            } else {
                $this->json_response['status']  = 404;
                $this->json_response['message'] = 'ID not found';
            }
        } else {
            $this->json_response['status']  = 500;
        }
    }

    private function password_verify()
    {
        if ($_GET) {
            foreach ($_GET as $key => $value) {
                if (in_array($key, $this->fields)) {
                    if ($key == 'password' || $key == 'password_hash') {
                        $password_field_name = $key;
                    } else {
                        $get_where[$key] = $value;
                    }
                }
            }
            if (isset($password_field_name) && isset($get_where)) {
                $this->data = $this->db->get_where($this->table, $get_where)->row_array();
                $result = $this->data ? (password_verify($_GET[$password_field_name], $this->data[$password_field_name]) ? true : [false]) : [false];
                $this->json_response['data'] = $result;
            } else {
                $this->json_response['status']  = 405;
            }
        } else {
            $this->json_response['status']  = 500;
        }
    }

    private function insert()
    {
        if ($_GET) {
            $this->post();
        } else {
            $this->json_response['status']  = 500;
        }
    }

    /**
     * Send return as JSON
     *
     * @param	array   $params optional ['response' => 'string', 'status' => int, 'message' => 'string', 'total' => int, 'result' => int, 'paging' => boolean, 'page' => int, 'data' => array()]
     * @return	echo    json_encode
     */
    public function send(array $params = [])
    {
        $status     = isset($params['status']) ? $params['status'] : 500;
        $message    = isset($params['message']) ? $params['message'] : '';
        $total      = isset($params['total']) ? $params['total'] : -1;
        $result     = isset($params['result']) ? $params['result'] : '';
        $paging     = isset($params['paging']) ? $params['paging'] : '';
        $page       = isset($params['page']) ? $params['page'] : '';
        $data       = isset($params['data']) ? $params['data'] : '';

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        // header('WWW-Authenticate: Basic realm="My Realm"');

        $response =
            $status == 400 ? 'Bad Request'
            : ($status == 401 ? 'Unauthorized'
                : ($status == 404 ? 'Not Found'
                    : ($status == 405 ? 'Method Not Allowed'
                        : ($status == 406 ? 'Not Acceptable'
                            : ($status == 417 ? 'Expectation Failed'
                                : ($status == 500 ? 'Internal Server Error'
                                    : 'OK'
                                )
                            )
                        )
                    )
                )
            );

        header("HTTP/1.1 {$status} {$response}");

        $json_response = ['response' => $response, 'status' => $status];
        if ($message != '') $json_response['message'] = $message;
        if ($total != -1) $json_response['total'] = $total;
        if ($result != '') $json_response['result'] = $result;
        if ($paging != '') $json_response['paging'] = $paging;
        if ($page != '') $json_response['page'] = $page;
        if ($data === [false]) $json_response['data'] = false;
        else if ($data != '') $json_response['data'] = $data;

        echo json_encode($json_response, JSON_NUMERIC_CHECK);
        exit;
    }
}
