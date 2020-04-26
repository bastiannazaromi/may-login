<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		check_login();
	}

	public function index()
	{
		$data['title'] = 'Menu Management';
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

		$data['menu'] = $this->db->get('user_menu')->result_array();

		$this->form_validation->set_rules('menu', 'Menu', 'required');

		if ($this->form_validation->run() == false)
		{
			$data['page'] = 'menu/index';
			$this->load->view('template/index', $data);
		}
		else
		{
			$this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);
				$this->session->set_flashdata('flash', 'Menu added !!!');
				redirect('Menu');
		}
	}

	public function submenu()
	{
		$data['title'] = 'Submenu Management';
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$this->load->model('M_Submenu', 'submenu');

		$data['submenu'] = $this->submenu->getSubmenu();
		$data['menu'] = $this->db->get('user_menu')->result_array();

		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('menu_id', 'Menu', 'required');
		$this->form_validation->set_rules('url', 'URL', 'required');
		$this->form_validation->set_rules('icon', 'Icon', 'required');

		if ($this->form_validation->run() == false)
		{
			$data['page'] = 'menu/submenu';
			$this->load->view('template/index', $data);
		}
		else
		{
			$data = [
				'title' => $this->input->post('title'),
				'menu_id' => $this->input->post('menu_id'),
				'url' => $this->input->post('url'),
				'icon' => $this->input->post('icon'),
				'is_active' => $this->input->post('is_active')
			];
			$this->db->insert('user_sub_menu', $data);
			$this->session->set_flashdata('flash', 'Submenu added !!!');
			redirect('Menu/submenu');
		}
	}

}

/* End of file Menu.php */
/* Location: ./application/controllers/Menu.php */