<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PenjualanModel;
use App\Models\WilayahModel;

class Penjualan extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PenjualanModel;	
		$this->data['site_title'] = 'Penjualan';
		
		$this->addJs($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/flatpickr/dist/flatpickr.min.css');

		$this->addJs ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-loader.css');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jwdmodal/jwdmodal-fapicker.css');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/penjualan.js');
		$this->addStyle ( $this->config->baseURL . 'public/themes/modern/css/modal-pilih-barang.css');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/wilayah.js');
		$this->data['setting_kasir'] = $this->getSetting('kasir');
	}
	
	public function index()
	{
		$this->hasPermissionPrefix('read');
		
		$data = $this->data;
		if (!empty($_POST['delete'])) 
		{
			$this->hasPermissionPrefix('delete', 'penjualan');
			
			$result = $this->model->deleteData();
			// $result = true;
			if ($result) {
				$data['msg'] = ['status' => 'ok', 'message' => 'Data penjualan berhasil dihapus'];
			} else {
				$data['msg'] = ['status' => 'error', 'message' => 'Data penjualan gagal dihapus'];
			}
		}
		$this->view('penjualan-result.php', $data);
	}
	
	public function ajaxGetBarangByBarcode() {
		$data = $this->model->getBarangByBarcode($_GET['code'], $_GET['id_gudang'], $_GET['id_jenis_harga']);
		if ($data) {
			$result = ['status' => 'ok', 'data' => $data];
		} else {
			$result = ['status' => 'error', 'message' => 'Data tidak ditemukan'];
		}
		
		echo json_encode($result);
	}
	
	public function add()
	{
		$this->data['title'] = 'Tambah Data Penjualan';
		$this->data['breadcrumb']['Add'] = '';
		$this->data = array_merge($this->data, $this->setData());
		$this->view('penjualan-form.php', $this->data);
	}
	
	public function ajaxSaveData() {
		$result = $this->model->saveData();
		echo json_encode($result);
	}
	
	public function ajaxDeleteData() {
		$delete = $this->model->deleteData($_POST['id']);
		// $delete = true;
		if ($delete) {
			$result =  ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal dihapus'];
		}
		
		echo json_encode($result);
	}
	
	private function setData() 
	{
		$result = $this->model->getAllGudang();
		foreach ($result as $val) {
			$gudang[$val['id_gudang']] = $val['nama_gudang'];
		}
		
		$result = $this->model->getJenisHarga();
		$jenis_harga_selected = '';
		foreach ($result as $val) {
			$jenis_harga[$val['id_jenis_harga']] = $val['nama_jenis_harga'];
			if ($val['default_harga'] == 'Y') {
				$jenis_harga_selected = $val['id_jenis_harga'];
			}
		}
		
		$pajak = $this->getSetting('pajak');
		
		return ['gudang' => $gudang, 'pajak' => $pajak, 'jenis_harga' => $jenis_harga, 'jenis_harga_selected' => $jenis_harga_selected];
	}
	
	
	public function detailData() 
	{
		$init_data = $this->setData();
		
		$id = $_GET['id'];
		$init_data['penjualan'] = $this->model->getPenjualanById($id);
		if ($init_data['penjualan']) {
			$init_data['jenis_harga_selected'] = $init_data['penjualan']['id_jenis_harga'];
			$init_data['barang'] = $this->model->getPenjualanBarangByIdPenjualan($id);
			$init_data['pembayaran'] = $this->model->getPembayaranByIdPenjualan($id);
		}
		
		return $init_data;
	}
	
	// For mobile
	public function detail() {
		$detail_data = $this->detailData();
		$this->data = array_merge($this->data, $detail_data);
		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/penjualan-mobile-detail.php', $this->data);
		}
	}
	//-- For mobile
	
	public function edit()
	{
		$this->hasPermission('update_all');
		
		$this->data['title'] = 'Edit Penjualan';
		$detail_data = $this->detailData();
		$this->data = array_merge($this->data, $detail_data);
		
		if (empty($_GET['id'])) {
			$this->errorDataNotFound();
		}
		$this->data['breadcrumb']['Edit'] = '';

		if (@$_GET['mobile'] == 'true') {
			echo view('themes/modern/penjualan-form-mobile.php', $this->data);
		} else {
			$this->view('penjualan-form.php', $this->data);
		}
	}
	
	public function invoicePdf() 
	{
		$this->data['setting_kasir'] = $this->getSetting('kasir');
		$this->data['setting_printer'] = $this->getSetting('printer');
		$this->data['setting_piutang'] = $this->getSetting('piutang');
		$this->data['order'] = $this->model->getPenjualanDetail($_GET['id']);
		$this->data['user_input'] = $this->model->getUserById($this->data['order']['order']['id_user_input']);
		
		if (!$this->data['order']) {
			$this->errorDataNotFound();
			return false;
		}
		
		$this->data['identitas'] = $this->model->getIdentitas();
		$this->data['setting'] = $this->getSetting('invoice');
		
		if (empty($this->settingAplikasi['jenis']) || $this->settingAplikasi['jenis'] == 1) {
			echo view('themes/modern/penjualan-invoice-pdf-template1.php', $this->data);
		} else {
			if ($this->data['setting']['template_invoice'] == 'template_1') {
				echo view('themes/modern/penjualan-invoice-pdf-template1.php', $this->data);
			} else {
				echo view('themes/modern/penjualan-invoice-pdf-template2.php', $this->data);
			}
		}
	}
	
	// Penjualan
	public function getDataDTPenjualan() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPenjualan();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataPenjualan();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$val['nama_customer'] = $val['nama_customer'] ?: '-';
			$exp = explode(' ', $val['tgl_penjualan']);
			$val['tgl_penjualan'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['sub_total'] = '<div class="text-end">' . format_number($val['sub_total']) . '</div>';
			$val['neto'] = '<div class="text-end">' . format_number($val['neto']) . '</div>';
			$val['untung_rugi'] = '<div class="text-end">' . format_number($val['untung_rugi']) . '</div>';
			$val['total_diskon_item'] = '<div class="text-end">' . format_number($val['total_diskon_item']) . '</div>';
			
			if ($val['kurang_bayar'] < 0) {
				$val['kurang_bayar'] = 0;
			}
			$val['kurang_bayar'] = '<div class="text-end">' . format_number($val['kurang_bayar']) . '</div>';
			
			if ($val['status'] == 'kurang_bayar') {
				$val['status'] = 'kurang';
			}
			$val['status'] = ucfirst($val['status']);
			
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/penjualan/edit?id=' . $val['id_penjualan'],'label' => '', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Edit Data'] ]) . 
				btn_label(['label' => '', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-penjualan', 'data-id' => $val['id_penjualan'], 'data-delete-message' => 'Hapus data penjualan ?', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Delete Data'] ]) . 
			'</div>';
			
			$attr_btn_email = ['label' => '', 'icon' => 'fas fa-paper-plane', 'attr' => ['data-url' => base_url() . '/penjualan/invoicePdf?email='.$val['email'].'&id=' . $val['id_penjualan'],'data-id' => $val['id_penjualan'],'class' => 'btn btn-primary btn-xs kirim-email'] ];
			if ($val['email']) {
				$attr_btn_email['attr']['data-bs-toggle'] = 'tooltip';
				$attr_btn_email['attr']['data-bs-title'] = 'Kirim Invoice ke Email';
			} else {
				$attr_btn_email['attr']['disabled'] = 'disabled';
				$attr_btn_email['attr']['class'] = $attr_btn_email['attr']['class'] . ' disabled';
			}
			
			$url_nota = base_url() . '/penjualan/printNota?id=' . $val['id_penjualan'];
			$url_invoice = base_url() . '/penjualan/printInvoice?id=' . $val['id_penjualan'];
			$val['ignore_invoice'] = '<div class="btn-action-group">' 
				. btn_link(['url' => $url_nota,'label' => '', 'icon' => 'fas fa-print', 'attr' => ['data-url' => $url_nota, 'class' => 'btn btn-secondary btn-xs print-nota me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Print Nota'] ])
				. btn_link(['url' => $url_invoice,'label' => '', 'icon' => 'fas fa-print', 'attr' => ['data-url' => $url_invoice, 'class' => 'btn btn-warning btn-xs print-invoice me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Print Invoice'] ])
				. btn_link(['url' => base_url() . '/penjualan/invoicePdf?id=' . $val['id_penjualan'],'label' => '', 'icon' => 'fas fa-file-pdf', 'attr' => ['data-filename' => 'Invoice-' . $val['no_invoice'], 'target' => '_blank', 'class' => 'btn btn-danger btn-xs save-pdf me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Download Invoice (PDF)'] ])
				. btn_label( $attr_btn_email ) 
				 . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function printInvoice() 
	{
		$this->data['identitas'] = $this->model->getIdentitas();
		$this->data['order'] = $this->model->getPenjualanDetail($_GET['id']);
		$setting = $this->getSetting('invoice');
		
		$this->data['setting'] = $setting;
		$this->data['setting_kasir'] = $this->getSetting('kasir');
		$this->data['setting_printer'] = $this->getSetting('printer');
		$this->data['penjualan'] = $this->model->getPenjualanById($_GET['id']);
		$this->data['barang'] = $this->model->getPenjualanBarangByIdPenjualan($_GET['id']);
		$this->data['pembayaran'] = $this->model->getPembayaranByIdPenjualan($_GET['id']);
		$this->data['petugas'] = $this->model->getUserById($this->data['penjualan']['id_user_input']);
		$this->data['data'] = 'Data penjualan';
		
		if (empty($this->settingAplikasi['jenis']) || $this->settingAplikasi['jenis'] == 1) 
		{
			echo view('themes/modern/penjualan-invoice-print-template1.php', $this->data);
		} else {
			if ($this->data['setting']['template_invoice'] == 'template_1') {
				echo view('themes/modern/penjualan-invoice-print-template1.php', $this->data);
			} else {
				echo view('themes/modern/penjualan-invoice-print-template2.php', $this->data);
			}
		}
	}
	
	public function printNota() 
	{
		$this->data['identitas'] = $this->model->getIdentitas();
		$setting = $this->getSetting('invoice');
		
		$this->data['setting'] = $setting;
		$this->data['setting_printer'] = $this->getSetting('printer');
		$this->data['penjualan'] = $this->model->getPenjualanById($_GET['id']);
		$this->data['barang'] = $this->model->getPenjualanBarangByIdPenjualan($_GET['id']);
		$this->data['pembayaran'] = $this->model->getPembayaranByIdPenjualan($_GET['id']);
		$this->data['petugas'] = $this->model->getUserById($this->data['penjualan']['id_user_input']);
		$this->data['data'] = 'Data penjualan';
		echo view('themes/modern/penjualan-print-nota.php', $this->data);
	}
	
	public function getDataDTListBarang() {
		echo view('themes/modern/penjualan-list-barang.php', $this->data);
	}
	
	public function getListCustomer() {
		echo view('themes/modern/penjualan-list-customer.php', $this->data);
	}
	
	public function getDataDTCustomer() 
	{
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataCustomer();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataCustomer();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$detail_customer = json_encode($val);
			$val['ignore_urut'] = $no;
			$val['alamat_customer'] = $val['alamat_customer'] . ' ' . $val['nama_kabupaten'];
			$val['no_telp'] = '<div class="text-nowrap">' . $val['no_telp'] . '</div>';
			// Pilih Customer
			$attr_btn = ['data-id-customer' => $val['id_customer'],'class'=>'btn btn-success pilih-customer btn-xs'];
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => $attr_btn]) . '<span style="display:none">' . $detail_customer . '</span>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
	public function getDataDTBarang() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataBarang();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListDataBarang( $_GET['id_gudang'], $_GET['id_jenis_harga'] );
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			$stok_class = '';
			if ($val['stok'] == 0) {
				$stok_class = 'text-danger';
			}
			
			$attr_btn = ['data-id-barang' => $val['id_barang'],'class'=>'btn btn-success pilih-barang btn-xs'];
			if ($val['stok'] == 0) {
				$attr_btn['disabled'] = 'disabled';
			}
			
			$val['nama_barang'] = '<span class="nama-barang">' . $val['nama_barang'] . '</span><span style="display:none" class="detail-barang">' . json_encode($val) . '</span>';
			$val['ignore_harga_jual'] = '<div class="text-end">' . format_number($val['harga_jual']) . '</div>';
			$val['ignore_harga_pokok'] = '<div class="text-end">' . format_number($val['harga_pokok']) . '</div>';
			$val['ignore_stok'] = '<div class="text-end ' . $stok_class . '">' . format_number($val['stok']) . '</div>';
			$val['ignore_urut'] = $no;
			$val['ignore_satuan'] = $val['satuan'];
			$val['ignore_action'] = btn_action([
									'edit' => ['url' => $this->config->baseURL . $this->currentModule['nama_module'] . '/edit?id='. $val['id_barang']]
								, 'delete' => ['url' => ''
												, 'id' =>  $val['id_barang']
												, 'delete-title' => 'Hapus data barang: <strong>'.$val['nama_barang'].'</strong> ?'
											]
							]);
							
			// Pilih barang
			$val['ignore_pilih'] = btn_label(['label' => 'Pilih', 'attr' => $attr_btn]);
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
	
}
