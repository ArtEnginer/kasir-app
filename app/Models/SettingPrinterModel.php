<?php
namespace App\Models;

class SettingPrinterModel extends \App\Models\BaseModel
{
	public function saveData() 
	{
		$data_db[] = ['type' => 'printer', 'param' => 'lebar_kertas_nota', 'value' => $_POST['lebar_kertas_nota'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'auto_page_break', 'value' => $_POST['auto_page_break'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'ukuran_kertas', 'value' => $_POST['ukuran_kertas'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'paper_width', 'value' => $_POST['paper_width'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'paper_height', 'value' => $_POST['paper_height'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'margin_left', 'value' => $_POST['margin_left'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'margin_top', 'value' => $_POST['margin_top'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'margin_right', 'value' => $_POST['margin_right'] ];
		$data_db[] = ['type' => 'printer', 'param' => 'margin_bottom', 'value' => $_POST['margin_bottom'] ];
		
		$this->db->transStart();
		$this->db->table('setting')->delete(['type' => 'printer']);
		$this->db->table('setting')->insertBatch($data_db);
		$query = $this->db->transComplete();
		$query_result = $this->db->transStatus();
		
		if ($query_result) {
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