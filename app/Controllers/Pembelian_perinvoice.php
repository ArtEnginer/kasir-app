<?php
/**
*	App Name	: Aplikasi Kasir Berbasis Web	
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2022
*/

namespace App\Controllers;
use App\Models\PembelianPerinvoiceModel;
use App\Libraries\JWDPDF;

class Pembelian_perinvoice extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		
		$this->model = new PembelianPerinvoiceModel;	
		$this->data['title'] = 'Pembelian Perinvoice';
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/moment/moment.min.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.js');
		$this->addStyle ( $this->config->baseURL . 'public/vendors/daterangepicker/daterangepicker.css');
		$this->addJs ( $this->config->baseURL . 'public/vendors/filesaver/FileSaver.js');
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
			
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/pembelian-perinvoice.js');
	}
	
	public function index() {
		$start_date = '01-01-' . date('Y');
		$end_date = date('d-n-Y');
		
		$exp = explode('-', $start_date);
		$start_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$exp = explode('-', $end_date);
		$end_date_db = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		
		$this->data['total_pembelian'] = $this->model->getResumePembelianByDate($start_date_db, $end_date_db);
		$this->data['supplier'] = $this->model->getAllSupplier();
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['start_date_db'] = $start_date_db;
		$this->data['end_date_db'] = $end_date_db;
		$this->view('pembelian-perinvoice-result.php', $this->data);
	}
	
	public function ajaxGetResumePembelian() {
		$result = $this->model->getResumePembelianByDate($_GET['start_date'], $_GET['end_date']);
		echo json_encode($result);
	}
		
	public function generatePdf($start_date, $end_date, $output) 
	{
		$pembelian = $this->model->getPembelianByDate($start_date, $end_date);
		
		$identitas = $this->model->getIdentitas();
		$pdf = new JWDPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
		$pdf->setFooterText('Pembelian periode ' . format_date($start_date) . ' s.d. ' . format_date($end_date));
		
		$pdf->setPageUnit('mm');

		// set document information
		$pdf->SetCreator($identitas['nama']);
		$pdf->SetAuthor($identitas['nama']);
		$pdf->SetTitle('List Pembelian Periode ' . $start_date . ' s.d. ' . $end_date);
		$pdf->SetSubject('Pembelian');
		
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
		$pdf->SetTextColor(50,50,50);
		$pdf->SetFont ('helvetica', 'B', $font_size + 4, '', 'default', true );
		$pdf->Cell(0, 0, 'Pembelian Barang', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		$pdf->SetFont ('helvetica', 'B', $font_size + 2, '', 'default', true );
		$pdf->Cell(0, 0, 'Periode: ' . format_date($start_date) . ' s.d. ' . format_date($end_date), 0, 1, 'C', 0, '', 0, false, 'T', 'M' );
		
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

		$pdf->ln(8);
		$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
		$border_color = '#CECECE';
		$background_color = '#efeff0';
		$tbl = <<<EOD
		<table border="0" cellspacing="0" cellpadding="6">
			<thead>
				<tr border="1" style="background-color:$background_color">
					<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
					<th style="width:25%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Nama Supplier</th>
					<th style="width:22%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">No Invoice</th>
					<th style="width:13%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Tgl. Invoice</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Total</th>
					<th style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Status</th>
				</tr>
			</thead>
			<tbody>
		EOD;

			$no = 1;
			$format_number = 'format_number';
			$format_date = 'format_date';
			$total = 0;
			foreach ($pembelian as $val) {
				$datetime = explode(' ', $val['tgl_invoice']);
				$exp = explode('-', $datetime[0]);
				$tgl_invoice = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
				// $status = strtoupper($val['status']);
				$status = strtoupper($val['status']);
				$tbl .= <<<EOD
					<tr>
						<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
						<td style="width:25%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_supplier]</td>
						<td style="width:22%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$val[no_invoice]</td>
						<td style="width:13%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$tgl_invoice</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['total'])}</td>
						<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$val[status]</td>
					</tr>
					EOD;
				$no++;
				$total += $val['total'];
			}
		
			$tbl .= <<<EOD
			</tbody>
			<tfoot>
				<tr style="background-color:$background_color">
					<td colspan="4" style="width:65%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="left">TOTAL</td>
					<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($total)}</td>
					<td style="width:15%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right"></td>
				</tr>
			</tfoot>
		</table>
		EOD;

		$pdf->writeHTML($tbl, false, false, false, false, '');
		
		$filename = 'Penjualan - ' . format_date($start_date) . '_' . format_date($end_date) . '.pdf';
		$filepath = ROOTPATH . 'public/tmp/pembelian_' . time() . '.pdf.tmp';
		
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
		$this->generatePdf($_GET['start_date'], $_GET['end_date'], $output); 
	}
	
	public function generateExcel($start_date, $end_date, $output) 
	{
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		
		$filepath = $this->model->writeExcel($start_date, $end_date);
		$filename = 'Pembelian Barang - ' . format_date($start_date) . '_' . format_date($end_date) . '.xlsx';
		
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
				header('Content-disposition: attachment; filename="'. $filename .'"');
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
		$this->generateExcel($_GET['start_date'], $_GET['end_date'], $output); 
	}
		
	// Pembelian
	public function getDataDTPembelianPerinvoice() {
		
		$this->hasPermissionPrefix('read');
		
		$num_data = $this->model->countAllDataPembelian();
		$result['draw'] = $start = $this->request->getPost('draw') ?: 1;
		$result['recordsTotal'] = $num_data;
		
		$query = $this->model->getListPembelian();
		$result['recordsFiltered'] = $query['total_filtered'];
				
		helper('html');
		$id_user = $this->session->get('user')['id_user'];
		
		$no = $this->request->getPost('start') + 1 ?: 1;
		foreach ($query['data'] as $key => &$val) 
		{
			
			$val['nama_supplier'] = $val['nama_supplier'] ?: '-';
			$exp = explode(' ', $val['tgl_invoice']);
			$val['tgl_invoice'] = '<div class="text-end">' . format_tanggal($exp[0]) . '</div>';
			$val['total'] = '<div class="text-end">' . format_number($val['total']) . '</div>';
			
			$val['ignore_urut'] = $no;
			$val['ignore_action'] = '<div class="btn-action-group">' . 
				btn_link(['url' => base_url() . '/pembelian/edit?id=' . $val['id_pembelian'],'label' => '', 'icon' => 'fas fa-edit', 'attr' => ['target' => '_blank', 'class' => 'btn btn-success btn-xs me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Edit Data'] ]) . 
				btn_label(['label' => '', 'icon' => 'fas fa-times', 'attr' => ['class' => 'btn btn-danger btn-xs del-penjualan', 'data-id' => $val['id_pembelian'], 'data-delete-message' => 'Hapus data penjualan ?', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Delete Data'] ]) . 
			'</div>';
			
			$attr_btn_email = ['label' => '', 'icon' => 'fas fa-paper-plane', 'attr' => ['data-url' => base_url() . '/penjualan/invoicePdf?email=Y&id=' . $val['id_pembelian'],'data-id' => $val['id_pembelian'],'class' => 'btn btn-primary btn-xs kirim-email'] ];
			if ($val['email']) {
				$attr_btn_email['attr']['data-bs-toggle'] = 'tooltip';
				$attr_btn_email['attr']['data-bs-title'] = 'Kirim Invoice ke Email';
			} else {
				$attr_btn_email['attr']['disabled'] = 'disabled';
				$attr_btn_email['attr']['class'] = $attr_btn_email['attr']['class'] . ' disabled';
			}
			
			$url_nota = base_url() . '/penjualan/printNota?id=' . $val['id_pembelian'];
			$val['ignore_invoice'] = '<div class="btn-action-group">' 
				. btn_link(['url' => $url_nota,'label' => '', 'icon' => 'fas fa-print', 'attr' => ['data-url' => $url_nota, 'class' => 'btn btn-secondary btn-xs print-nota me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Print Nota'] ])
				. btn_link(['url' => base_url() . '/penjualan/invoicePdf?id=' . $val['id_pembelian'],'label' => '', 'icon' => 'fas fa-file-pdf', 'attr' => ['data-filename' => 'Invoice-' . $val['no_invoice'], 'target' => '_blank', 'class' => 'btn btn-danger btn-xs save-pdf me-1', 'data-bs-toggle' => 'tooltip', 'data-bs-title' => 'Download Invoice (PDF)'] ])
				. btn_label( $attr_btn_email ) 
				 . '</div>';
			$no++;
		}
					
		$result['data'] = $query['data'];
		echo json_encode($result); exit();
	}
}
