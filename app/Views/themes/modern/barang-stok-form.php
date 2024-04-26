<?php

?>
<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Kartu Stok Barang</h5>
	</div>
	<div class="card-body">
		<form method="get" action="" class="form-horizontal form-laporan p-3 pb-0" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Tanggal</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" name="daterange" id="daterange" value="<?=$start_date?> s.d. <?=$end_date?>" />
					<input type="hidden" value="<?=$start_date_db?>" id="start-date"/>
					<input type="hidden" value="<?=$end_date_db?>" id="end-date"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Nama Barang</label>
				<div class="col-sm-5">
					<?php
					if ($list_barang) {
						echo options(['name' => 'id_barang', 'class' => 'select2'], $list_barang, @$_GET['id_barang']);
					} else {
						echo 'Data tidak ditemukan';
					}						
					?>
				</div>
			</div>
			<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
		</form>
		<p>
		<?php if (!empty($_GET['daterange'])) {
			if ($stok['riwayat_stok']) {
				// echo '<pre>'; print_r($riwayat_stok); die;
				echo '
				<div class="d-flex mb-3" style="justify-content:flex-end">
					<div class="btn-group">
						<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf"><i class="fas fa-file-pdf me-2"></i>PDF</button>
						<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel"><i class="fas fa-file-excel me-2"></i>XLSX</button>
					</div>
				</div>
				
				<table class="table table-striped table-border">
					<thead>
						<tr>
							<th>No</th>
							<th>No Nota</th>
							<th>Nama</th>
							<th>Tgl. Transaksi</th>
							<th>Keterangan</th>
							<th>Qty Masuk</th>
							<th>Qty Keluar</th>
							<th>Saldo</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td colspan="7">Saldo awal</td>
						<td>' . format_number($stok['saldo_awal'], true) . '</td>
					</tr>';
				$no = 1;
				$saldo = $stok['saldo_awal'];
				foreach ($stok['riwayat_stok'] as $val) {
					$saldo +=  $val['qty_masuk'];
					$saldo -=  $val['qty_keluar'];
					echo '<tr>
							<td>' . $no . '</td>
							<td>' . $val['no_invoice']. '</td>
							<td>' . $val['nama']. '</td>
							<td>' . format_date($val['tgl_transaksi']). '</td>
							<td>' . $val['keterangan']. '</td>
							<td>' . format_number($val['qty_masuk'], true). '</td>
							<td>' . format_number($val['qty_keluar'], true). '</td>
							<td>' . $saldo . '</td>
						</tr>';
					$no++;
				}
				echo '</tbody>
				</table>';
			} else {
				show_message(['status' => 'error', 'message' => 'Data tidak ditemukan']);
			}
		}
		?>
	</div>
</div>