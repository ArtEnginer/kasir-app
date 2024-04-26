<?php


namespace App\Controllers;

use App\Models\BarangStokModel;
use App\Libraries\JWDPDF;

class Barang_stok extends \App\Controllers\BaseController
{
	public function __construct()
	{

		parent::__construct();

		$this->model = new BarangStokModel;
		$this->data['title'] = 'Laporan Penjualan';

		$this->addJs($this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs($this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		$this->addJs($this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css');
		$this->addStyle($this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css');

		$this->addJs($this->config->baseURL . 'public/vendors/jquery-autocomplete/jquery.autocomplete.min.js');

		$this->addJs($this->config->baseURL . 'public/themes/modern/js/barang-stok.js');
	}

	public function index()
	{
		$this->data['stok'] = ['riwayat_stok' => 0, 'saldo_awal' => 0];
		if (!empty($_GET['daterange']) && !empty($_GET['id_barang'])) {
			$this->data['stok'] = $this->model->getRiwayatStok();
		}

		if (empty($_GET['daterange'])) {
			$start_date = '01-01-' . date('Y');
			$end_date = date('d-n-Y');
		} else {
			$exp = explode(' s.d. ', $_GET['daterange']);
			list($d, $m, $y) = explode('-', $exp[0]);
			$start_date = $d . '-' . $m . '-' . $y;
			list($d, $m, $y) = explode('-', $exp[1]);
			$end_date = $d . '-' . $m . '-' . $y;
		}

		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];

		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];

		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['start_date_db'] = $start_date_db;
		$this->data['end_date_db'] = $end_date_db;
		$this->data['list_barang'] = $this->model->getAllBarang();
		$this->view('barang-stok-form.php', $this->data);
	}

	public function generateExcel($output = '')
	{
		$exp = explode(' s.d. ', $_GET['daterange']);
		list($d, $m, $y) = explode('-', $exp[0]);
		$start_date = $y . '-' . $m . '-' . $d;
		list($d, $m, $y) = explode('-', $exp[1]);
		$end_date = $y . '-' . $m . '-' . $d;

		$filepath = $this->model->writeExcel();
		$filename = 'Riwayat Stok Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';

		switch ($output) {
			case 'raw':
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				return $filepath;
				break;
			default:
				header('Content-disposition: attachment; filename="' . $filename . '"');
				header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				$content = file_get_contents($filepath);
				delete_file($filepath);
				echo $content;
		}
		exit;
	}

	public function ajaxExportExcel()
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generateExcel($output);
	}

	public function generatePdf($output = '')
	{
		$stok = $this->model->getRiwayatStok();

		$exp = explode(' s.d. ', $_GET['daterange']);
		list($d, $m, $y) = explode('-', $exp[0]);
		$start_date = $y . '-' . $m . '-' . $d;
		list($d, $m, $y) = explode('-', $exp[1]);
		$end_date = $y . '-' . $m . '-' . $d;

		$identitas = $this->model->getIdentitas();
		$pdf = new JWDPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
		$pdf->setFooterText('Riwayat stok barang periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date));

		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('Riwayat Stok Barang Periode ' . $start_date . ' s.d. ' . $end_date);
		$pdf->SetSubject('Riwayat Stok Barang');

		// Margin Header
		$pdf->SetMargins(10, 0, 10);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->startDate = $start_date;
		$pdf->endDate = $end_date;
		$pdf->SetPrintHeader(true);
		$pdf->SetPrintFooter(true);

		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$pdf->SetProtection(array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		$margin_left = 10; //mm
		$margin_right = 10; //mm
		$margin_top = 30; //mm
		$font_size = 10;

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('dejavusans', '', $font_size + 4, '', true);
		// Margin Content
		$pdf->SetMargins($margin_left, $margin_top, $margin_right, false);

		$pdf->AddPage();

		// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
		$pdf->SetTextColor(50, 50, 50);
		$pdf->SetFont('helvetica', 'B', $font_size + 4, '', 'default', true);
		$pdf->Cell(0, 0, 'Riwayat Stok Barang', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->SetFont('helvetica', 'B', $font_size + 2, '', 'default', true);
		$pdf->Cell(0, 0, 'Periode: ' . format_date($start_date) . ' s.d. ' . format_date($end_date), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->SetFont('helvetica', '', $font_size, '', 'default', true);

		$pdf->ln(8);
		$pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
		$pdf->Cell(0, 0, 'Nama Barang : ' . $stok['riwayat_stok'][0]['nama_barang'], 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		$pdf->ln(5);

		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:27%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">No Invoice</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Tgl. Invoice</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">Qty Masuk</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">Qty Keluar</th>
					<th style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">Saldo</th>
					<th style="width:17%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="left">Keterangan</th>
				</tr>
			</thead>
			<tbody>
		EOD;

		$no = 1;
		$format_number = 'format_number';
		$format_date = 'format_date';

		$saldo = $stok['saldo_awal'];

		$tbl .= <<<EOD
					<tr>
						<td colspan="5" style="width:71%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="left">Stok awal</td>
						
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($stok['saldo_awal'], true)}</td>
						<td style="width:17%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="left"></td>
					</tr>
					EOD;

		foreach ($stok['riwayat_stok'] as $val) {
			$saldo +=  $val['qty_masuk'];
			$saldo -=  $val['qty_keluar'];

			$datetime = explode(' ', $val['tgl_transaksi']);
			$exp = explode('-', $datetime[0]);
			$tgl_transaksi = $exp[2] . '-' . $exp[1] . '-' . $exp[0];

			$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:27%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[no_invoice]</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$tgl_transaksi</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty_masuk'], true)}</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty_keluar'], true)}</td>
						<td style="width:12%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($saldo, true)}</td>
						<td style="width:17%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="left">$val[keterangan]</td>
					</tr>
					EOD;
			$no++;
		}

		$tbl .= <<<EOD
			</tbody>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');

		$filename = 'Riwayat Stok Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		$filepath = ROOTPATH . 'public/tmp/penjualan_' . time() . '.pdf.tmp';

		switch ($output) {
			case 'raw':
				$pdf->Output($filepath, 'F');
				$content = file_get_contents($filepath);
				echo $content;
				delete_file($filepath);
				break;
			case 'file':
				$pdf->Output($filepath, 'F');
				return $filepath;
				break;
			default:
				$pdf->Output($filename, 'D');
		}
		exit;
	}

	public function ajaxExportPdf()
	{
		$output = '';
		if (@$_GET['ajax'] == 'true') {
			$output = 'raw';
		}
		$this->generatePdf($output);
	}
}
