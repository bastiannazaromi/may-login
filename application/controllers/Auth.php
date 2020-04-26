<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	public function index()
	{
		if($this->session->userdata('email'))
		{
			redirect('User');
		}

		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required|trim');

		if ($this->form_validation->run() == false)
		{
			$data['title'] = 'Login Page';
			$data['page'] = 'auth/login';
			$this->load->view('auth/index', $data, FALSE);
		}
		else
		{
			$this->_login();
		}
	}

	private function _login()
	{
		$email = $this->input->post('email');
		$password = $this->input->post('password');

		$user = $this->db->get_where('user', ['email' => $email])->row_array();

		if ($user)
		{
			if ($user['is_active'] == 1)
			{
				if(password_verify($password, $user['password']))
				{
					$data = [
						'name' => $user['name'],
						'email' => $user['email'],
						'role_id' => $user['role_id']
					];
					$this->session->set_userdata('data_login', $data);
					$this->session->set_userdata($data);

					if ($user['role_id'] == 1)
					{
						redirect('Admin');
					}
					else
					{
						redirect('User');
					}
				}
				else
				{
					$this->session->set_flashdata('flash-login', 'Wrong password !!!');
					redirect('Auth');
				}
			}
			else
			{
				$this->session->set_flashdata('flash-login', 'Email has not been activated');
				redirect('Auth');
			}
		}
		else
		{
			$this->session->set_flashdata('flash-login', 'Email is not registred
				');
			redirect('Auth');
		}
	}

	public function registration()
	{
		if($this->session->userdata('email'))
		{
			redirect('User');
		}

		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
			'is_unique' => 'This email has already registred !'
		]);
		$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[8]|matches[password2]', [
			'matches' => 'Password dont matches !',
			'min_length' => 'Min length 8 caracter !'
		]);
		$this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');
		if ($this->form_validation->run() == false)
		{
			$data['title'] = 'User Registration';
			$data['page'] = 'auth/registration';
			$this->load->view('auth/index', $data, FALSE);
		}
		else
		{
			$data = [
				'name' => htmlspecialchars($this->input->post('name', true)),
				'email' => htmlspecialchars($this->input->post('email', true)),
				'image' => 'default.jpg',
				'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
				'role_id' => 2,
				'is_active' => 0,
				'date_created' => time()
			];

			// siapkan token
			$token = base64_encode(random_bytes(32));
			$user_token = [
				'email' => $this->input->post('email', true),
				'token' => $token,
				'date_created' => time()
			];

			$this->db->insert('user', $data);
			$this->db->insert('user_token', $user_token);

			// kirim email
			$this->_sendEmail($token, 'verify');

			$this->session->set_flashdata('flash', 'Congratulation your account has been created. Please activate your account');
			redirect('Auth');
		}
	}

	private function _sendEmail($token, $type)
	{
		//465
		$config = [
			'protocol'  => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_user' => 'maykomputer2019@gmail.com',
			'smtp_pass' => 'Basbas1909',
			'smtp_port' => 465,
			'mailtype'  => 'html',
			'charset' 	=> 'utf-8',
			'newline' 	=> "\r\n"
		];

		$this->email->initialize($config);

		$this->email->from('maykomputer2019@gmail.com', 'Junior Programmer CodeIgniter');
		$this->email->to($this->input->post('email'));

		if($type == 'verify')
		{
			$this->email->subject('Account Verification');
			$this->email->message('Click this link to verify your account : <a href="'. base_url() . 'Auth/verify?email=' .$this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
		}
		else if ($type == 'forgot')
		{
			$this->email->subject('Reset Password');
			$this->email->message('Click this link to reset your password : <a href="'. base_url() . 'Auth/resetPassword?email=' .$this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
		}

		if($this->email->send())
		{
			return true;
		}
		else
		{
			echo $this->email->print_debugger();
			die();
		}
	}

	public function verify()
	{
		$email = $this->input->get('email');
		$token = trim($this->input->get('token'));

		$user = $this->db->get_where('user', ['email' => $email])->row_array();

		if($user)
		{
			$user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

			if($user_token)
			{
				if(time() - $user_token['date_created'] < (60*60*24))
				{
					$this->db->set('is_active', 1);
					$this->db->where('email', $email);
					$this->db->update('user');

					$this->db->delete('user_token', ['email' => $email]);

					$this->session->set_flashdata('flash', $email . ' has been activated. Please login !!');
					redirect('Auth');
				}
				else
				{
					$this->db->delete('user', ['email' => $email]);
					$this->db->delete('user_toke', ['email' => $email]);

					$this->session->set_flashdata('flash-login', 'Account activation failed ! TOken expired');
					redirect('Auth');
				}
			}
			else
			{
				$this->session->set_flashdata('flash-login', 'Account activation failed ! Wrong token');
				redirect('Auth');
			}
		}
		else
		{
			$this->session->set_flashdata('flash-login', 'Account activation failed ! Wrong email');
			redirect('Auth');
		}
	}

	public function logout()
	{
		// $this->session->sess_destroy($this->session->userdata('data_login'));

		$this->session->unset_userdata('name');
		$this->session->unset_userdata('email');
		$this->session->unset_userdata('role_id');

		$this->session->set_flashdata('flash', 'You have been logout');
		redirect('Auth');
	}

	public function blocked()
	{
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

		$data['title'] = 'Access Blocked';
		$data['page'] = 'auth/blocked';
		$this->load->view('template/index', $data, FALSE);
	}

	public function forgotPassword()
	{
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

		if ($this->form_validation->run() == false)
		{
			$data['title'] = 'Forgot Password';
			$data['page'] = 'auth/forgotPassword';
			$this->load->view('auth/index', $data, FALSE);
		}
		else
		{
			$email = $this->input->post('email');
			$user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();

			if($user)
			{
				$token = base64_encode(random_bytes(32));
				$user_token = [
					'email' => $email,
					'token' => $token,
					'date_created' => time()
				];

				$this->db->insert('user_token', $user_token);
				$this->_sendEmail($token, 'forgot');
				$this->session->set_flashdata('flash', 'Please check your email to reset your password !');
				redirect('Auth');
			}
			else
			{
				$this->session->set_flashdata('flash-login', 'Email is not registred or not activated !');
				redirect('Auth/forgotPassword');
			}
		}
	}

	public function resetPassword()
	{
		$email = $this->input->get('email');
		$token = $this->input->get('token');

		$user = $this->db->get_where('user', ['email' => $email])->row_array();
		if ($user)
		{
			$user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
			if($user_token)
			{
				$this->session->set_userdata('reset_email', $email);
				$this->changePassword();
			}
			else
			{
				$this->session->set_flashdata('flash-login', 'Reset password failed ! Wrong token !');
				redirect('Auth');
			}
		}
		else
		{
			$this->session->set_flashdata('flash-login', 'Reset password failed ! Wrong email !');
			redirect('Auth');
		}
	}

	public function changePassword()
	{
		if(!$this->session->userdata('reset_email'))
		{
			redirect('Auth');
		}
		else
		{
			$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[8]|matches[password2]');
			$this->form_validation->set_rules('password2', 'Password', 'required|trim|min_length[8]|matches[password1]');

			if ($this->form_validation->run() == false)
			{
				$data['title'] = 'CHange Password';
				$data['page'] = 'auth/changePassword';
				$this->load->view('auth/index', $data, FALSE);
			}
			else
			{
				$password = password_hash($this->input->post('password1'),PASSWORD_DEFAULT);
				$email = $this->session->userdata('reset_email');

				$this->db->set('password', $password);
				$this->db->where('email', $email);
				$this->db->update('user');

				$this->session->unset_userdata('reset_email');
				$this->session->set_flashdata('flash', 'Password has been changed');
				redirect('Auth');
			}
		}
	}

}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */