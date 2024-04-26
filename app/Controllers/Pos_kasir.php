<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PosKasirModel;

class Pos_kasir extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PosKasirModel;	
		$this->data['title'] = 'Kasir';
	}
	
	public function index() {
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/pos-kasir.css');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pos-kasir.js');
		
		$result = $this->model->getAllGudang();
		$id_gudang_selected = '';
		foreach ($result as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
			if ($val['default_gudang'] == 'Y') {
				$id_gudang_selected = $val['id_gudang'];
			}
		}
		$this->data['gudang'] = $gudang;
		$this->data['id_gudang_selected'] = $id_gudang_selected;
		
		$result = $this->model->getJenisHarga();
		$jenis_harga_selected = '';
		foreach ($result as $val) {
			$jenis_harga[$val['id_jenis_harga']] = $val['nama_jenis_harga'];
			if ($val['default_harga'] == 'Y') {
				$jenis_harga_selected = $val['id_jenis_harga'];
			}
		}
		$this->data['jenis_harga'] = $jenis_harga;
		$this->data['jenis_harga_selected'] = $jenis_harga_selected;
		
		$result = $this->model->getSettingPajak();
		foreach ($result as $val) {
			$pajak[$val['param']] = $val['value'];
		}
		$this->data['pajak'] = $pajak;
		
		$result = $this->model->getSettingKasir();
		foreach ($result as $val) {
			$setting_kasir[$val['param']] = $val['value'];
		}
		$this->data['setting_kasir'] = $setting_kasir;
		
		echo view('themes/modern/pos-kasir-form.php', $this->data);
	}
	
	private function validateFormSetting() {
	
		$validation =  \Config\Services::validation();
		$validation->setRule('no_invoice', 'Nama Setting', 'trim|required|min_length[5]');
		$validation->setRule('no_nota_retur', 'Nama Setting', 'trim|required|min_length[5]');
		$validation->withRequest($this->request)->run();
		$form_errors = $validation->getErrors();
		
		return $form_errors;
	}
	
	public function ajaxSaveData() {
		$model = new \App\Models\PenjualanModel;
		$result = $model->saveData();
		// echo json_encode($result);
		// echo '<pre>';
		// print_r($_POST); die;
		// $result['status'] = 'ok';
		// $result['message'] = 'Data berhasil disimpan';
		echo json_encode($result);
	}
	
	public function getDataDTBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataBarang($_GET['id_gudang'], $_GET['id_jenis_harga']);
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$layout = $_GET['item_layout'];
				
		if ($layout== 'list') {
			$no = $this->request->getPost('start') + 1 ?: 1;
			foreach ($query['data'] as $key => &$val) 
			{
				
				$val['stok'] = key_exists($_GET['id_gudang'], $val['list_stok']) ? $val['list_stok'][$_GET['id_gudang']] : 0;
				$val['nama_barang'] = '<div style="min-width:150px">' . $val['nama_barang'] . '<span class="detail-barang" style="display:none">' . json_encode($val) . '</span>
										<div class="list-barang-detail"><small class="rounded badge-clear-success">Stok: <span class="">' . $val['stok'] . '</small></div></div>';
				$val['ignore_harga'] = '<div class="text-end text-nowrap">Rp. ' . format_number($val['harga']) . '</div>';
				$val['stok'] = '<div class="text-end">' . format_number($val['stok']) . '</div>';
				$val['ignore_urut'] = $no;
				$val['ignore_foto'] = '';
				
				if ($val['nama_file']) {
					if ($val['meta_file']) 
					{
						$meta_file = json_decode($val['meta_file'], true);
						$thumbnail = key_exists('thumbnail', $meta_file) ? $meta_file['thumbnail']['small']['filename'] : $val['nama_file'];
						$image_url = base_url() . '/public/files/uploads/' . $thumbnail;
					}
				} else {
					$image_url = base_url() . '/public/images/noimage.png';
				}
				
				$val['ignore_foto'] = '<div style="width:64px"><img src="' . $image_url . '"/></div>';
				$no++;
			}
		} else {
			
			$setting = $this->model->getSettingKasir();
			$setting_kasir = [];
			foreach ($setting as $val) {
				$setting_kasir[$val['param']] = $val['value'];
			}
		
			$list_barang = '<div class="d-flex align-items-center justify-content-center flex-wrap">';
			foreach ($query['data'] as $key => &$val) 
			{
				
				if ($val['nama_file']) {
					$meta_file = json_decode($val['meta_file'], true);
					if ( key_exists('thumbnail', $meta_file) ) {
						$image = $meta_file['thumbnail']['small']['filename'];
					} else {
						// $image = $meta_file['thumbnail']['small']['filename'];
						$image = $val['nama_file'];
					}
					
					$image_url = base_url() . '/public/files/uploads/' . $image;
					
				} else {
					$image_url = base_url() . '/public/images/noimage.png';
				}
				
				// $val['nama_barang'] = '';
				$val['stok'] = key_exists($_GET['id_gudang'], $val['list_stok']) ? $val['list_stok'][$_GET['id_gudang']] : 0;
				$stok = $setting_kasir['item_layout_grid_show_stok'] == 'Y' ? '<small class="rounded badge-clear-success" style="position:absolute;top:0;right:0"><strong>' . format_number($val['stok']) . '</strong></small>' : '';
				$list_barang .= 
				'<button class="item" style="max-width:' . $setting_kasir['item_layout_grid_width'] . 'px;position:relative">
					<div style="width:100%; max-height: ' . $setting_kasir['item_layout_grid_height'] . 'px; overflow:hidden">
						<img style="width:100%" src="' . $image_url . '"/>
						' . $stok . '
					</div>
					<div class="product-prop">
						<h6>'. $val['nama_barang'] . '</h6>
						<span class="detail-barang" style="display:none">' . json_encode($val) . '</span>
						<div class="mt-2">
							<small style="padding: 1px 7px;" class="price text-bg-primary rounded mt-2 fw-bold">' 
								. format_number($val['harga']) . 
							'</small>
						</div>
					</div>
				</button>';
			}
			$list_barang .= '</div>';		
			$query['data'] = [];
			$query['data'][] = ['nama_barang' => $list_barang];
		}
							
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
