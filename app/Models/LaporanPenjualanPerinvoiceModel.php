<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class LaporanPenjualanPerinvoiceModel extends \App\Models\BaseModel
{
	public function getResumePenjualanByDate($start_date, $end_date) {		
		$jenis_bayar = !empty($_GET['jenis_bayar']) ? $jenis_bayar = ' AND jenis_bayar = "' . $_GET['jenis_bayar'] . '"' : '';
		$id_customer = !empty($_GET['id_customer']) ? $id_customer = ' AND id_customer = "' . $_GET['id_customer'] . '"' : '';
		$sql = 'SELECT IFNULL(SUM(total_qty),0) AS total_qty, IFNULL(SUM(neto),0) AS total_neto, IFNULL(SUM(untung_rugi),0) AS total_untung_rugi 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $jenis_bayar . $id_customer;
		return $this->db->query($sql, [$start_date, $end_date])->getRowArray();
	}
	
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function getAllCustomer() {
		$sql = 'SELECT * FROM customer';
		$query = $this->db->query($sql)->getResultArray();
		$result = [];
		if ($query) {
			$result[''] = 'Semua';
			foreach ($query as $val) {
				$result[$val['id_customer']] = $val['nama_customer'];
			}
		}
		return $result;
	}
	
	public function writeExcel($start_date, $end_date) 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		$jenis_bayar = !empty($_GET['jenis_bayar']) ? $jenis_bayar = ' AND jenis_bayar = "' . $_GET['jenis_bayar'] . '"' : '';
		$id_customer = !empty($_GET['id_customer']) ? $id_customer = ' AND id_customer = "' . $_GET['id_customer'] . '"' : '';		
		$sql = 'SELECT nama_customer, no_invoice, tgl_invoice, sub_total, total_diskon, neto, untung_rugi, IF(kurang_bayar < 0, 0, kurang_bayar) AS kurang_bayar, status 
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $jenis_bayar . $id_customer;
				
		$query = $this->db->query($sql, [$start_date, $end_date]);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_customer' => ['type' => 'string', 'width' => 30, 'title' => 'Nama Customer'],
					'no_invoice' 	=> ['type' => 'string', 'width' => 20, 'title' => 'No. Invoice'],
					'tgl_invoice' 	=> ['type' => 'date', 'width' => 13, 'title' => 'Tgl. Invoice'],
					'sub_total' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Sub Total'],
					'total_diskon' 			=> ['type' => '#,##0', 'width' => 11, 'title' => 'Diskon'],
					'neto' 			=> ['type' => '#,##0', 'width' => 11, 'title' => 'Neto'],
					'untung_rugi' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Untung (Rugi)'],
					'kurang_bayar' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Kurang Bayar'],
					'status' 		=> ['type' => 'string', 'width' => 12, 'title' => 'Status'],
					// 'tgl_penjualan' => ['type' => 'datetime', 'width' => 19, 'title' => 'Tgl. Penjualan'],
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// Excel
		$sheet_name = strtoupper('Penjualan Barang');
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
		
		$tmp_file = ROOTPATH . 'public/tmp/penjualan_barang_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
	
	public function getPenjualanByDate($start_date, $end_date) {
		$jenis_bayar = !empty($_GET['jenis_bayar']) ? $jenis_bayar = ' AND jenis_bayar = "' . $_GET['jenis_bayar'] . '"' : '';				
		$id_customer = !empty($_GET['id_customer']) ? $id_customer = ' AND id_customer = "' . $_GET['id_customer'] . '"' : '';
		$sql = 'SELECT *
				FROM penjualan
				LEFT JOIN customer USING(id_customer)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $jenis_bayar . $id_customer;
				
		$result = $this->db->query($sql, [$start_date, $end_date])->getResultArray();
		return $result;
	}
	
	// Penjualan
	public function countAllDataPenjualan() {
		$id_customer = !empty($_GET['id_customer']) ? $id_customer = ' AND id_customer = "' . $_GET['id_customer'] . '"' : '';
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan AS tabel WHERE tgl_invoice >= ? AND tgl_invoice <= ?' . $id_customer;
		$result = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRow();
		return $result->jml;
	}
	
	public function getListPenjualan() 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$search_all = @$this->request->getPost('search')['value'];
		$jenis_bayar = !empty($_GET['jenis_bayar']) ? $jenis_bayar = ' AND jenis_bayar = "' . $_GET['jenis_bayar'] . '"' : '';
		$id_customer = !empty($_GET['id_customer']) ? $id_customer = ' AND id_customer = "' . $_GET['id_customer'] . '"' : '';
		$where = ' WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $jenis_bayar . $id_customer;
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
		
		// Query Total Filtered
		$sql = 'SELECT COUNT(*) AS jml FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		$total_filtered = $data['jml'];
		
		// Order
		$order_data = $this->request->getPost('order');
		$order = '';
		if (strpos($_POST['columns'][$order_data[0]['column']]['data'], 'ignore_search') === false) {
			$order_by = $columns[$order_data[0]['column']]['data'] . ' ' . strtoupper($order_data[0]['dir']);
			$order = ' ORDER BY ' . $order_by;
		}

		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		// Query Data
		$sql = 'SELECT * FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getResultArray();
		
		// Query Total
		$sql = 'SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto 
				FROM penjualan 
				LEFT JOIN customer USING(id_customer)
				' . $where;
		$total = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRowArray();
		if (!$total) {
			$total = ['total_qty' => 0, 'total_neto' => 0];
		}
		
		foreach ($data as &$val) {
			$val['total'] = $total;
		}
	
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>