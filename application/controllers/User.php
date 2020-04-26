<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		check_login();
	}

	public function index()
	{
		$data['title'] = 'My Profile';
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

		$data['page'] = 'user/profile';
		$this->load->view('template/index', $data);
	}

	public function edit()
	{
		$data['title'] = 'Edit Profile';
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

		$this->form_validation->set_rules('name', 'Full Name', 'required|trim');

		if($this->form_validation->run() == false)
		{
			$data['page'] = 'user/edit';
			$this->load->view('template/index', $data);
		}
		else
		{
			$name = $this->input->post('name');
			$email = $this->input->post('email');

			// cek jika ada gambar

			$upload_image = $_FILES['image']['name'];

			if($upload_image)
			{
				$config['upload_path'] = './assets/img/profile/';
				$config['allowed_types'] = 'gif|jpg|png';
				$config['max_size']     = '2048';
				$config['encrypt_name'] = TRUE;
				// $config['max_width'] = '1024';
				// $config['max_height'] = '768';

				$this->load->library('upload', $config);

				if ($this->upload->do_upload('image'))
				{
					$old_image = $data['user']['image'];
					if($old_image != 'default.jpg')
					{
						unlink(FCPATH . 'assets/img/profile/' . $old_image);
					}
					$new_image = $this->upload->data('file_name');

					$this->db->set('image', $new_image);
				}
				else
				{
					echo $this->upload->display_errors();
					die();
				}
			}

			$this->db->set('name', $name);
			$this->db->where('email', $email);
			$this->db->update('user');

			$this->session->set_flashdata('flash', 'Your profile has been updated');

			redirect('User/edit');
		}
	}

	public function changePassword()
	{
		$data['title'] = 'Change Password';
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

		$this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
		$this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[8]|matches[new_password2]');
		$this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[8]|matches[new_password1]');

		if($this->form_validation->run() == false)
		{
			$data['page'] = 'user/changePassword';
			$this->load->view('template/index', $data);
		}
		else
		{
			$current_password = $this->input->post('current_password');
			$new_password = $this->input->post('new_password1');

			if(!password_verify($current_password, $data['user']['password']))
			{
				$this->session->set_flashdata('flash-login', 'Wrong current password !!!');
				redirect('User/changePassword');
			}
			else
			{
				if($current_password == $new_password)
				{
					$this->session->set_flashdata('flash-login', 'New password cannot be the same as current password !!!');
					redirect('User/changePassword');
				}
				else
				{
					$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

					$this->db->set('password', $password_hash);
					$this->db->where('email', $this->session->userdata('email'));
					$this->db->update('user');

					$this->session->set_flashdata('flash', 'Password changed');
					redirect('User/changePassword');
				}
			}

		}

	}

}

/* End of file User.php */
/* Location: ./application/controllers/User.php */