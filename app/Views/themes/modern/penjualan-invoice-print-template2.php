<?php
require_once(ROOTPATH . 'app/ThirdParty/TclibBarcode/autoload.php');
?>
<html>
<head>
	<title>Print Invoice</title>
	<style>
	@page {
		size: <?=$setting_printer['paper_width']?>mm <?=$setting_printer['paper_height']?>mm;
		margin-top: <?=$setting_printer['margin_top']?>mm;;
		margin-right: <?=$setting_printer['margin_right']?>mm;
		margin-bottom: <?=$setting_printer['margin_bottom']?>mm;
		margin-left: <?=$setting_printer['margin_left']?>mm;
	}
	
	body {
		font-size: 13px;
		font-family: arial, helvetica;
	}
	
	table td {
		font-size: 13px;
		font-family: arial, helvetica;
	}
	
	img {
		float: left;
	
	}
	
	p {
		margin: 0;
		padding: 0;
	}
		
	.container {
		margin: auto;
		max-width: 793px;
		position: relative;
		margin-top: 10px;
		padding-top: 10px;
	}
	
	.header-container {
		display: flex;
		justify-content: space-between;
	}
	.invoice-text, .no-invoice {
		margin: 0;
		padding: 0;
	}
	.invoice-detail-container{
		margin: 0;
		padding: 0;
		text-align:right;
	}
	
	.identitas-container {

	}
	.brand-text {
		font-size: 130%;
	}
	.identitas-container .detail {
		margin-left: 10px;
	}
	.detail {
		display: flex;
		flex-direction: column;
		white-space: nowrap;
	}
	.detail p {
		margin: 0 7px;
	}
	
	table {
		font-size: 14px;
	}
	
	header {
		text-align: center;
		margin: auto;
	}
	footer {
		width: 100%;
		text-align: center;
	}
	hr {
		margin: 10px 0;
		padding: 0;
		height: 1px;
		border: 0;
		border-bottom: 1px solid rgb(49,49,49);
		width: 100%;
		
	}
	.nama-item {
		font-weight: bold;
	}
	
	.harga-item {
		display: flex;
		justify-content: flex-end;
		margin: 0;
		padding: 0;
	}
	
	table {
		border-collapse: collapse;
	}
	.no-border td {
		border: 0;
	}
	
	.text-right {
		text-align: right;
	}
	
	.nama-perusahaan {
		font-weight: bold;
		font-size: 120%;
		margin-bottom: 3px;
	}
	
	.text-bold {
		font-weight: bold;
	}
	
	td {
		vertical-align: top;
	}
	
	.border th,
	.border td {
		border: 1px solid #CCCCCC;
	}
	.padding th,
	.padding td {
		padding: 3px 10px;
	}
	.d-flex-between {
		display: flex;
		justify-content: space-between;
	}
	.text-end {
		text-align: right;
	}
	.badge {
		display: flex;
		justify-content: flex-end;
		margin-bottom: 25px;
		margin-right: -30px;
		 display: flex;
    /* justify-content: flex-end; */
		margin-bottom: 0;
		margin-right: 0;
		/* margin-top: 500px; */
		position: absolute;
		right: 0;
		top:0;
	}
	.badge-text {
		padding: 10px 20px;
		
	}
	.bg-danger {
		background: red;
	}
	.bg-success {
		background: #45e445;
		color: #0e861e;
	}
	</style>
	
</head>
<body onload="window.print()">
	<?php
		$pelanggan = $penjualan['nama_customer'] ? $penjualan['nama_customer'] : 'Umum';
		
		$termin = $jatuh_tempo = '-';
		if ($order['order']['jenis_bayar'] == 'tempo') {
			$termin = $setting_piutang['piutang_periode'] . ' Hari';
			$day = $termin > 1 ? 'days' : 'day';
			$jatuh_tempo = date('Y-m-d', strtotime('+' . $setting_piutang['piutang_periode'] . $day, strtotime($order['order']['tgl_penjualan'])));
			$jatuh_tempo = format_date($jatuh_tempo);
		}

	?>
	<div class="container">
		<div class="header-container">
			<div class="identitas-container">
				<img src="<?=base_url()?>/public/images/<?=$setting['logo']?>"/>
				<div class="detail">
					<p class="brand-text"><?=$identitas['nama']?></p>
					<p><?=$identitas['alamat']?></p>
					<p><?=$identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan']?></p>
					<p><?=$identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi']?></p>
					<p><?='Telepon: ' . $identitas['no_telp']?></p>
					<p><?='Email: ' . $identitas['email']?></p>
				</div>
			</div>
			<div class="invoice-detail-container">
				<h2 class="invoice-text">INVOICE</h2>	
				<h3 class="no-invoice"><?=$order['order']['no_invoice']?></h3>
				<p>Status Bayar: <?=$order['order']['status'] == 'lunas' ? 'LUNAS' : 'KURANG BAYAR'?></p>
				<p>Termin: <?=$termin?></p>
				<p>Jatuh Tempo: <?=$jatuh_tempo?></p>
			</div>
		</div>
		
		<div>
			<h3>Pembeli</h3>
			<table class="no-border" cellspacing="0" cellpadding="0">
				<tr>
					<td>Nama</td>
					<td style="padding:0 5px">:</td>
					<td><?=$order['customer']['nama_customer']?></td>
				</tr>
				<tr>
					<td>Alamat</td>
					<td style="padding:0 5px">:</td>
					<td>
						<p><?=$order['customer']['alamat_customer']?></p>
						<?php
						if (!empty($order['customer']['nama_kecamatan'])) {
							echo '<p>' . $order['customer']['nama_kecamatan'] . ', Kab. ' . $order['customer']['nama_kabupaten'] . '</p>
								<p>' . $order['customer']['nama_propinsi'] . '</p>';
						}
						?>
					</td>
				</tr>
			</table>
		</div>
		<div class="d-flex-between">
			<h3>Transaksi</h3>
			<div>
				<?php
				$split = explode(' ', $order['order']['tgl_penjualan']);
				echo format_date($split[0]) . ' ' . $split[1];
				?>
			</div>
		</div>
		
		<table cellspacing="0" cellpadding="0" style="width:100%" class="border padding">
			<thead>
				<tr>
					<th>No</td>
					<th>Deskripsi</td>
					<th><?=$setting_kasir['qty_pengali'] == 'Y' ? 'Qty' : 'Qty'?></td>
					<?php
						if ($setting_kasir['qty_pengali'] == 'Y') {
							echo '<th>' . $setting_kasir['qty_pengali_text'] . '</td>';
						}
					?>
					<th>Harga Satuan</td>
					<th>Harga Total</td>
					<th>Diskon</td>
					<th>Total</td>
				</tr>
			</thead>
			<tbody>
			<?php
				$no = 1;
				foreach ($order['detail'] as $val) {
					echo '<tr>
							<td>' . $no . '</td>
							<td>' . $val['nama_barang'] . '</td>
							<td class="text-end">' . format_number($val['qty'], true) . '</td>';
					if ($setting_kasir['qty_pengali'] == 'Y') {
						echo '<td class="text-end">' . format_number($val['qty_pengali'], true) . $setting_kasir['qty_pengali_suffix'] . '</td>';
					}
							
					echo '<td class="text-end">' . format_number($val['harga_satuan']) . '</td>
							<td class="text-end">' . format_number($val['harga_total']) . '</td>
							<td class="text-end">' . format_number($val['diskon']) . '</td>
							<td class="text-end">' . format_number($val['harga_neto']) . '</td>
						</tr>';
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
				
				$total = $order['order']['neto'];
				$total_terbilang = $order['order']['neto'];
				$penyesuaian = format_number($order['order']['penyesuaian']);
				$sub_total = format_number($order['order']['sub_total']);
				if ($order['order']['status'] == 'lunas') {
					$status = 'Kembali';
					$kurang_bayar = $order['order']['kembali'];
				} else {
					$status = 'Kurang';
					$kurang_bayar = $order['order']['kurang_bayar'];
				}
				$colspan = $setting_kasir['qty_pengali'] == 'Y' ? 5 : 4;
				$rowspan = $order['order']['pajak_display_text'] ? 7 : 6;
			?>
				<tr>
					<td style="border-left:0;border-bottom:0;vertical-align:bottom;padding-bottom:0;padding-left:0" rowspan="<?=$rowspan?>" colspan="<?=$colspan?>">
						<div style="border: 1px solid #CCCCCC;padding:10px">
							Terbilang <br/><br/><strong>"<?=terbilang($total)?> rupiah"</strong>
						</div>
					</td>
					<td>Subtotal</td>
					<td colspan="2" class="text-end"><?=format_number($sub_total)?></td>
				</tr>
				<tr>
					<td>Diskon</td>
					<td colspan="2" class="text-end"><?=format_number($diskon)?></td>
				</tr>
				<tr>
					<td>Penyesuaian</td>
					<td colspan="2" class="text-end"><?=format_number($penyesuaian)?></td>
				</tr>
				<?php
				if ($order['order']['pajak_display_text']) {
					echo '<tr>
						<td>' . $order['order']['pajak_display_text'] . '</td>
						<td colspan="2" class="text-end">' . format_number($order['order']['pajak_persen']) . '%</td>
					</tr>';	
					
				}
				?>
				<tr>
					<td>Total</td>
					<td colspan="2" class="text-end"><?=format_number($total)?></td>
				</tr>
			</tbody>
		</table>
		<div class="d-flex-between">
			<h3>Pembayaran</h3>
		</div>
		<table cellspacing="0" cellpadding="0" style="width:100%" class="border padding">
			<thead>
				<tr>
					<th>No</td>
					<th>Tanggal Pembayaran</td>
					<th>Metode Pembayaran</td>
					<th>Nominal</td>
				</tr>
			</thead>
			<tbody>
			<?php
				$no = 1;
				foreach ($order['bayar'] as $val) {
					$tgl_bayar = format_date($val['tgl_bayar']);
					$jml_bayar = format_number($val['jml_bayar']);
					echo '<tr>
							<td>' . $no . '</td>
							<td style="width:80%">' . $tgl_bayar . '</td>
							<td style="width:80%">' . strtoupper($val['metode_bayar']) . '</td>
							<td class="text-end" style="width:20%">' . $jml_bayar . '</td>
						</tr>';
					$no++;
				}
			?>
				<tr>
					<td colspan="3" class="text-end" style="border-left:0;border-bottom:0">Total Bayar</td>
					<td class="text-end"><?=format_number($order['order']['total_bayar'])?></td>
				</tr>
				<tr>
					<td colspan="3" class="text-end" style="border-left:0;border-bottom:0">Kurang Bayar / Kembali</td>
					<td class="text-end"><?=format_number($kurang_bayar)?></td>
				</tr>
			</tbody>
		</table>
		<table style="width:100%;text-align:center;margin-top:20px">
			<tbody>
				<tr>
					<td>Customer,</td>
					<td>Admin Penjualan / Gudang,</td>
				</tr>
				<tr>
					<td><div style="margin-top:55px"><?=$order['customer']['nama_customer']?></div></td>
					<td><div style="margin-top:55px"><?=empty($user_input['nama']) ? '-' : $user_input['nama']?></div></td>
				</tr>
			</tbody>
		</table>
		<div style="margin-top:20px; padding: 15px; border: 1px solid #CCCCCC;">
			<div>Catatan</div>
			<div style="margin-top:55px">Pembayaran Via Transfer a/n <strong>Nama Lengkap</strong> - Nama Bank: Nomor Rekening</div>
		</div>
	</div>
	<footer style="position:fixed; bottom:0; border-top:1px solid #CCCCCC;padding-top:10px;">
		<?=$setting['footer_text']?>
	</footer>
</body>
<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', () => {
		setTimeout(function() {
			window.close();
		}, 7000);
		
	});
</script>
</html>