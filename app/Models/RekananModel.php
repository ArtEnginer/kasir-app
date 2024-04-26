<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;

class RekananModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getJmlData() {
		$sql = 'SELECT COUNT(*) AS jml_data FROM rekanan';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml_data'];
	}

	public function deleteData() {
		$result = $this->db->table('rekanan')->delete(['id_rekanan' => $_POST['id']]);
		return $result;
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'rekanan'
					];
					
		try {
			$this->db->transException(true)->transStart();
			
			foreach ($list_table as $table) {
				$this->db->table($table)->emptyTable();
				$sql = 'ALTER TABLE ' . $table . ' AUTO_INCREMENT 1';
				$this->db->query($sql);
			}
			
			$this->db->transComplete();
			
			if ($this->db->transStatus() == true)
				return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			
			return ['status' => 'error', 'message' => 'Database error'];
			
		} catch (DatabaseException $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
	
	public function getRekananById($id) {
		$sql = 'SELECT *
				FROM rekanan 
				WHERE id_rekanan = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() 
	{
		$data_db['nama_rekanan'] = $_POST['nama_rekanan'];
		$data_db['alamat'] = $_POST['alamat'];
		$data_db['no_telp'] = $_POST['no_telp'];
		$query = false;
	
		if ($_POST['id']) 
		{
			$query = $this->db->table('rekanan')->update($data_db, ['id_rekanan' => $_POST['id']]);	
			$result['id_rekanan'] = $_POST['id'];
		} else {
			$query = $this->db->table('rekanan')->insert($data_db);
			if ($query) {
				$result['id_rekanan'] = $this->db->insertID();
			}
		}
		
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpane';
		}
		
		return $result;
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM rekanan ';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE 1=1 ';
		if ($search_all) {
			
			foreach ($columns as $val) {
				
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$where_col[] = $val['data'] . ' LIKE "%' . $search_all . '%"';
			}
			 $where .= ' AND (' . join(' OR ', $where_col) . ') ';
		}
		
		// Order		
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data
				FROM rekanan
					' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM rekanan ' . $where . $order  . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>