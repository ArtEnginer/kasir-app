<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Models;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CustomerModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function deleteData() {
		$result = $this->db->table('customer')->delete(['id_customer' => $_POST['id']]);
		return $result;
	}
	
	public function getCustomerById($id) {
		$sql = 'SELECT * FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				WHERE id_customer = ?';
		$result = $this->db->query($sql, trim($id))->getRowArray();
		return $result;
	}
	
	public function saveData() {
			
		$data_db['nama_customer'] = $_POST['nama_customer'];
		$data_db['alamat_customer'] = $_POST['alamat_customer'];
		$data_db['no_telp'] = $_POST['no_telp'];
		$data_db['email'] = $_POST['email'];
		$data_db['id_wilayah_kelurahan'] = $_POST['id_wilayah_kelurahan'];
		
		$new_name = '';
		$img_db['foto'] = '';
		
		$path = ROOTPATH . 'public/images/foto/';
		
		if (!empty($_POST['id'])) {
			$sql = 'SELECT foto FROM customer WHERE id_customer = ?';
			$img_db = $this->db->query($sql, $_POST['id'])->getRowArray();
			$new_name = $img_db['foto'];
			
			if ($_POST['foto_delete_img']) {
				$del = delete_file($path . $img_db['foto']);
				$new_name = '';
				if (!$del) {
					$data['msg']['message'] = 'Gagal menghapus gambar lama';
					$error = true;
				}
			}
		}
		
		$file = $this->request->getFile('foto');
		
		if ($file && $file->getName())
		{
			//old file
			if ($_POST['id']) {
				if ($img_db['foto']) {
					if (file_exists($path . $img_db['foto'])) {
						$unlink = delete_file($path . $img_db['foto']);
						if (!$unlink) {
							$result['msg']['status'] = 'error';
							$result['msg']['content'] = 'Gagal menghapus gambar lama';
							return $result;
						}
					}
				}
			}
			
			helper('upload_file');
			$new_name =  get_filename($file->getName(), $path);
			$file->move($path, $new_name);
				
			if (!$file->hasMoved()) {
				$result['msg']['status'] = 'error';
				$result['msg']['content'] = 'Error saat memperoses gambar';
				return $result;
			}
		}
		
		$data_db['foto'] = $new_name;
		
		if (@$_POST['id']) 
		{
			$query = $this->db->table('customer')->update($data_db, ['id_customer' => $_POST['id']]);
			$id_customer = $_POST['id'];
		} else {
			$query = $this->db->table('customer')->insert($data_db);
			$id_customer = $this->db->insertID();
		}
		
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id_customer'] = $id_customer;
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function uploadExcel() 
	{
		ini_set('max_execution_time', '900');
		
		helper(['upload_file', 'format']);		
		$file = $this->request->getFile('file_excel');
		if (! $file->isValid())
		{
			throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
		}
				
		require_once 'app/ThirdParty/Spout/src/Spout/Autoloader/autoload.php';
		
		$path = ROOTPATH . 'public/tmp/';
		$filename = upload_file($path, $_FILES['file_excel']);
		$reader = ReaderEntityFactory::createReaderFromFile($path . $filename);
		$reader->open($path . $filename);
		
		$id_user = $this->session->get('user')['id_user'];
		// $this->db->transStart();
		$num = 0;
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) == 'data') 
			{
				$data_customer = [];
				foreach ($sheet->getRowIterator() as $num_row => $row) 
				{
					$cols = $row->toArray();
					if ($num_row == 1) {
						$field_table = $cols;
						$field_name = array_map('strtolower', $field_table);
						continue;
					}
					
					$data_value = [];
					foreach ($field_name as $num_col => $field) 
					{
						$val = null;
						if (key_exists($num_col, $cols) && $cols[$num_col] != '') {
							$val = $cols[$num_col];
						}
						
						$data_value[$field] = trim($val);
					}
					
					if ($data_value) {
						
						//Id Wilayah Kelurahan
						$wilayah = $this->getWilayahKelurahan($data_value['kelurahan'], $data_value['kecamatan'], $data_value['kabupaten'], $data_value['propinsi']);
						$id_wilayah_kelurahan = $wilayah ? $wilayah['id_wilayah_kelurahan'] : null;
						
						$data_customer[] = ['nama_customer' => $data_value['nama_customer']
										, 'alamat_customer' => $data_value['alamat_customer']
										, 'id_wilayah_kelurahan' => $id_wilayah_kelurahan
										, 'no_telp' => $data_value['no_telp']
										, 'email' => $data_value['email']
									];
						
						
					}
					
					$num++;
				}
				if ($data_customer) {
					$this->db->table('customer')->insertBatch($data_customer);
				}
				break;
			}
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		if (!$num) {
			return ['status' => 'error', 'message' => 'Data file excel kosong'];
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil di masukkan ke dalam tabel siswa sebanyak ' . format_ribuan($num) . ' baris'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
		
		return $result;
	}
	
	private function getWilayahKelurahan($nama_kelurahan, $nama_kecamatan, $nama_kabupaten, $nama_propinsi) {
		$sql = 'SELECT * FROM wilayah_kelurahan 
		LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
		LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
		LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
		WHERE nama_kelurahan = ? AND nama_kecamatan = ? AND nama_kabupaten = ? AND nama_propinsi = ?';
		$result = $this->db->query($sql, [ $nama_kelurahan, $nama_kecamatan, $nama_kabupaten, $nama_propinsi ])->getRowArray();
		return $result;
	}
	
	public function countAllData($where) {
		$sql = 'SELECT COUNT(*) AS jml FROM customer ' . $where;
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData($where) {

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		if ($search_all) {
			// Additional Search

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
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$order_data = $this->request->getPost('order');
		$order = '';
		
		if (!empty($_POST) && strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = 'ORDER BY ' . $order_by . ' LIMIT ' . $start . ', ' . $length;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where;
				
		$query = $this->db->query($sql)->getRowArray();
		$total_filtered = $query['jml_data'];
							
		
		// Query Data
		$sql = 'SELECT * FROM customer 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan) 
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)
				' . $where . $order;
		
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>