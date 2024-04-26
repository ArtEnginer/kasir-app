<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022-2022
*/

namespace App\Models;

class BarangStokModel extends \App\Models\BaseModel
{
	public function getAllBarang() {
		$sql = 'SELECT * FROM barang';
		$query = $this->db->query($sql)->getResultArray();
		$result = [];
		if ($query) {
			foreach ($query as $val) {
				$result[$val['id_barang']] = $val['nama_barang'];
			}
		}
		return $result;
	}
	
	public function getRiwayatStok() 
	{
		$id_barang = $_GET['id_barang'];
		$exp = explode(' s.d. ', $_GET['daterange']);
		list($d, $m, $y) = explode('-', $exp[0]);
		$start_date = $y . '-' . $m . '-' . $d;
		list($d, $m, $y) = explode('-', $exp[1]);
		$end_date = $y . '-' . $m . '-' . $d;
		
		$sql = 'SELECT nama_barang, nama_customer AS nama, no_invoice, penjualan.tgl_invoice AS tgl_transaksi, 0 AS qty_masuk, penjualan_detail.qty AS qty_keluar, "Penjualan" AS keterangan 
				FROM penjualan_detail 
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN customer USING(id_customer)
				WHERE id_barang = ' . $id_barang . ' AND tgl_invoice >= "' . $start_date . '" AND tgl_invoice <= "' . $end_date . '"
				UNION ALL
				SELECT nama_barang, nama_supplier, no_invoice, pembelian.tgl_invoice AS tgl_beli, pembelian_detail.qty AS qty_beli, 0 AS qty_keluar
				, "Pembelian" AS keterangan
				FROM pembelian_detail
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN supplier USING(id_supplier)
				WHERE id_barang = ' . $id_barang . ' AND tgl_invoice >= "' . $start_date . '" AND tgl_invoice <= "' . $end_date . '"
				UNION ALL
				SELECT nama_barang, nama_supplier, no_nota_retur, pembelian_retur.tgl_nota_retur AS tgl_retur, 0 AS qty_masuk, pembelian_retur_detail.qty_retur AS retur_beli
				, "Retur Pembelian" AS keterangan
				FROM pembelian_retur_detail
				LEFT JOIN pembelian_detail USING(id_pembelian_detail)
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN pembelian_retur USING(id_pembelian_retur)
				LEFT JOIN supplier USING(id_supplier)
				WHERE id_barang = ' . $id_barang . ' AND tgl_nota_retur >= "' . $start_date . '" AND tgl_nota_retur <= "' . $end_date . '"
				UNION ALL
				SELECT nama_barang, nama_customer, no_nota_retur, penjualan_retur.tgl_nota_retur AS tgl_retur, penjualan_retur_detail.qty_retur AS retur_jual, 0 AS qty_keluar
				, "Retur Penjualan" AS keterangan
				FROM penjualan_retur_detail
				LEFT JOIN penjualan_detail USING(id_penjualan_detail)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN barang USING(id_barang)
				LEFT JOIN penjualan_retur USING(id_penjualan_retur)
				LEFT JOIN customer USING(id_customer)
				WHERE id_barang = ' . $id_barang . ' AND tgl_nota_retur >= "' . $start_date . '" AND tgl_nota_retur <= "' . $end_date . '"
				UNION ALL
				SELECT nama_barang, "" AS nama_customer, "" AS no_nota, barang_adjusment_stok.tgl_adjusment_stok, IF(adjusment_stok >=0, adjusment_stok, 0) AS qty_masuk, IF(adjusment_stok < 0, adjusment_stok * -1, 0) AS qty_keluar, "Adjusment Stok" AS keterangan
				FROM barang_adjusment_stok
				LEFT JOIN barang USING(id_barang)
				WHERE id_barang = ' . $id_barang . ' AND barang_adjusment_stok.tgl_adjusment_stok >= "' . $start_date . '" AND barang_adjusment_stok.tgl_adjusment_stok <= "' . $end_date . '"
				ORDER BY tgl_transaksi';
		// echo $sql; di
		$result['riwayat_stok'] = $this->db->query($sql)->getResultArray();
		
		$prev_day = date('Y-m-d', strtotime($start_date . ' -1 day'));
		$sql = 'SELECT SUM(saldo_qty) AS saldo_awal
			FROM (
				SELECT -1 * IFNULL(SUM(penjualan_detail.qty), 0) AS saldo_qty
				FROM penjualan_detail 
				LEFT JOIN penjualan USING(id_penjualan)
				WHERE id_barang = ' . $id_barang . ' AND tgl_invoice <= "' . $prev_day . '"
				UNION ALL
				SELECT SUM(pembelian_detail.qty) AS qty_beli
				FROM pembelian_detail
				LEFT JOIN pembelian USING(id_pembelian)
				WHERE id_barang = ' . $id_barang . ' AND tgl_invoice <= "' . $prev_day . '"
				UNION ALL
				SELECT -1 * IFNULL(SUM(pembelian_retur_detail.qty_retur), 0) AS retur_beli
				FROM pembelian_retur_detail
				LEFT JOIN pembelian_detail USING(id_pembelian_detail)
				LEFT JOIN pembelian USING(id_pembelian)
				LEFT JOIN pembelian_retur USING(id_pembelian_retur)
				WHERE id_barang = ' . $id_barang . ' AND tgl_nota_retur <= "' . $prev_day . '"
				UNION ALL
				SELECT SUM(penjualan_retur_detail.qty_retur) AS retur_jual
				FROM penjualan_retur_detail
				LEFT JOIN penjualan_detail USING(id_penjualan_detail)
				LEFT JOIN penjualan USING(id_penjualan)
				LEFT JOIN penjualan_retur USING(id_penjualan_retur)
				WHERE id_barang = ' . $id_barang . ' AND tgl_nota_retur <= "' . $prev_day . '"
				UNION ALL
				SELECT SUM(adjusment_stok) AS adjusment_stok
				FROM barang_adjusment_stok
				LEFT JOIN barang USING(id_barang)
				WHERE id_barang = ' . $id_barang . ' AND barang_adjusment_stok.tgl_adjusment_stok <= "' . $prev_day . '"
				) AS tabel';
		
		$stok_awal = $this->db->query($sql)->getRowArray();
		$result['saldo_awal'] = $stok_awal['saldo_awal'];
		return $result;
	}
	
	public function writeExcel() 
	{
		$id_barang = $_GET['id_barang'];
		$exp = explode(' s.d. ', $_GET['daterange']);
		list($d, $m, $y) = explode('-', $exp[0]);
		$start_date = $y . '-' . $m . '-' . $d;
		list($d, $m, $y) = explode('-', $exp[1]);
		$end_date = $y . '-' . $m . '-' . $d;
		
		require_once(ROOTPATH . "/app/ThirdParty/PHPXlsxWriter/xlsxwriter.class.php");
		
		$colls = [
					'no' 			=> ['type' => '#,##0', 'width' => 5, 'title' => 'No'],
					'nama_barang' => ['type' => 'string', 'width' => 30, 'title' => 'Nama Barang'],
					'nama' 	=> ['type' => 'string', 'width' => 20, 'title' => 'Nama'],
					'no_invoice' 	=> ['type' => 'string', 'width' => 20, 'title' => 'No. Invoice'],
					'tgl_invoice' 	=> ['type' => 'date', 'width' => 13, 'title' => 'Tgl. Invoice'],
					'qty_masuk' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Qty Masuk'],
					'qty_keluar' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Qty Keluar'],
					'saldo' 	=> ['type' => '#,##0', 'width' => 11, 'title' => 'Saldo'],
					'keterangan' 		=> ['type' => 'string', 'width' => 20, 'title' => 'Keterangan'],
					// 'tgl_penjualan' => ['type' => 'datetime', 'width' => 19, 'title' => 'Tgl. Penjualan'],
				];
		
		$col_type = $col_width = $col_header = [];
		foreach ($colls as $field => $val) {
			$col_type[$field] = $val['type'];
			$col_header[$field] = $val['title'];
			$col_header_type[$field] = 'string';
			$col_width[] = $val['width'];
		}
		
		$stok = $this->getRiwayatStok();
		$saldo = $stok['saldo_awal'];
		
		// Excel
		$sheet_name = strtoupper('Penjualan Barang');
		$writer = new \XLSXWriter();
		$writer->setAuthor('Jagowebdev');
		
		$writer->writeSheetHeader($sheet_name, $col_header_type, $col_options = ['widths'=> $col_width, 'suppress_row'=>true]);		
		$writer->writeSheetRow($sheet_name, $col_header);
		$writer->updateFormat($sheet_name, $col_type);
		
		$no = 1;
		
		$row_saldo_awal = ['', 'Saldo Awal', '', '', '', '', '', $saldo, ''];
		$writer->writeSheetRow($sheet_name, $row_saldo_awal);

		foreach ($stok['riwayat_stok'] AS $val) {
			$saldo +=  $val['qty_masuk'];
			$saldo -=  $val['qty_keluar'];	
			array_unshift($val, $no);
			array_splice($val, 7, 0, $saldo);
			/* echo '<pre>';
			print_r($val); die; */
			$writer->writeSheetRow($sheet_name, $val);
			$no++;
		}
		
		/* while ($row = $query->getUnbufferedRow('array')) {
			array_unshift($row, $no);
			$writer->writeSheetRow($sheet_name, $row);
			$no++;
		} */
		
		$tmp_file = ROOTPATH . 'public/tmp/penjualan_barang_' . time() . '.xlsx.tmp';
		$writer->writeToFile($tmp_file);
		return $tmp_file;
	}
}
?>