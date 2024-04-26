<?php
/**
*	App Name	: Aplikasi Siswa dan Pembayaran SPP Sekolah	
*	Author		: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2023-2023
*/

namespace App\Models;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class PengeluaranModel extends \App\Models\BaseModel
{
	public function __construct() {
		parent::__construct();
	}
	
	public function getKategori() 
	{
		$result = [];
		
		$sql = 'SELECT * FROM pengeluaran_kategori
				ORDER BY urut';
		
		$kategori = $this->db->query($sql)->getResultArray();

		foreach ($kategori as $val) 
		{
			$result[$val['id_pengeluaran_kategori']] = $val;
			$result[$val['id_pengeluaran_kategori']]['depth'] = 0;			
		}		
		return $result;
	}
	
	public function getPendapatanJenis() {
		$sql = 'SELECT * FROM pendapatan_jenis';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getRekanan() {
		$sql = 'SELECT * FROM rekanan';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getRekananByNameAndAlamat($nama, $alamat) {
		$sql = 'SELECT * FROM rekanan WHERE nama_rekanan = ? AND alamat = ?';
		$result = $this->db->query($sql, [$nama, $alamat])->getRowArray();
		return $result;
	}
	
	public function getAllMetodePembayaran() {
		$sql = 'SELECT * FROM jenis_bayar';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getJmlDataPengeluaran() {
		$sql ='SELECT COUNT(*) AS jml FROM pengeluaran';
		$result = $this->db->query($sql)->getRowArray();
		return $result['jml'];
	}
	
	public function getPengeluaranById() {
		$sql = 'SELECT * FROM pengeluaran 
				LEFT JOIN rekanan USING(id_rekanan)
				WHERE id_pengeluaran = ?';
		$data = $this->db->query($sql, $_GET['id'])->getRowArray();
		if ($data) {
			$sql = 'SELECT * 
					FROM pengeluaran_detail 
					WHERE id_pengeluaran = ?';
			$data['detail'] = $this->db->query($sql, $_GET['id'])->getResultArray();
			$sql = 'SELECT * 
					FROM pengeluaran_file_picker
					LEFT JOIN file_picker USING(id_file_picker)
					WHERE id_pengeluaran = ?';
			$data['file'] = $this->db->query($sql, $_GET['id'])->getResultArray();
		}
		return $data;
	}
	
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas_sekolah 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function deleteData() {
		$this->db->table('pengeluaran_file_picker')
					->delete(['id_pengeluaran' => $_POST['id']]);
		$this->db->table('pengeluaran_detail')
					->delete(['id_pengeluaran' => $_POST['id']]);
		$this->db->table('pengeluaran')
					->delete(['id_pengeluaran' => $_POST['id']]);
		
		$this->model->resetAutoIncrement('pengeluaran');
		
		return $this->db->affectedRows();
	}
	
	public function deleteAllData() {
		
		$list_table = [
						'pengeluaran'
						, 'pengeluaran_detail'
						, 'pengeluaran_file_picker'
					];
					
		try {
			$this->db->transException(true)->transStart();
			
			$list_id = [];
			foreach ($list_table as $table) 
			{
				$this->db->table($table)->emptyTable();
				$this->resetAutoIncrement($table);
			}
			
			$this->db->transComplete();
			
			if ($this->db->transStatus() == true)
				return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			
			return ['status' => 'error', 'message' => 'Database error'];
			
		} catch (DatabaseException $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
	
	private function gelLastKategori($array, &$result = []) {
		foreach ($array as $val) {
			if (empty($val['children'])) {
				$result[] = $val;
			} else {
				$this->gelLastKategori($val['children'], $result);
			}
		}
		return $result;
	}
	
	public function uploadExcel() 
	{
		helper(['upload_file', 'format']);
		$path = ROOTPATH . 'public/tmp/';
		
		$file = $this->request->getFile('file_excel');
		if (! $file->isValid())
		{
			throw new RuntimeException($file->getErrorString().'('.$file->getError().')');
		}
				
		require_once 'app/ThirdParty/Spout/src/Spout/Autoloader/autoload.php';
		
		$filename = upload_file($path, $_FILES['file_excel']);
		$reader = ReaderEntityFactory::createReaderFromFile($path . $filename);
		$reader->open($path . $filename);
		
		$warning = [];
		$error_message = [];
		$row_inserted = 0;
		
		$field_mandatory = ['nama_kategori', 'kelompok', 'tgl_pengeluaran', 'jenis_bayar', 'nama_pengeluaran', 'nilai_pengeluaran'];
		
		// Kategori
		$result = $this->getKategori();
		$list_kategori = kategori_list($result);
		$kategori = $this->gelLastKategori($list_kategori);
		
		$list_id_kategori  = [];
		foreach($kategori as $val) {
			$list_id_kategori[$val['nama_kategori']] = $val['id_pengeluaran_kategori'];
		}
		
		$list_kategori_excel = [];
		$list_rekanan_excel = [];
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) == 'list_kategori') 
			{
				foreach ($sheet->getRowIterator() as $num_row => $row) 
				{
					if ($num_row == 1) {
						continue;
					}
					$cols = $row->toArray();
					$list_kategori_excel[$cols[0]] = $cols[1];
				}
			} else if (strtolower($sheet->getName()) == 'list_rekanan') 
			{
				foreach ($sheet->getRowIterator() as $num_row => $row) 
				{
					if ($num_row == 1) {
						continue;
					}
					$cols = $row->toArray();
					$list_rekanan_excel[$cols[0]] = $cols[3];
				}
			}
		}
		

		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) != 'data') {
				continue;
			}
			
			$data_value = [];
			$error = [];
			foreach ($sheet->getRowIterator() as $num_row => $row) 
			{
				$cols = $row->toArray();					
				if ($num_row == 1) {
					$field_table = $cols;
					$field_name = array_map('strtolower', $field_table);
					continue;
				}
				
				foreach ($field_name as $num_col => $field) 
				{
					$val = null;
					if (key_exists($num_col, $cols) && $cols[$num_col] != '') {
						$val = $cols[$num_col];
					}
					
					if (in_array($field, $field_mandatory)) {
						if (!$val) {
							$error[] = 'Semua data kolom ' . $field . ' harus diisi';
							break;
						}
					}
															
					if ($val instanceof \DateTime) {
						$val = $val->format('d-m-Y');
					}
					
					if ($field == 'tgl_bukti' || $field == 'tgl_pengeluaran') {
						if (strlen($val) == 5) {
							
							$base_timestamp = mktime(0,0,0,1,$val-1,1900);
							$val = date("Y-m-d",$base_timestamp);
						} else if (strlen($val) == 10) {
							list($d, $m, $y) = explode('-', $val);
							$val = $y . '-' . $m . '-'. $d;
						}
					}
					
					$data_value[trim($field)][] = $val;
				}
			}
			
			if ($error) {
				return ['status' => 'error', 'message' => $error];
			}
			
			$arrange = [];
			foreach ($data_value['kelompok'] as $key => $val) {
				$arrange[$val][$key] = $key;
			}
			
			$jenis_bayar = ['tunai' => 1, 'transfer' => 2];
			$row_inserted = 0;
			foreach ($arrange as $item) 
			{
				$data_db = [];
				$data_db_detail = [];
				$total_pengeluaran = 0;
				foreach ($item as $index) 
				{
					$nama_rekanan = $data_value['nama_rekanan'][$index];
					$id_rekanan = null;
					if ($nama_rekanan && key_exists($nama_rekanan, $list_rekanan_excel)) {
						$id_rekanan = $list_rekanan_excel[$nama_rekanan];
					}
					
					$data_db_detail[] = ['nama_pengeluaran' => $data_value['nama_pengeluaran'][$index]
										, 'nilai_pengeluaran' => $data_value['nilai_pengeluaran'][$index]
										, 'keterangan' => $data_value['keterangan_pengeluaran'][$index]
										];
					$total_pengeluaran += $data_value['nilai_pengeluaran'][$index];
				}
				
				$data_db = ['id_rekanan' => $id_rekanan
								, 'id_pengeluaran_kategori' => $list_kategori_excel[$data_value['nama_kategori'][$index]]
								, 'no_bukti' => $data_value['no_bukti'][$index]
								, 'tgl_bukti' => $data_value['tgl_bukti'][$index]
								, 'tgl_pengeluaran' => $data_value['tgl_pengeluaran'][$index]
								, 'id_jenis_bayar' => $jenis_bayar[$data_value['jenis_bayar'][$index]]
								, 'total_pengeluaran' => $total_pengeluaran
								, 'id_user_input' => $_SESSION['user']['id_user'] 
								, 'tgl_input' => date('Y-m-d')
								];
				
				$this->db->table('pengeluaran')->insert($data_db);
				$id_pengeluaran = $this->db->insertID();
				
				foreach ($data_db_detail as &$val) {
					$row_inserted++;
					$val['id_pengeluaran'] = $id_pengeluaran;
				}
				
				$this->db->table('pengeluaran_detail')->insertBatch($data_db_detail);
			}
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		$message['status'] = 'ok';
		$message['message'] = 'Data berhasil di masukkan ke dalam database tabel penjualan_detail sebanyak ' . format_ribuan($row_inserted) . ' baris';		
		return $message;
	}
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_pengeluaran' 	=> ['type' => 'string', 'width' => 33, 'title' => 'Nama Pengeluaran'],
					'nama_kategori' 	=> ['type' => 'string', 'width' => 17, 'title' => 'Kategori'],
					'nilai_pengeluaran' 	=> ['type' => '#,##0', 'width' => 16, 'title' => 'Nilai Pengeluaran'],
					'tgl_pengeluaran' 	=> ['type' => 'string', 'width' => 16, 'title' => 'Tgl. Pengeluaran'],
					'keterangan' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Keterangan'],
					'nama_jenis_bayar' 	=> ['type' => 'string', 'width' => 21, 'title' => 'Metode Pembayaran']
				];
		
		$col_type = $col_width = $col_header = [];
		$select_column = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
			if ($field != 'no')
				$select_column[] = $field;
		}
		
		// SQL
		$where = ' WHERE id_pengeluaran IS NOT NULL ';
	
		if (!empty($_GET['daterange'])) {
			$exp = explode(' s.d. ', $_GET['daterange']);
			list($d, $m, $y) = explode('-', $exp[0]);
			$start_date = $y . '-' . $m . '-' . $d;
			
			list($d, $m, $y) = explode('-', $exp[1]);
			$end_date = $y . '-' . $m . '-' . $d;
			$where .= ' AND tgl_pengeluaran >= "' . $start_date . '" AND tgl_pengeluaran <= "' . $end_date . '" ';
		}
		$sql = 'SELECT ' . join(',', $select_column) . '
				FROM pengeluaran
				LEFT JOIN pengeluaran_detail USING(id_pengeluaran)
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				' . $where;
				
		$query = $this->db->query($sql);
		
		// Excel
		$sheet_name = strtoupper('Data Pengeluaran');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			$row['tgl_pengeluaran'] = format_tanggal($row['tgl_pengeluaran'], 'dd-mm-yyyy');
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/pengeluaran_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function saveData() {
		
		// $this->db->transStart();
		
		$data_db['id_rekanan'] = !empty($_POST['id_rekanan']) ? $_POST['id_rekanan'] : null;
		$data_db['no_bukti'] = !empty($_POST['no_bukti']) ? $_POST['no_bukti'] : null;
		$data_db['tgl_bukti'] = null;
		$data_db['id_pendapatan_jenis'] = !empty($_POST['id_pendapatan_jenis']) ? $_POST['id_pendapatan_jenis'] : null;;
		if (!empty($_POST['tgl_bukti'])) {
			$exp = explode('-', $_POST['tgl_bukti']);
			$data_db['tgl_bukti'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		
		$data_db['id_pengeluaran_kategori'] = $_POST['id_pengeluaran_kategori'];
		if (!empty($_POST['tgl_pengeluaran'])) {
			$exp = explode('-', $_POST['tgl_pengeluaran']);
			$data_db['tgl_pengeluaran'] = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		$data_db['id_jenis_bayar'] = $_POST['id_jenis_bayar'];
		
		$total = 0;
		foreach($_POST['nama_pengeluaran'] as $index => $val) {
			$total += str_replace('.', '', $_POST['nilai_pengeluaran'][$index]);
		}
		$data_db['total_pengeluaran'] = $total;
		
		if (!empty($_POST['id']))  
		{
			$data_db['id_user_update'] = $_SESSION['user']['id_user'];
			$data_db['tgl_update'] = date('Y-m-d H:i:s');
			$this->db->table('pengeluaran')->update($data_db, ['id_pengeluaran' => $_POST['id']]);
			$this->db->table('pengeluaran_detail')->delete(['id_pengeluaran' => $_POST['id']]);
			$this->db->table('pengeluaran_file_picker')->delete(['id_pengeluaran' => $_POST['id']]);
			$id_pengeluaran = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$this->db->table('pengeluaran')->insert($data_db);
			$id_pengeluaran = $this->db->insertID();
		}
		
		// Detail
		$data_db_detail = [];
		foreach($_POST['nama_pengeluaran'] as $index => $val) 
		{
			$data_db_detail[] = [
								'id_pengeluaran' => $id_pengeluaran
								, 'nama_pengeluaran' =>  $val
								, 'nilai_pengeluaran' => str_replace('.', '', $_POST['nilai_pengeluaran'][$index])
								, 'keterangan' => $_POST['keterangan'][$index]
							];
		}
		$this->db->table('pengeluaran_detail')->insertBatch($data_db_detail);
				
		$data_db_file = [];
		if (!empty($_POST['id_file_picker'])) {
			foreach($_POST['id_file_picker'] as $index => $val) 
			{
				if (!$val)
					continue;
				
				$data_db_file[] = ['id_pengeluaran' =>  $id_pengeluaran
									, 'id_file_picker' => $val
								];
			}
		}
		
		if ($data_db_file) {
			$this->db->table('pengeluaran_file_picker')->insertBatch($data_db_file);
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	}
	
	/* public function saveDataEdit() {
		
		$result = $this->db->table('siswa_spp_nilai')
						->update(['nilai_spp' => str_replace('.', '', $_POST['nilai_spp'])
									, 'keterangan' => $_POST['keterangan']
								], ['id_siswa' => $_POST['id_siswa']
									, 'bulan' => $_POST['bulan']
									, 'tahun' => $_POST['tahun']
								]										
							);
		
		if ($result) {
			return ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		} else {
			return ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
	} */
	
	public function countAllData() {
		
		/* if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
		} */
		
		$sql = 'SELECT COUNT(*) AS jml 
				FROM pengeluaran WHERE id_pengeluaran IS NOT NULL';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');
		
		$where = ' WHERE id_pengeluaran IS NOT NULL ';
		if (!empty($_GET['kelas'])) {
			$where .= ' AND group_kelas = ' . $_GET['kelas'];  
		}
		// Search
		$search_all = @$this->request->getPost('search')['value'];
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
		
		if (!empty($_GET['daterange'])) {
			$exp = explode(' s.d. ', $_GET['daterange']);
			list($d, $m, $y) = explode('-', $exp[0]);
			$start_date = $y . '-' . $m . '-' . $d;
			
			list($d, $m, $y) = explode('-', $exp[1]);
			$end_date = $y . '-' . $m . '-' . $d;
			$where .= ' AND tgl_pengeluaran >= "' . $start_date . '" AND tgl_pengeluaran <= "' . $end_date . '" ';
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data
				FROM (SELECT id_pengeluaran FROM pengeluaran
				LEFT JOIN pengeluaran_detail USING(id_pengeluaran)
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				' . $where . ' GROUP BY id_pengeluaran) AS tabel';
		$result = $this->db->query($sql)->getRowArray();
		$total_filtered = $result ? $result['jml_data'] : 0;
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT *, GROUP_CONCAT(nama_pengeluaran) AS nama_pengeluaran, GROUP_CONCAT(keterangan) AS keterangan
				FROM pengeluaran
				LEFT JOIN pengeluaran_detail USING(id_pengeluaran)
				LEFT JOIN jenis_bayar USING(id_jenis_bayar)
				LEFT JOIN pengeluaran_kategori USING(id_pengeluaran_kategori)
				' . $where . ' GROUP BY id_pengeluaran ' . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
		
	public function countAllDataRekanan() {
		$sql = 'SELECT COUNT(*) AS jml FROM rekanan ';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListDataRekanan() {

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