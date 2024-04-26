<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\SettingKasirModel;

class Setting_kasir extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new SettingKasirModel;	
		$this->data['title'] = 'Setting Kasir';
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/setting-kasir.js');
	}
	
	public function index() {
		
		if (!empty($_POST['submit'])) {
			$error = $this->validateFormSetting();
			if ($error) {
				$this->data['message'] = ['status' => 'error', 'message' => $error];
			} else {
				$message = $this->model->saveSetting();
				$this->data['message'] = $message;
			}
		}
		
		$setting = $this->model->getSetting('kasir');
		$setting_kasir = [];
		foreach ($setting as $val) {
			$setting_kasir[$val['param']] = $val['value'];
		}
		
		$this->data['setting_kasir'] = $setting_kasir;
		$this->view('setting-kasir-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('item_layout', 'Item Layout', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
}
