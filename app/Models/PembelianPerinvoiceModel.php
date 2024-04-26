<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class PembelianPerinvoiceModel extends \App\Models\BaseModel
{
	public function getIdentitas() {
		$sql = 'SELECT * FROM identitas 
				LEFT JOIN wilayah_kelurahan USING(id_wilayah_kelurahan)
				LEFT JOIN wilayah_kecamatan USING(id_wilayah_kecamatan)
				LEFT JOIN wilayah_kabupaten USING(id_wilayah_kabupaten)
				LEFT JOIN wilayah_propinsi USING(id_wilayah_propinsi)';
		return $this->db->query($sql)->getRowArray();
	}
	
	public function writeExcel($start_date, $end_date) 
	{
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		$supplier = !empty($_GET['id_supplier']) ? ' AND id_supplier = ' . $_GET['id_supplier'] : '';			
		$sql = 'SELECT nama_supplier, no_invoice, tgl_invoice, total, status 
				FROM pembelian
				LEFT JOIN supplier USING(id_supplier)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $supplier;
				
		$query = $this->db->query($sql, [$start_date, $end_date]);
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_supplier' => ['type' => 'string', 'width' => 30, 'title' => 'Nama Customer'],
					'no_invoice' 	=> ['type' => 'string', 'width' => 20, 'title' => 'No. Invoice'],
					'tgl_invoice' 	=> ['type' => 'date', 'width' => 13, 'title' => 'Tgl. Invoice'],
					'total' 	=> ['type' => '#,##0', 'width' => 13, 'title' => 'Total'],
					'status' 		=> ['type' => 'string', 'width' => 12, 'title' => 'Status']
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		// Excel
		$sheet_name = strtoupper('Pembelian Barang');
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
	
	public function getPembelianByDate($start_date, $end_date) {
		$supplier = !empty($_GET['id_supplier']) ? ' AND id_supplier = ' . $_GET['id_supplier'] : '';
		$sql = 'SELECT *
				FROM pembelian
				LEFT JOIN supplier USING(id_supplier)
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $supplier;
				
		$result = $this->db->query($sql, [$start_date, $end_date])->getResultArray();
		return $result;
	}
	
	public function getAllSupplier() {
		$sql = 'SELECT * FROM supplier';
		$result = $this->db->query($sql)->getResultArray();
		$data = [];
		if ($result) 
		{
			$data[''] = 'Semua';
			foreach ($result as $val) {
				$data[$val['id_supplier']] = $val['nama_supplier'];
			}
		}
		return $data;
	}
	
	public function getResumePembelianByDate($start_date, $end_date) {
		$supplier = !empty($_GET['id_supplier']) ? ' AND id_supplier = ' . $_GET['id_supplier'] : '';
		$sql = 'SELECT IFNULL(SUM(total),0) AS total_pembelian
				FROM pembelian 
				WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $supplier;
		return $this->db->query($sql, [$start_date, $end_date])->getRowArray();
	}
	
	// Penjualan
	public function countAllDataPembelian() {
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian AS tabel WHERE tgl_invoice >= ? AND tgl_invoice <= ?';
		$result = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getRow();
		return $result->jml;
	}
	
	public function getListPembelian() 
	{

		$columns = $this->request->getPost('columns');

		// Search
		$supplier = !empty($_GET['id_supplier']) ? ' AND id_supplier = ' . $_GET['id_supplier'] : '';
		
		$search_all = @$this->request->getPost('search')['value'];
		$where = ' WHERE tgl_invoice >= ? AND tgl_invoice <= ? ' . $supplier;
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
		$sql = 'SELECT COUNT(*) AS jml FROM pembelian 
				LEFT JOIN supplier USING(id_supplier)
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
		$sql = 'SELECT * FROM pembelian 
				LEFT JOIN supplier USING(id_supplier)
				' . $where . $order . ' LIMIT ' . $start . ', ' . $length;
		// echo $sql; die;
		$data = $this->db->query($sql, [$_GET['start_date'], $_GET['end_date']])->getResultArray();
		
		return ['data' => $data, 'total_filtered' => $total_filtered];
	}
}
?>