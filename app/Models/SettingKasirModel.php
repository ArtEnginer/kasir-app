<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class SettingKasirModel extends \App\Models\BaseModel
{
	public function saveSetting() 
	{
		$result = [];
		
		$data_db[] = ['type' => 'kasir', 'param' => 'item_layout', 'value' => $_POST['item_layout']];
		$data_db[] = ['type' => 'kasir', 'param' => 'item_layout_grid_width', 'value' => $_POST['item_layout_grid_width']];
		$data_db[] = ['type' => 'kasir', 'param' => 'item_layout_grid_height', 'value' => $_POST['item_layout_grid_height']];
		$data_db[] = ['type' => 'kasir', 'param' => 'item_layout_grid_length', 'value' => $_POST['item_layout_grid_length']];
		$data_db[] = ['type' => 'kasir', 'param' => 'item_layout_grid_show_stok', 'value' => $_POST['item_layout_grid_show_stok']];
		$data_db[] = ['type' => 'kasir', 'param' => 'qty_pengali', 'value' => $_POST['qty_pengali']];
		$data_db[] = ['type' => 'kasir', 'param' => 'qty_pengali_suffix', 'value' => $_POST['qty_pengali_suffix']];
		$data_db[] = ['type' => 'kasir', 'param' => 'qty_pengali_text', 'value' => $_POST['qty_pengali_text']];
		$data_db[] = ['type' => 'kasir', 'param' => 'bersihkan_form', 'value' => $_POST['bersihkan_form']];
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'kasir']);
		$this->db->table('setting')->insertBatch($data_db);
		$this->db->transComplete();
		
		if ($this->db->transStatus()) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
}
?>