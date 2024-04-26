<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use CodeIgniter\Database\Exceptions\DatabaseException;

class BarangModel extends \App\Models\BaseModel
{
	
	private function sqlQuery() 
	{
		$sql = 'SELECT kode_barang, nama_barang, deskripsi, barcode, satuan, total_stok
				FROM barang 
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN (
					SELECT *, SUM(saldo_stok) AS total_stok FROM (
						SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
						FROM barang_adjusment_stok
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
						FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
						FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
							UNION ALL
						SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
						FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
							UNION ALL
						SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
						FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
							UNION ALL
						SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
							UNION ALL
						SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
						FROM transfer_barang_detail
						LEFT JOIN transfer_barang USING (id_transfer_barang)
					) AS tabel
					GROUP BY id_barang
				) AS tabel_stok USING(id_barang)';
		return $sql;
	}
	
	public function getDataBarang() {
		$sql = $this->sqlQuery();		
		return $this->db->query($sql)->getResultArray();
	}
	
	public function uploadExcel() 
	{
		ini_set('max_execution_time', '900');
		
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
		
		// Satuan
		$barang = $satuan = $kategori = $barang_harga = $barang_image = [];
		$id_user = $this->session->get('user')['id_user'];
		foreach ($reader->getSheetIterator() as $sheet) 
		{
			if (strtolower($sheet->getName()) == 'barang') 
			{
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
						
						if ($val instanceof \DateTime) {
							$val = $val->format('Y-m-d H:i:s');
						}
						
						$data_value[$field] = $val;
						if ($field == 'kategori') {
							$kategori[$val] = ['nama_kategori' => $val
											, 'deskripsi' => $val
											, 'icon' => 'far fa-circle'
											, 'aktif' => 'Y'
											, 'new' => 'Y'
											, 'urut' => 1
										];
						}
						
						if ($field == 'satuan') {
							$satuan[$val] = ['nama_satuan' => $val
											, 'satuan' => $val
										];
						}
					}

					$barang[] = ['kode_barang' => $data_value['kode']
											, 'nama_barang' => $data_value['nama']
											, 'nama_kategori' => $data_value['kategori']
											, 'nama_satuan' => $data_value['satuan']
											, 'stok' => $data_value['stok']
											, 'stok_minimum' => $data_value['stok_minimum']
											, 'deskripsi' => $data_value['deskripsi']
											, 'harga_pokok' => $data_value['harga_pokok']
											, 'harga_jual_ecer' => $data_value['harga_jual_ecer']
											, 'harga_jual_grosir' => $data_value['harga_jual_grosir']
											, 'berat' => $data_value['berat']
											, 'barcode' => $data_value['barcode']
											, 'tgl_input' => date('Y-m-d H:i:s')
											, 'id_user_input' => $id_user
										];

				}
				break;
			}
		}
		
		if (!$barang) {
			return ['status' => 'error', 'message' => 'Data file excel kosong'];
		}
		
		$reader->close();
		delete_file($path . $filename);
		
		// echo '<pre>';
		// print_r($kategori); die;
		// $this->db->transStart();
		$this->db->table('barang_kategori')->insertBatch($kategori);
		$this->db->table('satuan_unit')->insertBatch($satuan);
		
		// Get Kategori
		$result = $this->db->query('SELECT * FROM barang_kategori')->getResultArray();
		foreach ($result as $val) {
			$list_kategori[$val['nama_kategori']] = $val;
		}
		
		// Get satuan
		$result = $this->db->query('SELECT * FROM satuan_unit')->getResultArray();
		foreach ($result as $val) {
			$list_satuan[$val['nama_satuan']] = $val;
		}
	
		foreach ($barang as $index => $val) 
		{
			if (key_exists($val['nama_kategori'], $list_kategori)) {
				$val['id_barang_kategori'] = $list_kategori[$val['nama_kategori']]['id_barang_kategori'];
			}
			if (key_exists($val['nama_satuan'], $list_satuan)) {
				$val['id_satuan_unit'] = $list_satuan[$val['nama_satuan']]['id_satuan_unit'];
			}
			$stok = $val['stok'];
			$harga_pokok = $val['harga_pokok'];
			$harga_jual_ecer = $val['harga_jual_ecer'];
			$harga_jual_grosir = $val['harga_jual_grosir'];
			
			unset ($val['nama_kategori']);
			unset ($val['nama_satuan']);
			unset ($val['stok']);
			unset ($val['harga_pokok']);
			unset ($val['harga_jual_ecer']);
			unset ($val['harga_jual_grosir']);
			
			$this->db->table('barang')->insert($val);
			$id_barang = $this->db->insertID();
			
			// Adjusment Stok
			$data_db = [];
			$data_db['id_barang'] = $id_barang;
			$data_db['id_gudang'] = 1;
			$data_db['adjusment_stok'] = $stok;
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$data_db['id_user_input'] = $id_user;
			$this->db->table('barang_adjusment_stok')->insert($data_db);
			
			// Harga pokok
			if ($harga_pokok) {
				$data_db = [];
				$data_db['id_barang'] = $id_barang;
				$data_db['harga'] = $harga_pokok;
				$data_db['jenis'] = 'harga_pokok';
				$data_db['tgl_input'] = date('Y-m-d H:i:s');
				$data_db['id_user_input'] = $id_user;
				$this->db->table('barang_harga')->insert($data_db);
			}
			
			// Harga jual ecer
			if ($harga_jual_ecer) {
				$data_db = [];
				$data_db['id_barang'] = $id_barang;
				$data_db['id_jenis_harga'] = 1;
				$data_db['harga'] = $harga_jual_ecer;
				$data_db['jenis'] = 'harga_jual';
				$data_db['tgl_input'] = date('Y-m-d H:i:s');
				$data_db['id_user_input'] = $id_user;
				$this->db->table('barang_harga')->insert($data_db);
			}
			
			// Harga jual grosir
			if ($harga_jual_grosir) {
				$data_db = [];
				$data_db['id_barang'] = $id_barang;
				$data_db['id_jenis_harga'] = 2;
				$data_db['harga'] = $harga_jual_grosir;
				$data_db['jenis'] = 'harga_jual';
				$data_db['tgl_input'] = date('Y-m-d H:i:s');
				$data_db['id_user_input'] = $id_user;
				$this->db->table('barang_harga')->insert($data_db);
			}
			
			// Image
			/* $data_db = [];
			$data_db['id_barang'] = $id_barang;
			$data_db['id_file_picker'] = 1;
			$data_db['urut'] = 1;
			$this->db->table('barang_image')->insert($data_db); */
			// $this->db->transComplete();
		}
		
		// echo 'oke'; die;
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$result = ['status' => 'ok', 'message' => 'Data berhasil di masukkan ke dalam tabel barang sebanyak ' . format_ribuan(count($barang)) . ' baris'];
		} else {
			$result = ['status' => 'error', 'message' => 'Data gagal disimpan'];
		}
		
		return $result;
	}
	
	public function deleteAllBarang() {
		
		$list_table = [
						'barang',
						'barang_adjusment_stok',
						'barang_harga',
						'barang_image',
						'pembelian',
						'pembelian_detail',						
						'pembelian_retur',
						'pembelian_retur_detail',
						'pembelian_bayar',
						'pembelian_file',
						'penjualan',
						'penjualan_detail',
						'penjualan_retur',
						'penjualan_retur_detail',
						'penjualan_bayar',							
						'penjualan_retur_dokumen',
						'transfer_barang',
						'transfer_barang_detail'
					];
					
		try {
			$this->db->transException(true)->transStart();
			if ($_POST['delete_image'] == 'Y') {
				$sql = 'SELECT * FROM barang_image';
				$barang_image = $this->db->query($sql)->getResultArray();
			}
			
			foreach ($list_table as $table) {
				$this->db->table($table)->emptyTable();
				$sql = 'ALTER TABLE ' . $table . ' AUTO_INCREMENT 1';
				$this->db->query($sql);
			}
			
			// FIle
			if ($_POST['delete_image'] == 'Y') {
				$config = new \Config\Filepicker();
				if ($barang_image) {
					foreach ($barang_image as $val) 
					{
						$id_file = $val['id_file_picker'];
						
						$sql = 'SELECT * FROM file_picker WHERE id_file_picker = ?';
						$file = $this->db->query($sql, $id_file)->getRowArray();
						
						if ($file) {
							
							$delete = $this->db->table('file_picker')->delete(['id_file_picker' => $id_file]);
							if ($delete) {
								$meta = json_decode($file['meta_file'], true);
								
								$dir = trim($config->uploadPath, '/');
								$dir = trim($dir, '\\');
								$dir = $dir . '/';
								
								// Main File
								if (file_exists($dir . $file['nama_file'])) { 
									$unlink = delete_file($dir . $file['nama_file']);
									/* if (!$unlink) {
										$error[] = 'Gagal menghapus file: ' . $val['filename'];
									} */
								}
								
								// Thumbnail
								if(key_exists('thumbnail', $meta)) 
								{
									foreach ($meta['thumbnail'] as $val) {
										if (file_exists($dir . $val['filename'])) { 
											$unlink = delete_file($dir . $val['filename']);
											/* if (!$unlink) {
												$error[] = 'Gagal menghapus file: ' . $val['filename'];
											} */
										}
									}
								}
								
							} else {
								// $error[] = 'Gagal menghapus data database file ID: ' . $id_file;
							}
						}
					}
					
					$sql = 'SELECT COUNT(*) AS jml FROM file_picker';
					$jml = $this->db->query($sql)->getRowArray();
					if (!$jml['jml']) {
						$sql = 'ALTER TABLE file_picker AUTO_INCREMENT 1';
						$this->db->query($sql);
					}
				}
			}
		
	
			$this->db->transComplete();
			
			if ($this->db->transStatus() == true)
				return ['status' => 'ok', 'message' => 'Data berhasil dihapus'];
			
			return ['status' => 'error', 'message' => 'Database error'];
			
		} catch (DatabaseException $e) {
			return ['status' => 'error', 'message' => $e->getMessage()];
		}
	}
	
	public function writeExcel() 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
						
		$sql = $this->sqlQuery();		
		$query = $this->db->query($sql);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'kode_barang' 	=> ['type' => 'string', 'width' => 10, 'title' => 'Kode Barang'],
					'nama_barang' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Nama Barang'],
					'deskripsi' 	=> ['type' => 'string', 'width' => 30, 'title' => 'Deskripsi'],
					'barcode' 		=> ['type' => 'string', 'width' => 15, 'title' => 'Barcode'],
					'satuan' 		=> ['type' => 'string', 'width' => 5, 'title' => 'Satuan'],
					'total_stok' 	=> ['type' => '#,##0', 'width' => 7, 'title' => 'Stok']
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// Excel
		$sheet_name = strtoupper('Daftar Barang');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		}
		
		$tmp_file = ROOTPATH . 'public/tmp/barang_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function deleteData() 
	{
		$this->db->transBegin();
		$this->db->table('barang')->delete(['id_barang' => $_POST['id']]);
		// $this->db->table('barang_detail')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_adjusment_stok')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_harga')->delete(['id_barang' => $_POST['id']]);
		$this->db->table('barang_image')->delete(['id_barang' => $_POST['id']]);
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	public function getAllGudang() {
		$sql = 'SELECT * FROM gudang';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getAllSatuan() {
		$sql = 'SELECT * FROM satuan_unit';
		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}
	
	public function getBarangById($id) {
		$sql = 'SELECT * FROM barang WHERE id_barang = ?';
		$barang = $this->db->query($sql, trim($id))->getRowArray();
		if ($barang) {
			$sql_image = 'SELECT * FROM barang_image LEFT JOIN file_picker USING(id_file_picker) WHERE id_barang = ? ORDER BY urut';
			$images = $this->db->query($sql_image, $barang['id_barang'])->getResultArray();
			$barang['images'] = $images;
		}
		return $barang;
	}
	
	public function getKategori() 
	{
		$result = [];
		
		$sql = 'SELECT * FROM barang_kategori
				ORDER BY urut';
		
		$kategori = $this->db->query($sql)->getResultArray();

		foreach ($kategori as $val) 
		{
			$result[$val['id_barang_kategori']] = $val;
			$result[$val['id_barang_kategori']]['depth'] = 0;			
		}		
		return $result;
	}
	
	public function getStok($id = 0) {
		
		if (!$id)
			return;
		
		$sql = 'SELECT *, SUM(saldo_stok) AS total_stok FROM (
					SELECT id_barang, id_gudang, adjusment_stok AS saldo_stok, "adjusment" AS jenis
					FROM barang_adjusment_stok
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty as SIGNED) * -1 AS saldo_stok, "penjualan" AS jenis
					FROM penjualan_detail LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, qty_retur AS saldo_stok, "penjualan_retur" AS jenis
					FROM penjualan_retur_detail LEFT JOIN penjualan_detail USING(id_penjualan_detail) LEFT JOIN penjualan USING(id_penjualan)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, qty AS saldo_stok, "pembelian" AS jenis
					FROM pembelian_detail LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang, CAST(qty_retur AS SIGNED) * -1 AS saldo_stok, "pembelian_retur" AS jenis
					FROM pembelian_retur_detail LEFT JOIN pembelian_detail USING(id_pembelian_detail) LEFT JOIN pembelian USING(id_pembelian)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang_asal, CAST(qty_transfer AS SIGNED) * -1 AS saldo_stok, "transfer_keluar" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_barang = ' . $id . '
						UNION ALL
					SELECT id_barang, id_gudang_tujuan, qty_transfer AS saldo_stok, "transfer_masuk" AS jenis 
					FROM transfer_barang_detail
					LEFT JOIN transfer_barang USING (id_transfer_barang)
					WHERE id_barang = ' . $id . '
				) AS tabel
				LEFT JOIN gudang USING(id_gudang)
				GROUP BY id_barang, id_gudang';
	
		$result = $this->db->query($sql, $id)->getResultArray();
		return $result;
	}
	
	public function getHargaPokokByIdBarang($id) 
	{
		$sql = 'SELECT harga FROM barang_harga
				WHERE id_barang = ? AND jenis = "harga_pokok"
				ORDER BY tgl_input DESC LIMIT 1';
				
		$result = $this->db->query($sql, $id)->getRowArray();
		if ($result) 
			return $result['harga'];
		return $result;
	}
	
	public function getBarangByBarcode($code) 
	{		
		$sql = 'SELECT barcode FROM barang
				WHERE barcode = ?';
				
		$result = $this->db->query($sql, $code)->getRowArray();
		return $result;
	}
	
	public function getHargaJualByIdBarang($id = null) 
	{
		if ($id) {
			$sql = 'SELECT *, (SELECT harga  FROM barang_harga
						WHERE id_jenis_harga = jenis_harga.id_jenis_harga AND id_barang = ? AND jenis = "harga_jual"
						ORDER BY tgl_input DESC LIMIT 1
					) AS harga
					FROM jenis_harga ';
			$result = $this->db->query($sql, $id)->getResultArray();
			return $result;
		}
		
		$sql = 'SELECT * FROM jenis_harga ';
		$result = $this->db->query($sql)->getResultArray();
		
		return $result;
	}
	
	public function saveData() 
	{
		$data_db['kode_barang'] = $_POST['kode_barang'];
		$data_db['nama_barang'] = $_POST['nama_barang'];
		$data_db['deskripsi'] = $_POST['deskripsi'];
		$data_db['id_barang_kategori'] = $_POST['id_barang_kategori'];
		$data_db['id_satuan_unit'] = $_POST['id_satuan_unit'];
		$data_db['berat'] = str_replace('.', '', $_POST['berat']);
		$data_db['barcode'] = $_POST['barcode'];
		$data_db['stok_minimum'] = $_POST['stok_minimum'];
		$data_db['tgl_daluarsa'] = null;
		
		if (!empty($_POST['tgl_daluarsa'])) {
			list($d, $m, $y) = explode('-', $_POST['tgl_daluarsa']);
			$tgl_daluarsa = $y . '-' . $m . '-' . $d;
			$data_db['tgl_daluarsa'] = $tgl_daluarsa;
		}
		
		$this->db->transStart();
		
		if ($_POST['id']) 
		{
			$data_db['id_user_edit'] = $_SESSION['user']['id_user'];
			$data_db['tgl_edit'] = date('Y-m-d H:i:s');
			$query = $this->db->table('barang')->update($data_db, ['id_barang' => $_POST['id']]);
			$id_barang = $_POST['id'];
		} else {
			$data_db['id_user_input'] = $_SESSION['user']['id_user'];
			$data_db['tgl_input'] = date('Y-m-d H:i:s');
			$query = $this->db->table('barang')->insert($data_db);
			$id_barang = $this->db->insertID();
		}
		
		if ($query) 
		{
			// Image
			if ($id_barang) {
				$this->db->table('barang_image')->delete(['id_barang' => $id_barang]);
			}
	
			$data_db = [];
			foreach ($_POST['id_file_picker'] as $index => $val) {
				if (!$val)
					continue;
				$data_db[] = ['id_file_picker' => $val, 'id_barang' => $id_barang, 'urut' => ($index + 1) ];
			}
			if ($data_db) {
				$this->db->table('barang_image')->insertBatch($data_db);
			}
			
			// Adjusment stok
			$data_db = [];
			foreach ($_POST['adjusment'] as $index => $val) {
				if (!$val)
					continue;
			
				$val = str_replace('.', '', $val);
				if ($val != 0) {
					list($d, $m, $y) = explode('-', $_POST['tgl_adjusment_stok'][$index]);
					$tgl_adjusment_stok = $y . '-' . $m . '-' . $d;
					$operator = ['plus' => '+', 'minus' => '-'];
					$data_db[] = ['id_barang' => $id_barang, 'id_gudang' => $_POST['id_gudang'][$index], 'adjusment_stok' => $operator[$_POST['operator'][$index]] . $val, 'tgl_adjusment_stok' => $tgl_adjusment_stok,'tgl_input' => date('Y-m-d H:i:s'), 'id_user_input' => $_SESSION['user']['id_user']];
				}
			}
			if ($data_db) {
				$this->db->table('barang_adjusment_stok')->insertBatch($data_db);
			}
			
			// Harga Pokok
			if ($_POST['adjusment_harga_pokok']) {
				$data_db = [
								'id_barang' => $id_barang, 
								'harga' => str_replace('.', '', $_POST['harga_pokok']), 
								'jenis' => 'harga_pokok', 
								'tgl_input' => date('Y-m-d H:i:s'), 
								'id_user_input' => $_SESSION['user']['id_user']
							];
				$this->db->table('barang_harga')->delete(['id_barang' => $id_barang, 'jenis' => 'harga_pokok']);
				$this->db->table('barang_harga')->insert($data_db);
			}
				
			// Harga jual
			$data_db = [];
			foreach ($_POST['harga_jual'] as $index => $val) 
			{
				$val = str_replace('.', '', $val);
				// if ($val != $_POST['harga_awal'][$index]) {
					$data_db[] = [
									'id_barang' => $id_barang, 
									'id_jenis_harga' => $_POST['id_jenis_harga'][$index], 
									'harga' => $val, 
									'jenis' => 'harga_jual', 
									'tgl_input' => date('Y-m-d H:i:s'), 
									'id_user_input' => $_SESSION['user']['id_user']
								];
				// }
			}
			if ($data_db) {
				$this->db->table('barang_harga')->delete(['id_barang' => $id_barang, 'jenis' => 'harga_jual']);
				$this->db->table('barang_harga')->insertBatch($data_db);
			}
			
		}
		
		$this->db->transComplete();
		if ($this->db->transStatus()) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
			$result['id'] = $id_barang;
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function saveDataStok() 
	{
		$id_barang = $_POST['id'];
		$data_db = [];
		
		foreach ($_POST['adjusment'] as $index => $val) 
		{
			if (!$val)
				continue;
			
			$val = str_replace('.', '', $val);
			list($d, $m, $y) = explode('-', $_POST['tgl_adjusment_stok'][$index]);
			$tgl_adjusment = $y . '-' . $m . '-' . $d;
			$operator = ['plus' => '+', 'minus' => '-'];
			if ($val != 0) {
				$data_db[] = ['id_barang' => $id_barang
								, 'id_gudang' => $_POST['id_gudang'][$index]
								, 'adjusment_stok' => $operator[$_POST['operator'][$index]] . $val
								, 'tgl_input' => date('Y-m-d H:i:s')
								, 'id_user_input' => $_SESSION['user']['id_user']
								, 'tgl_adjusment_stok' => $tgl_adjusment
							];
			}
		}
		
		$query = false;
		if ($data_db) {
			$query = $this->db->table('barang_adjusment_stok')->insertBatch($data_db);
		}
			
		if ($query) {
			$result['status'] = 'ok';
			$result['message'] = 'Data berhasil disimpan';
		} else {
			$result['status'] = 'error';
			$result['message'] = 'Data gagal disimpan';
		}
		
		return $result;
	}
	
	public function countAllData() {
		$sql = 'SELECT COUNT(*) AS jml FROM barang';
		$result = $this->db->query($sql)->getRow();
		return $result->jml;
	}
	
	public function getListData() {

		$columns = $this->request->getPost('columns');

		// Search
		$where_stok = '';
		if (!empty($_GET['tampilkan']) && $_GET['tampilkan'] == 'dibawah_stok_minimum') {
			$where_stok = ' AND stok < stok_minimum';
		}
		$where = ' WHERE 1=1 ' . $where_stok;
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
		$order_data = $this->request->getPost('order');
		$order = '';
		if (@strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml_data 
				FROM barang 
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN barang_stok USING(id_barang)
				' . $where;
		$total_filtered = $this->db->query($sql)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		$sql = 'SELECT * FROM barang
				LEFT JOIN (SELECT * FROM barang_image WHERE urut = 1) as barang_image USING(id_barang)
				LEFT JOIN file_picker USING(id_file_picker)
				LEFT JOIN satuan_unit USING(id_satuan_unit)
				LEFT JOIN barang_stok USING(id_barang)
				' . $where . $order  . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>