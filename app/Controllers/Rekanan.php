<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Controllers;
use App\Models\RekananModel;

class Rekanan extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new RekananModel;	
		$this->data['site_title'] = 'Rekanan';

		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/rekanan.js');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$this->data['jml_data'] = $this->model->getJmlData();
		$this->view('rekanan-result.php', $this->data);
	}
	
	public function ajaxDeleteData() {

		$result = $this->model->deleteData();
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		echo json_encode($result);
	}
	
	public function ajaxDeleteAllData() {

		$result = $this->model->deleteAllData();
		// $result = true;
		if ($result) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	public function ajaxGetFormData() {
		
		if (isset($_GET['id'])) {
			if ($_GET['id']) {
				$this->data['rekanan'] = $this->model->getRekananById($_GET['id']);
				if (!$this->data['rekanan'])
					return;
			}
		}
		
		echo view('themes/modern/rekanan-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		
		$form_errors = $this->validateForm();
	
		if ($form_errors) {
			$message['status'] = 'error';
			$message['message'] = $form_errors;
		} else {
			$message = $this->model->saveData();
		}

		echo json_encode($message);
	}
	
	private function validateForm() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('nama_rekanan', 'Nama Rekanan', 'trim|required');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
	public function getDataDT() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllData();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListData();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="form-inline btn-action-group">'
										. btn_label(
												['icon' => 'fas fa-edit'
													, 'attr' => ['class' => 'btn btn-success btn-edit btn-xs me-1', 'data-id' => $val['id_rekanan']]
													, 'label' => 'Edit'
												])
										. btn_label(
												['icon' => 'fas fa-times'
													, 'attr' => ['class' => 'btn btn-danger btn-delete btn-xs'
																	, 'data-id' => $val['id_rekanan']
																	, 'data-delete-title' => 'Hapus data rekanan : <strong>' . $val['nama_rekanan'] . '</strong>'
																]
													, 'label' => 'Delete'
												]) . 
										
										'</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
