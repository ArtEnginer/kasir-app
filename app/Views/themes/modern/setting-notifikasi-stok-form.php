<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Notifikasi Stok</h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" style="max-width: 750px" class="form-horizontal p-3" enctype="multipart/form-data">
			<div>
				<div class="row mb-3">
					<label class="col-sm-4 col-form-label">Notifikasi Stok</label>
					<div class="col-sm-8">
						<?=options(['name' => 'notifikasi_show'], ['Y' => 'Ya', 'N' => 'Tidak'], @$setting_notifikasi['notifikasi_show'])?>
						<small>Tampilkan notifikasi stok (dashboard bagian pojok kanan atas)</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-4 col-form-label">Dashboard Stok</label>
					<div class="col-sm-8">
						<?=options(['name' => 'dashboard_show'], ['Y' => 'Ya', 'N' => 'Tidak'], @$setting_notifikasi['dashboard_show'])?>
						<small>Tampilkan data stok pada halaman dashboard</small>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
		</form>
	</div>
</div>