<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct(); // ini digunakan untuk memanggil costruct yang di ci controller
        $this->load->library('form_validation'); //untuk menjalankan form validation nya 
    }

    public function index()
    {
        $this->form_validation->set_rules('email', 'email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'password', 'required|trim');

        if ($this->form_validation->run() == false) {
            $title['title'] = 'UIB E-Recruitment Buddy';
            $this->load->view('templates/auth_header', $title);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            // validation success
            $this->_login();
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $userData = $this->db->get_where('user', ['email' => $email])->row_array(); //get user data from database

        // validation login
        // if user account valid 
        if ($userData) {

            //check password
            if (password_verify($password, $userData['password'])) {
                $userSession = [
                    'name' => $userData['name'],
                    'email' => $userData['email']
                ];
                $this->session->set_userdata($userSession);
                redirect('user');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password!</div>');
                redirect('auth');
            }
        } else {
            // if error
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email is not registered!</div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        // rule form validation
        $this->form_validation->set_rules('name', 'name', 'required|trim');
        $this->form_validation->set_rules('npm', 'npm', 'required|trim');
        $this->form_validation->set_rules('email', 'email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => 'This email already registered!'
        ]);
        $this->form_validation->set_rules('phoneNumber', 'phone number', 'required|trim');
        // $this->form_validation->set_rules('password', 'password', 'required|trim|min_length[3]|matches[password1]', [
        //     'matches' => 'Password dont match!',
        //     'min_length' => 'Password to short!'
        // ]);
        // $this->form_validation->set_rules('password1', 'password', 'required|trim|matches[password]');

        if ($this->form_validation->run() == false) { //jika validasi gagal makan tampil kan kembali form ini lagi 
            $title['title'] = 'Buddy Registration';
            $this->load->view('templates/auth_header', $title);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'npm' => htmlspecialchars($this->input->post('npm', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'phone_number' => $this->input->post('phoneNumber'),
                'image' => 'default.jpg',
            ];

            $this->db->insert('buddy', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulations! you have successfully registered as a Buddy</div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('nama');
        $this->session->unset_userdata('email');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logout</div>');
        redirect('auth');
    }
}
