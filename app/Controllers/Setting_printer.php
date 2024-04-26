<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Controllers;
use App\Models\SettingPrinterModel;

class Setting_printer extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();

		$this->model = new SettingPrinterModel;	
		$this->data['site_title'] = 'Setting Printer';
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/setting-printer.js');
	}
	
	public function index() 
	{
		$data = $this->data;
		if (!empty($_POST['submit'])) 
		{
			$form_errors = $this->validateForm();
			
			if ($form_errors) {
				$data['message'] = ['status' => 'error', 'message' => $form_errors];
			} else {
				
				if (!$this->hasPermission('update_all'))
				{
					$data['message'] = ['status' => 'error', 'message' => 'Role anda tidak diperbolehkan melakukan perubahan'];
				} else {
					$result = $this->model->saveData();
					$data['message'] = ['status' => $result['status'], 'message' => $result['message']];
				}
			}
		}
		
		$query = $this->model->getSetting('printer');
		foreach($query as $val) {
			$data['setting'][$val['param']] = $val['value'];
		}
		
		$data['title'] = 'Edit ' . $this->currentModule['judul_module'];
		
		$this->view('setting-printer-form.php', $data);
	}

	private function validateForm() 
	{
		$validation =  \Config\Services::validation();		
		$validation->setRule('margin_left', 'Margin Kiri', 'trim|required');
		$validation->setRule('margin_right', 'Margin Kanan', 'trim|required');
		$validation->setRule('margin_top', 'Margin Atas', 'trim|required');
		$validation->setRule('margin_bottom', 'Margin Bawah', 'trim|required');
		$validation->setRule('paper_width', 'Lebar Kertas', 'trim|required');
		$validation->setRule('paper_height', 'Tinggi Kertas', 'trim|required');
		
		$validation->withRequest($this->request)
					->run();
		$form_errors =  $validation->getErrors();

		return $form_errors;
	}	
}