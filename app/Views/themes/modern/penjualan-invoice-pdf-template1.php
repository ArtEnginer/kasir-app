<?php
require_once('app/ThirdParty/Tcpdf/tcpdf.php');
require_once('app/Helpers/util_helper.php');

$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);

$pdf->setPageUnit('mm');

// set document information
$pdf->SetCreator($identitas['nama']);
$pdf->SetAuthor($identitas['nama']);
$pdf->SetTitle('Invoice #' .$order['order']['no_invoice']);
$pdf->SetSubject('Invoice Penjualan');

$margin_left = 10; //mm
$margin_right = 10; //mm
$margin_top = 10; //mm
$margin_bottom = 10; //mm
$font_size = 10;

$pdf->SetAutoPageBreak(FALSE, $margin_bottom);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

$pdf->SetProtection(array('modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', $font_size + 4, '', true);
$pdf->SetMargins($margin_left, $margin_top, $margin_right, false);

$pdf->AddPage();

// $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0)));
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

$pdf->SetTextColor(50,50,50);
// $pdf->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 20, 0, 0, 'JPG', 'https://jagowebdev.com');
$pdf->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 20, 0, 0, 'JPG', '');

$image_dim = getimagesize(ROOTPATH . 'public/images/' . $setting['logo']);
$x = $margin_left + ($image_dim[0] * 0.2645833333) + 5;
$pdf->SetXY($x, $margin_top + 3);
$pdf->Cell(0, 9, $identitas['nama'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
$pdf->SetX($x);
$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
$pdf->Cell(0, 0, $identitas['alamat'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
$pdf->SetX($x);
$pdf->Cell(0, 0, $identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
$pdf->SetX($x);
$pdf->Cell(0, 0, $identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi'] , 0, 1, 'L', 0, '', 0, false, 'T', 'M' );

$barcode_style = array(
	'position' => 'R',
	'align' => 'C',
	'stretch' => false,
	'fitwidth' => true,
	'cellfitalign' => '',
	'border' => false,
	'hpadding' => 'auto',
	'vpadding' => 'auto',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255),
	'text' => true,
	'font' => 'helvetica',
	'fontsize' => $font_size,
	'stretchtext' => false
);

$pdf->SetY($margin_top + 10);
$pdf->write1DBarcode($order['order']['no_invoice'], 'C128', '', '', '', 20, 0.4, $barcode_style, 'N');

$pdf->ln(8);
$pdf->SetFont ('helvetica', 'B', $font_size + 10, '', 'default', true );
$pdf->Cell(0, 0, 'INVOICE', 0, 1, 'C', 0, '', 0, false, 'T', 'M' );

$pdf->ln(8);
$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
$pdf->Cell(0, 0, 'Pembeli ', 0, 1);
$pdf->ln(4);

$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );

$y =  $pdf->GetY();
$pdf->Cell(10, 0, 'Nama', 0, 1);
$pdf->SetXY($margin_left + 13, $y);
$pdf->Cell(10, 0, ':', 0, 1);
$pdf->SetXY($margin_left + 15, $y);
$pdf->Cell(10, 0, $order['customer']['nama_customer'], 0, 1);

$y =  $pdf->GetY();
$pdf->Cell(10, 0, 'Alamat', 0, 1);
$pdf->SetXY($margin_left + 13, $y);
$pdf->Cell(0, 0, ':', 0, 1);
$pdf->SetXY($margin_left + 15, $y);
$pdf->Cell(0, 0, $order['customer']['alamat_customer'], 0, 1);

if (!empty($order['customer']['nama_kecamatan'])) {
	$pdf->SetX($margin_left + 15);
	$pdf->Cell(0, 0, 'Kec. ' . $order['customer']['nama_kecamatan'] . ', Kab. ' . $order['customer']['nama_kabupaten'], 0, 1);
	$pdf->SetX($margin_left + 15);
	$pdf->Cell(0, 0, $order['customer']['nama_propinsi'], 0, 1);
}

$pdf->ln(5);
$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
$y =  $pdf->GetY();
$pdf->Cell(0, 0, 'Transaksi' , 0, 1);
$pdf->SetFont ('helvetica', '', $font_size, '', '', true );
$pdf->SetY($y);

$pdf->Cell(0, 0, format_date($order['order']['tgl_penjualan']), 0, 1, 'R', 0, '', 0, false, 'T', 'M' );

$pdf->ln(5);
$pdf->SetFont ('helvetica', '', $font_size, '', 'default', true );
$border_color = '#CECECE';
$background_color = '#efeff0';

$kolom_qty_pengali = $setting_kasir['qty_pengali'] == 'Y' ? '<th style="width:9%;border-top-color:' . $border_color . ';border-bottom-color:' . $border_color . ';border-right-color:' . $border_color . '" align="center">' . $setting_kasir['qty_pengali_text'] . '</th>' : '';
$lebar_barang = $setting_kasir['qty_pengali'] == 'Y' ? '33%' : '35%';
$lebar_qty = $setting_kasir['qty_pengali'] == 'Y' ? '7%' : '10%';
$lebar_total = $setting_kasir['qty_pengali'] == 'Y' ? '13%' : '15%';
$qty_text = $setting_kasir['qty_pengali'] == 'Y' ? 'Qty' : 'Kuantitas';
$tbl = <<<EOD
<table border="0" cellspacing="0" cellpadding="6">
	<thead>
		<tr border="1" style="background-color:$background_color">
			<th style="width:5%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">No</th>
			<th style="width:$lebar_barang;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">Deskripsi</th>
			<th style="width:$lebar_qty;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">$qty_text</th>
			$kolom_qty_pengali
			<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Satuan</th>
			<th style="width:$lebar_total;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Harga Total</th>
			<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Diskon</th>
			<th style="width:$lebar_total;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="center">Total</th>
		</tr>
	</thead>
	<tbody>
EOD;

	$no = 1;
	$format_number = 'format_number';
	foreach ($order['detail'] as $val) {
		$kolom_qty_pengali = $setting_kasir['qty_pengali'] == 'Y' ? '<th style="width:9%;border-left-color:' . $border_color . ';border-bottom-color:' . $border_color . ';border-right-color:' . $border_color . '" align="right">' .  $format_number($val['qty_pengali'], true) . $setting_kasir['qty_pengali_suffix'] . '</th>' : '';
		$tbl .= <<<EOD
			<tr>
				<td style="width:5%;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color" align="center">$no</td>
				<td style="width:$lebar_barang;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$val[nama_barang]</td>
				<th style="width:$lebar_qty;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['qty'], true)}</th>
				$kolom_qty_pengali
				<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_satuan'])}</th>
				<th style="width:$lebar_total;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_total'])}</th>
				<th style="width:10%;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['diskon'])}</th>
				<th style="width:$lebar_total;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($val['harga_neto'])}</th>
			</tr>
EOD;
	$no++;
	}

$diskon = 0;
if ($order['order']['diskon_nilai']) {
	if ($order['order']['diskon_jenis'] == '%') {
		$diskon = $order['order']['diskon_nilai'] . '%';
	} else {
		$diskon = format_number($order['order']['diskon_nilai']);
	}
}

$total = format_number($order['order']['neto']);
$penyesuaian = format_number($order['order']['penyesuaian']);
$sub_total = format_number($order['order']['sub_total']);
if ($order['order']['status'] == 'lunas') {
	$status = 'Kembali';
	$kurang_bayar = $order['order']['kembali'];
} else {
	$status = 'Kurang';
	$kurang_bayar = $order['order']['kurang_bayar'];
}

$colspan = $setting_kasir['qty_pengali'] == 'Y' ? '7' : '6';
$text_width = $setting_kasir['qty_pengali'] == 'Y' ? '77%' : '75%';
$value_width = $setting_kasir['qty_pengali'] == 'Y' ? '23%' : '25%';
$tbl .= <<<EOD
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Subtotal</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$sub_total</td>
		</tr>
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Diskon</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$diskon</td>
		</tr>
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Penyesuaian</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$penyesuaian</td>
		</tr>
EOD;

if ($order['order']['pajak_display_text']) {

	$tbl .= <<<EOD
			<tr style="background-color:$background_color">
				<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">{$order['order']['pajak_display_text']}</td>
				<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['pajak_persen'])}%</td>
			</tr>
	EOD;
}

$tbl .= <<<EOD
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$total</td>
		</tr>
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">Total Bayar</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['total_bayar'])}</td>
		</tr>
		<tr style="background-color:$background_color">
			<td colspan="$colspan" style="width:$text_width;border-bottom-color:$border_color;border-right-color:$border_color;border-left-color:$border_color">$status</td>
			<td style="width:$value_width;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($kurang_bayar)}</td>
		</tr>
	</tbody>
</table>
EOD;

$pdf->writeHTML($tbl, false, false, false, false, '');
$pdf->ln(5);

$pdf->SetFont ('helvetica', 'B', $font_size, '', '', true );
$pdf->Cell(0, 0, 'Pembayaran' , 0, 1);

$pdf->ln(5);
$pdf->SetFont ('helvetica', '', $font_size, '', '', true );

if (empty($order['bayar'])) {
	$pdf->Cell(0, 0, 'Tidak ada pembayaran', 0, 1, 'L');
} else {
	$tbl = <<<EOD
	<table border="0" cellspacing="0" cellpadding="6">
		<thead>
			<tr border="1" style="background-color:$background_color">
				<th style="width:$text_width;border-left-color:$border_color;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right" align="center">Tanggal Pembayaran</th>
				<th style="width:$value_width;border-top-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right" align="center">Nominal</th>
			</tr>
		</thead>
		<tbody>
EOD;

	foreach ($order['bayar'] as $val) {
		
		$tgl_bayar = format_date($val['tgl_bayar']);
		$jml_bayar = format_number($val['jml_bayar']);
		$tbl .= <<<EOD
		<tr>
			<td style="width:$text_width;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">$tgl_bayar</td>
			<td style="width:$value_width;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">$jml_bayar</td>
		</tr>
EOD;
	}

	$tbl .= <<<EOD
		<tr>
			<td style="width:$text_width;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color">TOTAL</td>
			<td style="width:$value_width;border-left-color:$border_color;border-bottom-color:$border_color;border-right-color:$border_color" align="right">{$format_number($order['order']['total_bayar'])}</td>
		</tr>
	</tbody>
	</table>
EOD;

	$pdf->writeHTML($tbl, false, false, false, false, '');
}

$pdf->SetY(-20);
// $pdf->writeHTML('<hr style="background-color:#FFFFFF; border-bottom-color:#CCCCCC;height:0"/>', false, false, false, false, '');
$pdf->writeHTML('<div style="background-color:#FFFFFF; border-bottom-color:#ababab;height:0"></div>', false, false, false, false, '');

$pdf->ln(2);

$pdf->SetFont ('helvetica', 'I', $font_size, '', '', true );
$pdf->SetTextColor(50,50,50);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0, 0, $setting['footer_text'], 0, 1, 'L');

$filename = 'Invoice-' . str_replace(['/', '\\'], '_', $order['order']['no_invoice']) . '.pdf';
$filepath_invoice = ROOTPATH . 'public/tmp/' . $filename;

if (!empty($_GET['email'])) 
{	
	$filepath = ROOTPATH . 'public/tmp/invoice_'. time() . '.pdf';
	$pdf->Output($filepath, 'F');
	
	if (@$_GET['email']) {
		$email = $_GET['email'];
	} else {
		$email = $order['customer']['email'];
	}

	$email_config = new \Config\EmailConfig;
	$email_data = array('from_email' => $email_config->from
					, 'from_title' => $email_config->fromTitle
					, 'to_email' => $email
					, 'to_name' => $order['customer']['nama_customer']
					, 'email_subject' => 'Invoice: ' . $order['order']['no_invoice']
					, 'email_content' => '<h2>Hi, ' . $order['customer']['nama_customer'] . '</h2><p>Berikut terlampir invoice pembelian atas nama ' . $order['customer']['nama_customer'] . '.</p><p>Anda dapat mengunduhnya pada bagian Attachment.<br/><br/><p>Salam</p>'
					, 'attachment' => ['path' => $filepath, 'name' => $filename]
	);
	
	require_once('app/Libraries/SendEmail.php');
	
	$emaillib = new \App\Libraries\SendEmail;
	$emaillib->init();
	$send_email =  $emaillib->send($email_data);

	unlink($filepath);
	if ($send_email['status'] == 'ok') {
		$message['status'] = 'ok';
		$message['message'] = 'Invoice berhasil dikirim ke alamat email: ' . $email;
	} else {
		$message['status'] = 'error';
		$message['message'] = 'Invoice gagal dikirim ke alamat email: ' . $email . '<br/>Error: ' . $send_email['message'];
	}
	
	echo json_encode($message);
	exit();
}

if (@$_GET['ajax'] == 'true') {
	$pdf->Output($filepath_invoice, 'F');
	$content = file_get_contents($filepath_invoice);
	echo $content;
	delete_file($filepath_invoice);
} else {
	$pdf->Output($filename, 'D');
}
exit;