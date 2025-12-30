<?php defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__ . '/../vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\WooCommerce\HttpClient\HttpClient;
class Auth extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->lang->load('auth', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->load->model('auth_model');
        $this->load->library('ion_auth');
        $this->load->model('pos_model');
        $this->load->model('sales_model');
        $this->load->model('doitac_model');
        $this->load->model('companies_model');
        $this->load->model('products_model');

        $this->pos_settings = $this->pos_model->getSetting();
    }

    function index()
    {

        if (!$this->loggedIn) {
            redirect('login');
        } else {
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    function users()
    {
        if ( ! $this->loggedIn) {
            redirect('login');
        }
        if ( ! $this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('users')));
        $meta = array('page_title' => lang('users'), 'bc' => $bc);
        $this->page_construct('auth/index', $meta, $this->data);
    }

    function getUsers()
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            $this->sma->md();
        }

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, concat(first_name,' ', last_name),username, email, company, award_points, " . $this->db->dbprefix('groups') . ".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', NULL)
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('auth/profile/$1') . "' class='tip' title='" . lang("edit_user") . "'><i class=\"fa fa-edit\"></i></a></div>", "id");

        if (!$this->Owner) {
            $this->datatables->unset_column('id');
        }
        echo $this->datatables->generate();
    }

    function getUserLogins($id = NULL)
    {
        if (!$this->ion_auth->in_group(array('super-admin', 'admin'))) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect('welcome');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('user_id', $id);

        echo $this->datatables->generate();
    }

    function delete_avatar($id = NULL, $avatar = NULL)
    {

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            unlink('assets/uploads/avatars/' . $avatar);
            unlink('assets/uploads/avatars/thumbs/' . $avatar);
            if ($id == $this->session->userdata('user_id')) {
                $this->session->unset_userdata('avatar');
            }
            $this->db->update('users', array('avatar' => NULL), array('id' => $id));
            $this->session->set_flashdata('message', lang("avatar_deleted"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . $_SERVER["HTTP_REFERER"] . "'; }, 0);</script>");
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function profile($id = NULL)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('owner') && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$id || empty($id)) {
            redirect('auth');
        }

        $this->data['title'] = lang('profile');

        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $this->data['csrf'] = $this->_get_csrf_nonce();
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'class' => 'form-control',
            'type' => 'password',
            'value' => ''
        );
        $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
        $this->data['old_password'] = array(
            'name' => 'old',
            'id' => 'old',
            'class' => 'form-control',
            'type' => 'password',
        );
        $this->data['new_password'] = array(
            'name' => 'new',
            'id' => 'new',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['new_password_confirm'] = array(
            'name' => 'new_confirm',
            'id' => 'new_confirm',
            'type' => 'password',
            'class' => 'form-control',
            'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
        );
        $this->data['user_id'] = array(
            'name' => 'user_id',
            'id' => 'user_id',
            'type' => 'hidden',
            'value' => $user->id,
        );

        $this->data['id'] = $id;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('profile')));
        $meta = array('page_title' => lang('profile'), 'bc' => $bc);
        $this->page_construct('auth/profile', $meta, $this->data);
    }

    public function captcha_check($cap)
    {
        $expiration = time() - 300; // 5 minutes limit
        $this->db->delete('captcha', array('captcha_time <' => $expiration));

        $this->db->select('COUNT(*) AS count')
            ->where('word', $cap)
            ->where('ip_address', $this->input->ip_address())
            ->where('captcha_time >', $expiration);

        if ($this->db->count_all_results('captcha')) {
            return true;
        } else {
            $this->form_validation->set_message('captcha_check', lang('captcha_wrong'));
            return FALSE;
        }
    }
	
	function showPassReset(){
		$password="12345678910";//$this->input->get('password');
		$this->auth_model->showPassReset($password);
		exit();		
	}

    function login($m = NULL)
    {
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('welcome');
        }
        $this->data['title'] = lang('login');

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {

            $remember = (bool)$this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        redirect('auth/logout');
                    }
                }
                if ($this->ion_auth->in_group('customer') || $this->ion_auth->in_group('supplier')) {
                    redirect('auth/logout/1');
                }
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $referrer = $this->session->userdata('requested_page') ? $this->session->userdata('requested_page') : 'welcome';
                redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('login');
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $this->session->flashdata('message');
            if ($this->Settings->captcha) {
                $this->load->helper('captcha');
                $vals = array(
                    'img_path' => './assets/captcha/',
                    'img_url' => site_url() . 'assets/captcha/',
                    'img_width' => 150,
                    'img_height' => 34,
                    'word_length' => 5,
                    'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
                );
                $cap = create_captcha($vals);
                $capdata = array(
                    'captcha_time' => $cap['time'],
                    'ip_address' => $this->input->ip_address(),
                    'word' => $cap['word']
                );

                $query = $this->db->insert_string('captcha', $capdata);
                $this->db->query($query);
                $this->data['image'] = $cap['image'];
                $this->data['captcha'] = array('name' => 'captcha',
                    'id' => 'captcha',
                    'type' => 'text',
                    'class' => 'form-control',
                    'required' => 'required',
                    'placeholder' => lang('type_captcha')
                );
            }

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('email'),
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'placeholder' => lang('password'),
            );
            $this->data['allow_reg'] = $this->Settings->allow_reg;
            if ($m == 'db') {
                $this->data['message'] = lang('db_restored');
            } elseif ($m) {
                $this->data['error'] = lang('we_are_sorry_as_this_sction_is_still_under_development.');
            }

            $this->load->view($this->theme . 'auth/login', $this->data);
        }
    }

    function reload_captcha()
    {
        $this->load->helper('captcha');
        $vals = array(
            'img_path' => './assets/captcha/',
            'img_url' => site_url() . 'assets/captcha/',
            'img_width' => 150,
            'img_height' => 34,
            'word_length' => 5,
            'colors' => array('background' => array(255, 255, 255), 'border' => array(204, 204, 204), 'text' => array(102, 102, 102), 'grid' => array(204, 204, 204))
        );
        $cap = create_captcha($vals);
        $capdata = array(
            'captcha_time' => $cap['time'],
            'ip_address' => $this->input->ip_address(),
            'word' => $cap['word']
        );
        $query = $this->db->insert_string('captcha', $capdata);
        $this->db->query($query);
        //$this->data['image'] = $cap['image'];

        echo $cap['image'];
    }

    function logout($m = NULL)
    {

        $logout = $this->ion_auth->logout();
        $this->session->set_flashdata('message', $this->ion_auth->messages());

        redirect('login/' . $m);
    }

    function change_password()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('login');
        }
        $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
        $this->form_validation->set_rules('new_password', lang('new_password'), 'required|min_length[8]|max_length[25]');
        $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');

        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('auth/profile/' . $user->id . '/#cpassword');
        } else {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));

            $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));

            if ($change) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('auth/profile/' . $user->id . '/#cpassword');
            }
        }
    }

    function forgot_password()
    {
        $this->form_validation->set_rules('forgot_email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $error = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->session->set_flashdata('error', $error);
            redirect("login#forgot_password");
        } else {

            $identity = $this->ion_auth->where('email', strtolower($this->input->post('forgot_email')))->users()->row();
            if (empty($identity)) {
                $this->ion_auth->set_message('forgot_password_email_not_found');
                $this->session->set_flashdata('error', $this->ion_auth->messages());
                redirect("login#forgot_password");
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);

            if ($forgotten) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("login#forgot_password");
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect("login#forgot_password");
            }
        }
    }

    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);
		
        if ($user) {

            $this->form_validation->set_rules('new', lang('password'), 'required|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {

                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message'] = $this->session->flashdata('message');
                $this->data['title'] = lang('reset_password');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = array(
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'class' => 'form-control',
                    'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    'data-bv-regexp-message' => lang('pasword_hint'),
                    'placeholder' => lang('new_password')
                );
                $this->data['new_password_confirm'] = array(
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'data-bv-identical' => 'true',
                    'data-bv-identical-field' => 'new',
                    'data-bv-identical-message' => lang('pw_not_same'),
                    'placeholder' => lang('confirm_password')
                );
                $this->data['user_id'] = array(
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                );
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;
                $this->data['identity_label'] = $user->email;
                //render
                $this->load->view($this->theme . 'auth/reset_password', $this->data);
            } else {
                // do we have a valid request?
                if ($user->id != $this->input->post('user_id')) {

                    //something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);
                    show_error(lang('error_csrf'));

                } else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        //$this->logout();
                        redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code);
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("login#forgot_password");
        }
    }

    function activate($id, $code = false)
    {

        if ($code !== false) {
            $activation = $this->ion_auth->activate($id, $code);
        } else if ($this->Owner) {
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            if ($this->Owner) {
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                redirect("auth/login");
            }
        } else {
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("forgot_password");
        }
    }

    function deactivate($id = NULL)
    {
        $this->sma->checkPermissions('users', TRUE);
        $id = $this->config->item('use_mongodb', 'ion_auth') ? (string)$id : (int)$id;
        $this->form_validation->set_rules('confirm', lang("confirm"), 'required');

        if ($this->form_validation->run() == FALSE) {
            if ($this->input->post('deactivate')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            } else {
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['user'] = $this->ion_auth->user($id)->row();
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'auth/deactivate_user', $this->data);
            }
        } else {

            if ($this->input->post('confirm') == 'yes') {
                if ($id != $this->input->post('id')) {
                    show_error(lang('error_csrf'));
                }

                if ($this->ion_auth->logged_in() && $this->Owner) {
                    $this->ion_auth->deactivate($id);
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                }
            }

            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function create_user()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->data['title'] = "Create User";
        $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
        $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
        $this->form_validation->set_rules('status', lang("status"), 'trim|required');
        $this->form_validation->set_rules('group', lang("group"), 'trim|required');

        $this->form_validation->set_rules('username', lang("username"), 'trim|required');
        $this->form_validation->set_rules('first_name', lang("first_name"), 'trim|required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'trim|required');
        $this->form_validation->set_rules('email', lang("email"), 'trim|required');

        if ((int)$this->input->post('group')>2) {
            $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
        }

        if ($this->form_validation->run() == true) {

            $username = strtolower($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $notify = $this->input->post('notify');
            $new_permission_all=(array)$this->input->post('new_permission');
            $new_permission=[];
            foreach ($new_permission_all as $p) {
                if ($this->input->post('group')!=$p) {
                    $new_permission[]=$p;
                }
            }

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'group_id' => $this->input->post('group') ? $this->input->post('group') : '3',
                'biller_id' => $this->input->post('biller'),
                'warehouse_id' => $this->input->post('warehouse'),
                'view_right' => $this->input->post('view_right'),
                'edit_right' => $this->input->post('edit_right'),
                'allow_discount' => $this->input->post('allow_discount'),
                'new_permission' => json_encode($new_permission),
            );
            $active = $this->input->post('status');
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {

            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("auth/users");

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups'] = $this->ion_auth->groups()->result_array();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('auth/users'), 'page' => lang('users')), array('link' => '#', 'page' => lang('create_user')));
            $meta = array('page_title' => lang('users'), 'bc' => $bc);
            $this->page_construct('auth/create_user', $meta, $this->data);
        }
    }

    function edit_user($id = NULL)
    {

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $this->data['title'] = lang("edit_user");

        if (!$this->loggedIn || !$this->Owner && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $user = $this->ion_auth->user($id)->row();

        if ($user->username != $this->input->post('username')) {
            $this->form_validation->set_rules('username', lang("username"), 'trim|is_unique[users.username]');
        }
        if ($user->email != $this->input->post('email')) {
            $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
        }



        if ($this->form_validation->run() === TRUE) {

            if ($this->Owner) {
                if ($id == $this->session->userdata('user_id')) {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                    );
                } elseif ($this->ion_auth->in_group('customer', $id) || $this->ion_auth->in_group('supplier', $id)) {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                    );
                } else {
                    if ((int)$this->input->post('group')>2) {
                        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim|required');
                    }
                    $new_permission_all=(array)$this->input->post('new_permission');
                    $new_permission=[];
                    foreach ($new_permission_all as $p) {
                        if ($this->input->post('group')!=$p) {
                            $new_permission[]=$p;
                        }
                    }
                    
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'username' => $this->input->post('username'),
                        'email' => $this->input->post('email'),
                        'phone' => $this->input->post('phone'),
                        'gender' => $this->input->post('gender'),
                        'active' => $this->input->post('status'),
                        'group_id' => $this->input->post('group'),
                        'biller_id' => $this->input->post('biller') ? $this->input->post('biller') : NULL,
                        'warehouse_id' => $this->input->post('warehouse') ? $this->input->post('warehouse') : NULL,
                        'award_points' => $this->input->post('award_points'),
                        'view_right' => $this->input->post('view_right'),
                        'edit_right' => $this->input->post('edit_right'),
                        'allow_discount' => $this->input->post('allow_discount'),
                        'new_permission' => json_encode($new_permission),
                    );
                }

            } elseif ($this->Admin) {
                $data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
                    'active' => $this->input->post('status'),
                    'award_points' => $this->input->post('award_points'),
                );
            } else {
                $data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'gender' => $this->input->post('gender'),
                );
            }

            if ($this->Owner) {
                if ($this->input->post('password')) {
                    if (DEMO) {
                        $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $this->form_validation->set_rules('password', lang('edit_user_validation_password_label'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
                    $this->form_validation->set_rules('password_confirm', lang('edit_user_validation_password_confirm_label'), 'required');

                    $data['password'] = $this->input->post('password');
                }
            }
            //$this->sma->print_arrays($data);

        }
        if ($this->form_validation->run() === TRUE && $this->ion_auth->update($user->id, $data)) {
            $this->session->set_flashdata('message', lang('user_updated'));
            redirect("auth/profile/" . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }


    function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    function _valid_csrf_nonce()
    {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
            $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function _render_page($view, $data = null, $render = false)
    {

        $this->viewdata = (empty($data)) ? $this->data : $data;
        $view_html = $this->load->view('header', $this->viewdata, $render);
        $view_html .= $this->load->view($view, $this->viewdata, $render);
        $view_html = $this->load->view('footer', $this->viewdata, $render);

        if (!$render)
            return $view_html;
    }

    /**
     * @param null $id
     */
    function update_avatar($id = NULL)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        if (!$this->ion_auth->logged_in() || !$this->Owner && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        //validate form input
        $this->form_validation->set_rules('avatar', lang("avatar"), 'trim');

        if ($this->form_validation->run() == true) {

            if ($_FILES['avatar']['size'] > 0) {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/avatars';
                $config['allowed_types'] = 'gif|jpg|png';
                //$config['max_size'] = '500';
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('avatar')) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $photo = $this->upload->file_name;

                $this->load->helper('file');
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = 'assets/uploads/avatars/' . $photo;
                $config['new_image'] = 'assets/uploads/avatars/thumbs/' . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 150;
                $config['height'] = 150;;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                $user = $this->ion_auth->user($id)->row();
            } else {
                $this->form_validation->set_rules('avatar', lang("avatar"), 'required');
            }
        }

        if ($this->form_validation->run() == true && $this->auth_model->updateAvatar($id, $photo)) {
            unlink('assets/uploads/avatars/' . $user->avatar);
            unlink('assets/uploads/avatars/thumbs/' . $user->avatar);
            $this->session->set_userdata('avatar', $photo);
            $this->session->set_flashdata('message', lang("avatar_updated"));
            redirect("auth/profile/" . $id);
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect("auth/profile/" . $id);
        }
    }

    function register()
    {
        $this->data['title'] = "Register";
        if (!$this->Settings->allow_reg) {
           $this->session->set_flashdata('error', lang('registration_is_disabled'));
           redirect("login");
        }

        $this->form_validation->set_message('is_unique', lang('account_exists'));
        $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
        $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('username', lang('username'), 'required|is_unique[users.username]');
       // $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
        //$this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');
        if ($this->Settings->captcha) {
          //  $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
            );
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {

            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("login");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups'] = $this->ion_auth->groups()->result_array();

            $this->load->helper('captcha');
            $vals = array(
                'img_path' => './assets/captcha/',
                'img_url' => site_url() . 'assets/captcha/',
                'img_width' => 150,
                'img_height' => 34,
            );
            $cap = create_captcha($vals);
            $capdata = array(
                'captcha_time' => $cap['time'],
                'ip_address' => $this->input->ip_address(),
                'word' => $cap['word']
            );

            $query = $this->db->insert_string('captcha', $capdata);
            $this->db->query($query);
            $this->data['image'] = $cap['image'];
            $this->data['captcha'] = array('name' => 'captcha',
                'id' => 'captcha',
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => lang('type_captcha')
            );

            $this->data['first_name'] = array(
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'class' => 'form-control',
                'required' => 'required',
                'value' => $this->form_validation->set_value('first_name'),
            );
            $this->data['last_name'] = array(
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('last_name'),
            );
            $this->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('email'),
            );
            $this->data['company'] = array(
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('company'),
            );
            $this->data['phone'] = array(
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('phone'),
            );
            $this->data['password'] = array(
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('password'),
            );
            $this->data['password_confirm'] = array(
                'name' => 'password_confirm',
                'id' => 'password_confirm',
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('password_confirm'),
            );
			
            $this->load->view($this->theme.'auth/register', $this->data);
        }
    }

    function user_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        if ($id != $this->session->userdata('user_id')) {
                            $this->auth_model->delete_user($id);
                        }
                    }
                    $this->session->set_flashdata('message', lang("users_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $user = $this->site->getUser($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $user->company);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user->group);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'users_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_user_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function delete($id = NULL)
    {
        // redirect($_SERVER["HTTP_REFERER"]);
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->get('id')) { $id = $this->input->get('id'); }

        if ( ! $this->Owner || $id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        if ($this->auth_model->delete_user($id)) {
            //echo lang("user_deleted");
            $this->session->set_flashdata('message', 'user_deleted');
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function thongbao()
    {
        if (!$this->loggedIn) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Thông báo')));
        $meta = array('page_title' => lang('Thông báo'), 'bc' => $bc);
        $this->page_construct('auth/thongbao', $meta, $this->data);
    }
    public function getThongBaos()
    {        
        $detail_link = anchor('auth/view_thongbao/$1', '<i class="fa fa-eye"></i> ' . lang('Xem chi tiết'), ' class="tip" data-toggle="modal" data-target="#myModal"');
        $this->load->library('datatables');
        
        $this->datatables->select("title,created,(CASE WHEN popup=1 THEN 'Popup' ELSE 'Thông báo' END) as theloai,(CASE WHEN daxem=1 THEN updated ELSE 'Chưa xem' END) as daxem,ID")->from('thongbao');           

            $this->datatables->add_column("Actions", $detail_link, "ID");
            $this->datatables->unset_column('ID');
            echo $this->datatables->generate();           
        
        
    }
    
    function view_thongbao($id=0)
    {
        if($id>0){
           $id = (int)$id;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $thongbao = $this->db->from('thongbao')->where('ID', $id)->get()->row_array();
            if (!empty($thongbao) && count($thongbao)) {

                if ($thongbao['daxem']==0) {
                    $updated=gmdate("Y:m:d H:i:s", time() + 7 * 3600);
                    $this->db->where('ID', $id)->update('thongbao', array('daxem'=>1,'closed'=>1,'updated'=>$updated));
                }
            }

            $this->data['thongbao'] = $thongbao;

            $this->load->view($this->theme . 'auth/view_thongbao', $this->data);

        }
    }

    function cms_close_quangcao($id=0)
    {
        if($id>0){

            $id = (int)$id;
            $thongbao = $this->db->from('thongbao')->where('ID', $id)->get()->row_array();
            if (!empty($thongbao) && count($thongbao)) {
               
                $updated=gmdate("Y:m:d H:i:s", time() + 7 * 3600);
                $this->db->where('ID', $id)->update('thongbao', array('closed'=>1,'updated'=>$updated));
                return true;
            }
        }
    }
    public function history()
    {
        if (!$this->loggedIn) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Lịch sử giao dịch')));
        $meta = array('page_title' => lang('Lịch sử giao dịch'), 'bc' => $bc);
        $this->page_construct('auth/history', $meta, $this->data);
    }
    public function getHistorys()
    {        
        $detail_link = anchor('auth/view_history_full/$1', '<i class="fa fa-eye"></i> ' . lang('Xem chi tiết'), ' class="tip" data-toggle="modal" data-target="#myModal"');
        $this->load->library('datatables');
        
        $phanloai=isset($_POST['phanloai'])?$_POST['phanloai']:'';
        $phanloai_array=isset($_POST['phanloai_array'])?$_POST['phanloai_array']:'';

        if ($_POST['from'] == '' || $_POST['to'] == '') 
        {
            $_POST['from']=date("Y-m-d",strtotime('first day of this month'));
            $_POST['to']=date("Y-m-d",strtotime('last day of this month'));
        }
        $_POST['to']= str_replace("/","-",$_POST['to']);
        $_POST['from']= str_replace("/","-",$_POST['from']);
        $_POST['to'] = date('Y-m-d', strtotime($_POST['to']));
        $_POST['from'] = date('Y-m-d', strtotime($_POST['from']));

        $where_add_date=" WHERE date(created) BETWEEN '".$_POST['from']."' AND '".$_POST['to']."'";
        $where_add_his=" WHERE date(history_date) BETWEEN '".$_POST['from']."' AND '".$_POST['to']."'";     

        if ($this->Admin||$this->Owner)
        {                            

        }else{                            
            if (!$this->session->userdata('view_right'))
            {                                                            
                $where_add_date.=" AND created_by=".$this->session->userdata('user_id')." AND warehouse_id=".$this->session->userdata('warehouse_id');
                $where_add_his.=" AND created_by=".$this->session->userdata('user_id')." AND warehouse_id=".$this->session->userdata('warehouse_id');   
            }else{
                $where_add_date.=" AND warehouse_id=".$this->session->userdata('warehouse_id');
                $where_add_his.=" AND warehouse_id=".$this->session->userdata('warehouse_id');
            }            
        }

        $report=[];
        //get report san pham
     
        $query_phieuchi="SELECT history_date,(SELECT CONCAT(first_name,' ',last_name) FROM scodeweb_users WHERE id=history_auth) as history_auth,'PHIEUCHI' as PHANLOAI,history as loai,id as transaction_id, `reference` as transaction_code,idauto,'' as `ADD` FROM `scodeweb_expenses_history`".$where_add_his;

        $query_phieuchi_ncc="SELECT history_date,(SELECT CONCAT(first_name,' ',last_name) FROM scodeweb_users WHERE id=history_auth) as history_auth,'PHIEUCHI' as PHANLOAI,history as loai,id as transaction_id, `reference_no` as transaction_code,idauto,'TRUE' as `ADD` FROM `scodeweb_payments_history`".$where_add_his." AND type='sent'";

        $query_phieuthu="SELECT history_date,(SELECT CONCAT(first_name,' ',last_name) FROM scodeweb_users WHERE id=history_auth) as history_auth,'PHIEUTHU' as PHANLOAI,history as loai,id as transaction_id, `reference_no` as transaction_code,idauto,'' as `ADD` FROM `scodeweb_payments_history`".$where_add_his." AND type!='sent'";
       
        $query_order="SELECT history_date,(SELECT CONCAT(first_name,' ',last_name) FROM scodeweb_users WHERE id=history_auth) as history_auth,'HOADON' as PHANLOAI,history as loai,id as transaction_id, `reference_no` as transaction_code,idauto,'' as `ADD` FROM `scodeweb_sales_history`".$where_add_his;

        if ($phanloai_array=='') {
            if ($phanloai=='PHIEUTHU') {
                $report[]=$query_phieuthu;
            }else if ($phanloai=='PHIEUCHI') {
                $report[]=$query_phieuchi;
                $report[]=$query_phieuchi_ncc;
            }else if ($phanloai=='HOADON') {
                $report[]=$query_order;
            }else
            {                              
               $report=[$query_phieuthu,$query_phieuchi,$query_phieuchi_ncc,$query_order];                            
            }
        }else if ($phanloai_array!='') {
            $phanloai_array=explode(",",$phanloai_array);
            if (!empty($phanloai_array)){
                foreach ($phanloai_array as $loai)
                {
                   if ($loai=='PHIEUTHU') {
                        $report[]=$query_phieuthu;
                    }else if ($loai=='PHIEUCHI') {
                        $report[]=$query_phieuchi;
                        $report[]=$query_phieuchi_ncc;
                    }else if ($loai=='HOADON') {
                        $report[]=$query_order;
                    }                                   
                }
            }
        }
        
       $query_all="SELECT thongbao.* FROM(".implode(" UNION ",$report).") AS thongbao ORDER BY history_date DESC";  

        echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_all);          
                
    }
    function ThongBaoPopup($return=false)
    {
        $thongbao=$this->db->select('ID,hinhanh,lienket')->from('thongbao')->where(['daxem'=>0,'closed'=>0,'popup'=>1])->order_by('created','desc')->limit(1)->get()->row_array();
        
        if ($return) {
            return $thongbao;    
        }else{
            exit(json_encode($thongbao));
        }
        
    }
    function SysnApiWoooOrdersV3(){

        $woo_url=json_decode($this->Settings->woo_url);
        $woo_key=json_decode($this->Settings->woo_key);
        $woo_sec=json_decode($this->Settings->woo_sec);
        foreach ($woo_url as $index=>$value) 
        {
            if ($value!="") {
                $this->SysnApiWoooOrders($value,$woo_key[$index],$woo_sec[$index]);
            }
        }
    }
    function SysnApiWoooOrders($woo_url='',$woo_key='',$woo_sec=''){           
        if($woo_url!=''&&$woo_key!=''&&$woo_sec!=''){
            try{
                $woocommerce = new Client($woo_url,$woo_key,$woo_sec,
                    [
                        'version' => 'wc/v3',
                        'debug'           => true,
                        'return_as_array' => false,
                        'validate_url'    => false,
                        'timeout'         => 30,
                        'ssl_verify'      => false,
                    ]
                );
                
                
                $error=array();
                $success=array();
                $last_time=$this->sales_model->getLastTimeOrderWooApi();
                $ex=explode("+",$last_time);
                $last_time=$ex[0];
                $orders=$woocommerce->get('orders?after='.$last_time);
                $tong_don=count($orders);        



                //tien hanh xu ly dong bo don hang 
                if($tong_don>0){
                    foreach($orders as $cart){  
                      //  echo var_dump($cart);
                        //kienm tra xem woo_id co ton tai tren he thong hay chua
                        if($this->sales_model->checkCartWooApi($cart->id)==0){          
                            
                            $warehouse_id = $this->Settings->default_warehouse; //get default kho                   
                            $customer_id = $this->pos_settings->default_customer; // de default customer
                            $biller_id = $this->pos_settings->default_biller; //get default billder
                            
                            $customer_id_woo=$cart->customer_id;
                            if($customer_id_woo>0){
                                $getContact = $woocommerce->get('customers/'.$customer_id_woo);
                                $email=$getContact->email;
                                $first_name=$getContact->first_name;
                                $last_name=$getContact->last_name;
                                if($getContact->billing){                   
                                    $address=$getContact->billing->address_1;
                                    $phone=$getContact->billing->phone;
                                }
                                if($email==""){
                                    $email=$phone."@posbasic.net";
                                }
                                $full_name=$first_name." ".$last_name;
                                if($phone!=''&&$full_name!=''){
                                    //auto add new customer or get customer by woo_customer_id      
                                    //kiem tra woo_customer_id neu co ton tai thi tien hanh cap nhat, con lai them moi 
                                    $customer_id=$this->sales_model->checkCustomerIdWooApi($customer_id_woo);
                                    if($customer_id>0){
                                        //tien hanh update
                                        $data_cus = array('name' => $full_name,
                                            'address' => $address,
                                            'phone' => $phone
                                        );
                                        $this->companies_model->updateCompany($customer_id, $data_cus);
                                    }else{
                                        $customer_id=$this->sales_model->checkCustomerByPhoneWooApi($phone);
                                        if($customer_id>0){
                                            //tien hanh update
                                            $data_cus = array('name' => $full_name,
                                                'address' => $address,
                                                'phone' => $phone
                                            );
                                            $this->companies_model->updateCompany($customer_id, $data_cus);
                                        }else{
                                            //tien hanh them khach hang moi
                                            $customer_group_default=$this->Settings->customer_group;
                                            $cg = $this->site->getCustomerGroupByID($customer_group_default);       
                                            
                                            $data_cus = array('name' => $full_name,
                                                'email' => $email,
                                                'group_id' => '3',
                                                'group_name' => 'customer',
                                                'customer_group_id' => $customer_group_default,
                                                'customer_group_name' => $cg->name,
                                                'price_group_id' => NULL,
                                                'price_group_name' => NULL,
                                                'company' => 'POSBASIC',
                                                'address' => $address,
                                                'vat_no' => '',
                                                'city' => '',
                                                'state' => '',
                                                'postal_code' => '',
                                                'country' => '',
                                                'phone' => $phone,
                                                'cf1' => '',
                                                'cf2' => '',
                                                'cf3' => '',
                                                'cf4' => '',
                                                'cf5' => '',
                                                'cf6' => '',
                                                'nobandau' =>0,
                                                'woo_customer_id'=>$customer_id_woo,
                                            );
                                            $customer_id = $this->companies_model->addCompany($data_cus);
                                        }
                                    }
                                }
                            }else{
                                //woo api ko có customer_id_woo
                                //dua vao order info get full name, email, phone, address
                                $email=$cart->billing->email;
                                
                                $first_name=$cart->billing->first_name;
                                $last_name=$cart->billing->last_name;
                                                    
                                $address=$cart->billing->address_1;
                                $phone=$cart->billing->phone;
                                
                                if($email==""){
                                    $email=$phone."@posbasic.net";
                                }
                                $full_name=$first_name." ".$last_name;
                                if($phone!=''&&$full_name!=''){
                                    //auto add new customer or get customer by woo_customer_id      
                                    //kiem tra woo_customer_id neu co ton tai thi tien hanh cap nhat, con lai them moi 
                                    $customer_id=$this->sales_model->checkCustomerByPhoneWooApi($phone);
                                    if($customer_id>0){
                                        //tien hanh update
                                        $data_cus = array('name' => $full_name,
                                            'address' => $address,
                                            'phone' => $phone
                                        );
                                        $this->companies_model->updateCompany($customer_id, $data_cus);
                                    }else{
                                        //tien hanh them khach hang moi
                                        $customer_group_default=$this->Settings->customer_group;
                                        $cg = $this->site->getCustomerGroupByID($customer_group_default);       
                                        
                                        $data_cus = array('name' => $full_name,
                                            'email' => $email,
                                            'group_id' => '3',
                                            'group_name' => 'customer',
                                            'customer_group_id' => $customer_group_default,
                                            'customer_group_name' => $cg->name,
                                            'price_group_id' => NULL,
                                            'price_group_name' => NULL,
                                            'company' => 'POSBASIC',
                                            'address' => $address,
                                            'vat_no' => '',
                                            'city' => '',
                                            'state' => '',
                                            'postal_code' => '',
                                            'country' => '',
                                            'phone' => $phone,
                                            'cf1' => '',
                                            'cf2' => '',
                                            'cf3' => '',
                                            'cf4' => '',
                                            'cf5' => '',
                                            'cf6' => '',
                                            'nobandau' =>0,
                                        );
                                        $customer_id = $this->companies_model->addCompany($data_cus);
                                    }
                                }
                            }
                            $total_items = $tong_don;
                            $sale_status = "pending";
                            $payment_status = "due";
                            $payment_term = 0;
                            $doitac = 0;
                            $due_date =  null;
                            $shipping =  0;
                            $customer_details = $this->site->getCompanyByID($customer_id);
                            $customer = $customer_details->name;
                            $biller_details = $this->site->getCompanyByID($biller_id);
                            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                            $ghichu=$cart->customer_note;
                            $note = $this->sma->clear_tags($ghichu);
                            $staff_note = $woo_url;
                            $quote_id = null;
                            $is_web = null;
                            
                            $data = array('date' => date('Y-m-d H:i:s',strtotime($cart->date_created)),
                            'reference_no' => $cart->number,
                            'customer_id' => $customer_id,
                            'customer' => $customer,
                            'biller_id' => $biller_id,
                            'biller' => $biller,
                            'warehouse_id' => $warehouse_id,
                            'note' => $note,
                            'staff_note' => $staff_note,
                            'total' => $cart->total,
                            'doitac' => 0,
                            'product_discount' => 0,
                            'order_discount_id' => 0,
                            'order_discount' => 0,
                            'total_discount' => $cart->discount_total,
                            'product_tax' => 0,
                            'order_tax_id' => 0,
                            'order_tax' => 0,
                            'total_tax' => $cart->total_tax,
                            'shipping' => $this->sma->formatDecimal($cart->shipping_total),
                            'grand_total' => $cart->total,
                            'total_items' => $tong_don,
                            'sale_status' => $sale_status,
                            'payment_status' => $payment_status,
                            'payment_term' => $payment_term,
                            'due_date' => $due_date,
                            'paid' => 0,
                            'created_by' => $this->session->userdata('user_id'),
                            'is_web' => $cart->id,
                            'fromweb' => $woo_url,
                                        );      
                            
                            //$this->sma->print_arrays($data);  
                            //tien hanh loc qua tat ca san pham
                            $products=array();
                            if(count($cart->line_items)>0){
                                foreach($cart->line_items as $item){
                                    //check sku in product_code
                                    $sku=$item->sku;
                                    if ((int)$item->variation_id>0) {                                                                                                                       
                                         $sku=$sku."_".strtolower($item->variation_id);
                                    }                                  
                                    
                                    $product_id=$this->sales_model->checkProductBySkuWooApi($sku);
                                    if($product_id>0){
                                        $pro_obj=$this->sales_model->getProductByIdWooApi($product_id);
                                        $item_net_price=$item->price;
                                        $unit = $this->site->getUnitByID($pro_obj->sale_unit);
                                        //bien the variation_id
                                        $products[] = array(
                                            'product_id' => $product_id,
                                            'product_code' => $sku,
                                            'product_name' => $pro_obj->name,
                                            'product_type' => $pro_obj->type,
                                            '_POST_id' => '',
                                            'net_unit_price' => $item_net_price,
                                            'unit_price' => $this->sma->formatDecimal($item_net_price),
                                            'quantity' => $item->quantity,
                                            'product_unit_id' => $pro_obj->sale_unit,
                                            'product_unit_code' => $unit ? $unit->code : NULL,
                                            'unit_quantity' => $item->quantity,
                                            'warehouse_id' => $warehouse_id,
                                            'item_tax' => '',
                                            'tax_rate_id' => '',
                                            'tax' => '',
                                            'discount' => 0,
                                            'item_discount' => 0,
                                            'subtotal' => $this->sma->formatDecimal($item->subtotal),
                                            'serial_no' => '',
                                            'real_unit_price' => $item_net_price,
                                        );
                                        krsort($products);
                                        $success[]="Đơn hàng ".$cart->number." đã được đồng bộ với sản phẩm [".$item->name."]";
                                    }else{
                                        $error[]="Sản phẩm [".$item->name."] không tồn tại sku [".$sku."] trên hệ thống, đơn hàng ".$cart->number." chưa được đồng bộ";
                                    }
                                }
                            }
                            if(count($data)>0&&count($products)>0){
                                if(!$this->sales_model->addSale($data, $products, NULL)){
                                    $error[]="Đơn hàng ".$cart->number." chưa được đồng bộ có lỗi xảy ra";
                                }else{
                                    
                                }
                            }
                        }   
                    }
                }else{
                   $this->session->set_flashdata('error',"Chưa có đơn hàng cần xử lý");
                   redirect("sales/web");
                }
                $ketqua="";
                if(count($error)>0){
                    $ketqua.=implode("<hr/>",$error);
                }   
                if(count($success)>0){
                    $ketqua.="<br/>";
                    $ketqua.=implode("<hr/>",$success);
                }
                $this->session->set_flashdata('warning', $ketqua);
                redirect("sales/web");
            }catch (Exception $e ) {

                return $e->getMessage(); 
            }
        }
    }
    function SysnApiWoooProductsV3(){

        $woo_url=json_decode($this->Settings->woo_url);
        $woo_key=json_decode($this->Settings->woo_key);
        $woo_sec=json_decode($this->Settings->woo_sec);
        $str='';
        foreach ($woo_url as $index=>$value) 
        {
            if ($value!="") {
                $this->SysnApiWoooProductsV2($value,$woo_key[$index],$woo_sec[$index]);
            }
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }
    function SysnApiWoooProductsV2($woo_url='',$woo_key='',$woo_sec='')
    {        
        if($woo_url!=''&&$woo_key!=''&&$woo_sec!=''){
              
            try{
                $woocommerce = new Client($woo_url,$woo_key,$woo_sec,
                    [
                        'version' => 'wc/v3',
                        'debug'           => true,
                        'return_as_array' => false,
                        'validate_url'    => false,
                        'timeout'         => 30,
                        'ssl_verify'      => false,
                    ]
                );
                
                $error=array();
                $success=array();
                
                $products=$woocommerce->get('products?status=publish&per_page=100&page=1');
                for ($m = 2; $m <10 ; $m++) {
                    $product_i=$woocommerce->get('products?status=publish&per_page=100&page='.$m);
                    if (count($product_i)>0) {
                         $products=array_merge($products,$product_i);
                    }
                   
                }
                $tong_don=count($products);          
                
              
                //tien hanh xu ly dong bo don hang  
                $arrResult=array();             
                $keys = array('name', 'code', 'barcode_symbology', 'brand', 'category_code', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'image', 'subcategory_code', 'variants', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6','id_woo');                             
                $rw = 2;
                $max_id=0;
               // $arr_product=$this->auth_model->getLastProductIdNuocSuoi();

                if($tong_don>0){ 
                    
                    foreach($products as $csv_pr){  
                        
                       // if(!in_array((int)$csv_pr->id,$arr_product))
                        if (true) 
                        {
                           
                            $csv_pr->category_code='default';
                            $csv_pr->brand='scodeweb';  
                            $csv_pr->unit='cai';  
                            $csv_pr->sale_unit='cai';  
                            $csv_pr->purchase_unit='cai';  
                           
                           
                                //if ( ! $this->products_model->getProductByCode(trim($csv_pr->sku))&&$csv_pr->sku!=null&&trim($csv_pr->sku)!='null') 
                                if (true){
                                        
                                    if ($catd = $this->products_model->getCategoryByCode(trim($csv_pr->category_code))) {
                                       
                                        if($csv_pr->sku!=''&&$csv_pr->sku!=null&&$csv_pr->status=='publish'&&$csv_pr->sku){

                                            if ((float)str_replace("* ","",trim($csv_pr->price))>0) {
                                                                                              
                                                $brand = $this->products_model->getBrandByName(trim($csv_pr->brand));
                                                $unit = $this->products_model->getUnitByCode(trim($csv_pr->unit));
                                                
                                                $base_unit = $unit ? $unit->id : NULL;
                                                $sale_unit = $base_unit;
                                                $purcahse_unit = $base_unit;
                                                if ($base_unit) {
                                                    $units = $this->site->getUnitsByBUID($base_unit);
                                                    foreach ($units as $u) {
                                                        if ($u->code == trim($csv_pr->sale_unit)) {
                                                            $sale_unit = $u->id;
                                                        }
                                                        if ($u->code == trim($csv_pr->purchase_unit)) {
                                                            $purcahse_unit = $u->id;
                                                        }
                                                    }
                                                }
                                               
                                                if (count($csv_pr->variations)>0) {
                                                    foreach ($csv_pr->attributes as $attr) {

                                                        if ($attr->visible==true&&$attr->variation==true) {
                                                          
                                                            foreach ($csv_pr->variations as $value) {
                                                                //get all variations
                                                                   $obj_variation=$woocommerce->get('products/'.$csv_pr->id.'/variations/'.$value);

                                                                    $skusub=trim($csv_pr->sku).'_'.strtolower($value);
                                                                    if ( ! $this->products_model->getProductByCode(trim($skusub)))
                                                                    {
                                                                        $pr_code[] = $skusub;
                                                                        $tensp=$csv_pr->name." - ".$obj_variation->attributes[0]->_POST;
                                                                        $pr_name[]=$tensp;
                                                                        $pr_cat[] = $catd->id;
                                                                        $pr_variants[] = NULL;
                                                                        $pr_brand[] = $brand ? $brand->id : NULL;
                                                                        $pr_unit[] = $base_unit;
                                                                                                                                                
                                                                        $sale_units[] = $sale_unit;
                                                                        $purcahse_units[] = $purcahse_unit; 
                                                                        $tax_method[] = 0;
                                                                        $prsubcat = NULL;
                                                                        $pr_subcat[] = NULL;                             
                                                                        $pr_cost[] = 0;
                                                                        $check_price = str_replace("* ","",trim($csv_pr->price));
                                                                        //tien hanh lay gia thuoc tinh
                                                                        $check_img=$csv_pr->images[0]->src;
                                                                         if (count($csv_pr->variations)>0) {
                                                                             //tien lay gia ban tung bien the
                                                                             foreach ($csv_pr->variations as $bienthe) {
                                                                                 $bt=$woocommerce->get('products/'.$csv_pr->id.'/variations/'.$bienthe);
                                                                                
                                                                                 if ($value==$bt->attributes[0]->_POST) {
                                                                                     $check_price=$bt->price;
                                                                                     $check_img=$bt->image->src;
                                                                                 }
                                                                                 
                                                                             }
                                                                         }
                                                                        $pr_price[] = $check_price;
                                                                        if($check_img!=""){
                                                                            //tien hanh luu hinh anh ve host
                                                                            $content = file_get_contents(trim($check_img));
                                                                            //Store in the filesystem.
                                                                            file_put_contents("assets/uploads/".trim($skusub).".jpg", $content);
                                                                            file_put_contents("assets/uploads/thumbs/".trim($skusub).".jpg", $content);
                                                                            $pr_image[] = trim($skusub).".jpg";    
                                                                        }else{
                                                                            $pr_image[] = NULL;
                                                                        }
                                                                        $pr_aq[] = 0;
                                                                        $tax_details = NULL;
                                                                        $pr_tax[] = NULL;
                                                                        $bs[] = 'code128';
                                                                        $cf1[] = null;
                                                                        $cf2[] = null;
                                                                        $cf3[] = null;
                                                                        $cf4[] = null;
                                                                        $cf5[] = null;
                                                                        $cf6[] = null;
                                                                        
                                                                        $id_nuocsuoi[] =(int)$csv_pr->id; 
                                                                    }
                                                                    
                                                              }  
                                                        }

                                                    }
                                                }else {
                                                    
                                                    if ( ! $this->products_model->getProductByCode(trim($csv_pr->sku)))
                                                    {
                                                        $pr_code[] = trim($csv_pr->sku);
                                                        $tensp=$csv_pr->name;
                                                        $pr_name[]=$tensp;
                                                        $pr_cat[] = $catd->id;
                                                        $pr_variants[] = NULL;
                                                        $pr_brand[] = $brand ? $brand->id : NULL;
                                                        $pr_unit[] = $base_unit;
                                                        
                                                        if($csv_pr->images&&$csv_pr->images[0]->src!=""){
                                                            //tien hanh luu hinh anh ve host
                                                            $content = file_get_contents(trim($csv_pr->images[0]->src));
                                                            //Store in the filesystem.
                                                            file_put_contents("assets/uploads/".trim($csv_pr->sku).".jpg", $content);
                                                            file_put_contents("assets/uploads/thumbs/".trim($csv_pr->sku).".jpg", $content);
                                                            $pr_image[] = trim($csv_pr->sku).".jpg";    
                                                        }else{
                                                            $pr_image[] = NULL;
                                                        }
                                                        
                                                        $sale_units[] = $sale_unit;
                                                        $purcahse_units[] = $purcahse_unit; 
                                                        $tax_method[] = 0;
                                                        $prsubcat = NULL;
                                                        $pr_subcat[] = NULL;                             
                                                        $pr_cost[] = 0;
                                                        $pr_price[] = str_replace("* ","",trim($csv_pr->price));                                                        
                                                        $pr_aq[] = 0;
                                                        $tax_details = NULL;
                                                        $pr_tax[] = NULL;
                                                        $bs[] = 'code128';
                                                        $cf1[] = null;
                                                        $cf2[] = null;
                                                        $cf3[] = null;
                                                        $cf4[] = null;
                                                        $cf5[] = null;
                                                        $cf6[] = null;
                                                        
                                                        $id_nuocsuoi[] =(int)$csv_pr->id;
                                                    }    
                                                    
                                                }           
                                        
                                            }
                                        }                                  
                                    } 
                                }else{
                                    echo var_dump("DA TON TAI:".$csv_pr->sku);
                                }
                             
                        }else{
                            //tien hanh cap nhat gia ban 
                           $this->auth_model->updatePriceProductLhson((int)$csv_pr->id,str_replace("* ","",trim($csv_pr->price)),$csv_pr->name);    
                        }
                    }
                    $ikeys = array('code', 'barcode_symbology', 'name', 'brand', 'category_id', 'unit', 'sale_unit', 'purchase_unit', 'cost', 'price', 'alert_quantity', 'tax_rate', 'tax_method', 'subcategory_id', 'variants', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'image','id_woo');

                    $items = array();
                    foreach (array_map(null, $pr_code, $bs, $pr_name, $pr_brand, $pr_cat, $pr_unit, $sale_units, $purcahse_units, $pr_cost, $pr_price, $pr_aq, $pr_tax, $tax_method, $pr_subcat, $pr_variants, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $pr_image,$id_nuocsuoi) as $ikey => $value) {
                        
                            $items[] = array_combine($ikeys, $value);
                                                
                    }
                    
                   // $this->sma->print_arrays($items);     
                    if(count($items)>0){
                        $this->products_model->add_products($items);
                        //tien hanh update 
                    }                       
                    
                }       

                $this->session->set_flashdata('warning', "Đã đồng bộ ".count($items)." sản phẩm từ web ".$woo_url);
              //  redirect("products");
            }catch (Exception $e ) {
                echo var_dump($e->getMessage());
                return $e->getMessage();
            }
        }
    }
    
    function AutoBackup()
    {        
        $this->load->dbutil();
        $prefs = array(
            'format' => 'txt',
            'filename' => 'Auto_db_'.date("Y").'_'.date("m").'_'.date("d").'.sql'
        );
        $back = $this->dbutil->backup($prefs);
        $backup =& $back;
        $db_name = 'db-backup-on-' . date("Y-m-d-H-i-s") . '.txt';
        $save = './files/backups/' . $db_name;
        $this->load->helper('file');
        write_file($save, $backup);
        $message='Auto backup từ website <a href="'.base_url().'">'.base_url().'</a><br/>';
        $message.='Đây là file backup ngày '.date("d-m-Y")." lúc ".date("H:i:s");
        $bcc=null;
        $cc=null;
        $attachment='./assets/autobk/' . $db_name;
       
        
        $emailnhan=$this->Settings->default_email;
        if ($emailnhan!="") {
            write_file($attachment, $backup);

            $this->sma->send_email($emailnhan, "Auto backup ".$this->Settings->site_name." | ".date("d-m-Y"),$message, null, null, $attachment, $cc, $bcc);

            unlink($attachment); 
        }       

        echo lang('db_saved'); 
    }
    function autoSysnWooStock()
    {
        //lay danh sach tat ca san pham co ban hang va nhap hang trong 5p
        $d_left=date("Y-m-d H:i:s",strtotime("-600 seconds"));
        $query_nhap="SELECT b.product_id FROM scodeweb_purchases a, scodeweb_purchase_items b WHERE a.id=b.purchase_id and a.date>='".$d_left."' GROUP BY b.product_id";
        $q=$this->db->query($query_nhap);
        $product_list=null;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               $product_list[] = $row->product_id;
            }
        }
       $query_ban="SELECT b.product_id FROM scodeweb_sales a, scodeweb_sale_items b WHERE a.id=b.sale_id and a.date>='".$d_left."' GROUP BY b.product_id";
        $q=$this->db->query($query_ban);
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $product_list[] = $row->product_id;
            }
        }
        if (count($product_list)>0) {
            foreach ($product_list as $product_id) {
                $this->UpdateStockWooV1($product_id);
            }    
        }
       // echo var_dump($product_list);
        redirect($_SERVER["HTTP_REFERER"]);
    }
    function UpdateStockWooV1($product_id=0){
        if ($product_id>0&&$this->Settings->autosync==1) {

            $woo_url=json_decode($this->Settings->woo_url);
            $woo_key=json_decode($this->Settings->woo_key);
            $woo_sec=json_decode($this->Settings->woo_sec);
            $tonkhohientai=$this->site->tonkhohientai($product_id);

            $str='';
            foreach ($woo_url as $index=>$value) 
            {
                if ($value!="") {
                    
                    $this->UpdateStockWooWordpress($value,$woo_key[$index],$woo_sec[$index],$product_id,$tonkhohientai);
                }
            }
        }
    }
    function UpdateStockWooWordpress($woo_url='',$woo_key='',$woo_sec='',$product_id=0,$tonkhohientai=0){
        //lhson code 24/12/2020       
        if($woo_url!=''&&$woo_key!=''&&$woo_sec!=''&&(int)$this->Settings->autosync==1){
              
            try{
                $woocommerce = new Client($woo_url,$woo_key,$woo_sec,
                    [
                        'version' => 'wc/v3',
                        'debug'           => true,
                        'return_as_array' => false,
                        'validate_url'    => false,
                        'timeout'         => 30,
                        'ssl_verify'      => false,
                    ]
                );
                //get woo id product by ID
                $prd=$this->site->getProductByID($product_id);
                if ((int)$prd->id_woo>0) {
                    $sku=$prd->code;
                    if ($tonkhohientai>0) {
                         $data = [
                            'manage_stock' => 'true','stock_quantity'=>$tonkhohientai,'stock_status'=>'instock','price'=>number_format($prd->price,null, '.', ''),'regular_price'=>number_format($prd->price,null, '.', '')
                        ];
                    }else{
                         $data = [
                            'manage_stock' => 'true','stock_quantity'=>$tonkhohientai,'stock_status'=>'outofstock','price'=>number_format($prd->price,null, '.', ''),'regular_price'=>number_format($prd->price,null, '.', '')
                        ];
                    }
                    //outofstock | instock                   

                    $products=$woocommerce->get('products/?sku='.$sku);
                    
                    foreach ($products as $pr)
                    {                 
                                                   
                        $rs=$woocommerce->put('products/'.$pr->id,$data);
                         echo var_dump($product_id);                
                    }
                }
                
            }catch (Exception $e ) {
                echo var_dump($e->getMessage());
            }   
        }
    }
     function payment()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
         if (!$this->Owner && !$this->Admin) {
             $this->session->set_flashdata('warning', lang("access_denied"));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->data['title'] = lang('Lịch sử gia hạn');
       
        $list_using=$this->site->loadListUsingByUser();    
        
        $this->data['list_using']=$list_using['list'];
        $this->data['package_api']=$this->site->getAllPackage();


        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Lịch sử gia hạn')));
    
        $meta = array('page_title' => lang('Lịch sử gia hạn'), 'bc' => $bc);
        $this->page_construct('auth/payment', $meta, $this->data);
    }
    
    function setNewPermission($id=0){
        if ($id>0) {
            
            $user = $this->site->getUser();
            $permission=json_decode($user->new_permission);
            $new_permission=[];
            $new_permission[]=$user->group_id;
            foreach ($permission as $p) {
                if ($id!=$p) {
                    $new_permission[]=$p;
                }
            }
            $data = array('group_id' => $id,'new_permission'=>json_encode($new_permission));
            
            if (!in_array($id,$permission))
            {
               exit('ERROR');                                                       
            }
            if ($this->ion_auth->update($user->id, $data)) {
                $this->session->set_userdata(array('group_id'=>$id));      
                //get permission
                exit('OK');
            } else {
                exit('ERROR');
            }
        }
        exit();
    }
    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1.0);
        if ($this->Settings->barcode_img) { 
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
            return $imageResource;
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            header("Content-Type: image/svg+xml");
            echo $imageResource;
        }
    }
}
