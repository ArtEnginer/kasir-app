<?php
require_once(ROOTPATH . 'app/ThirdParty/TclibBarcode/autoload.php');
?>
<html>
<head>
	<title>Print Nota</title>
	<style>
	@page {
		size:    A4;
		margin: 1em;
	}
	
	p {
		margin: 0;
		padding: 0;
	}
	
	.brand-text {
		font-size: 120%;
	}

	body {
		font-size: 14px;
		font-family: arial, helvetica;
	}
	.container {
		margin: auto;
		max-width: 793px;
		position: relative;
		margin-top: 10px;
		padding-top: 10px;
	}
	.identitas-container {
		display: flex;
		width: 100%;
	}
	.identitas-container .detail {
		margin-left: 10px;
	}
	.detail {
		display: flex;
		flex-direction: column;
		justify-content: center;
	}
	.detail p {
		margin: 0 7px;
	}
	table {
		font-size: 14px;
	}
	
	.barcode-text {
		font-size: 20px;
		text-align: center;
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
	
	.logo-container {
		display: flex;
		justify-content: space-between;
		margin-top:50px;
	}
	.invoice-text {
		text-align: center;
		margin: 30px 0 25px 0;
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
		padding: 8px 12px;
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
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj('C128', $order['order']['no_invoice'], -2, 55, 'black', array(0, 0, 0, 0));

	?>
	<div class="container">
		<div class="badge">
			<?php
			if (empty($order['bayar'])) {
				echo '<div class="badge-text bg-danger" style="color:#FFFFFF">UNPAID</div>';
			} else {
				if ($order['order']['status'] == 'lunas') {
					echo '<div class="badge-text bg-success" style="color:#FFFFFF">LUNAS</div>';
				} else {
					echo '<div class="badge-text bg-danger" style="color:#FFFFFF">KURANG</div>';
				}
			}
			?>
		</div>
		<div class="logo-container">
			<div class="identitas-container">
				<img src="<?=base_url()?>/public/images/<?=$setting['logo']?>"/>
				<div class="detail">
					<p class="brand-text"><?=$identitas['nama']?></p>
					<p><?=$identitas['alamat']?></p>
					<p><?=$identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan']?></p>
					<p><?=$identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi']?></p>
				</div>
			</div>
			<div>
				<?=$bobj->getHtmlDiv()?>
				<div class="barcode-text"><?=$order['order']['no_invoice']?></div>
			</div>
		</div>
		<div class="invoice-text">
			<h1>INVOICE</h1>	
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
				<?=$order['order']['tgl_penjualan']?>
			</div>
		</div>
		
		<table cellspacing="0" cellpadding="0" style="width:100%" class="border padding">
			<thead>
				<tr>
					<th>No</td>
					<th>Deskripsi</td>
					<th><?=$setting_kasir['qty_pengali'] == 'Y' ? 'Qty' : 'Kuantitas'?></td>
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
				$colspan = $setting_kasir['qty_pengali'] == 'Y' ? 6 : 5;
			?>
				<tr>
					<td colspan="<?=$colspan?>">Subtotal</td>
					<td colspan="2" class="text-end"><?=format_number($sub_total)?></td>
				</tr>
				<tr>
					<td colspan="<?=$colspan?>">Diskon</td>
					<td colspan="2" class="text-end"><?=format_number($diskon)?></td>
				</tr>
				<tr>
					<td colspan="<?=$colspan?>">Penyesuaian</td>
					<td colspan="2" class="text-end"><?=format_number($penyesuaian)?></td>
				</tr>
				<?php
				if ($order['order']['pajak_display_text']) {
					echo '<tr>
						<td colspan="' . $colspan . '">' . $order['order']['pajak_display_text'] . '</td>
						<td colspan="2" class="text-end">' . format_number($order['order']['pajak_persen']) . '%</td>
					</tr>';	
					
				}
				?>
				<tr>
					<td colspan="<?=$colspan?>">Total</td>
					<td colspan="2" class="text-end"><?=format_number($total)?></td>
				</tr>
				<tr>
					<td colspan="<?=$colspan?>">Total Bayar</td>
					<td colspan="2" class="text-end"><?=format_number($order['order']['total_bayar'])?></td>
				</tr>
				<tr>
					<td colspan="<?=$colspan?>"><?=$status?></td>
					<td colspan="2" class="text-end"><?=format_number($kurang_bayar)?></td>
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
							<td class="text-end" style="width:20%">' . $jml_bayar . '</td>
						</tr>';
					$no++;
				}
			?>
				<tr>
					<td colspan="2">Total</td>
					<td class="text-end"><?=format_number($order['order']['total_bayar'])?></td>
				</tr>
			</tbody>
		</table>
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