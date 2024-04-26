<?php
helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Setting Pajak</h5>
	</div>
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		$display = $setting_kasir['item_layout'] == 'list' ? ' style="display:none"' : '';
		?>
		<form method="post" action="" style="max-width: 650px" class="form-horizontal p-3" enctype="multipart/form-data">
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Pengali Kuantitas</label>
					<div class="col-sm-9">
						<?=options(['name' => 'qty_pengali'], ['N' => 'Tidak', 'Y' => 'Ya'], $setting_kasir['qty_pengali'])?>
						<div class="pengali-suffix-container" <?=$setting_kasir['qty_pengali'] == 'Y' ? '' : 'style="display:none"'?>>
							<div class="d-flex align-items-center mt-2">
								<div class="input-group" style="width:130px">
									<span class="input-group-text">Akhiran: </span>
									<input type="text" style="width:50px;" name="qty_pengali_suffix" class="form-control text-end" value="<?=$setting_kasir['qty_pengali_suffix']?>">
								</div>
								<small><em>*) Hanya 1 huruf</em></small>
							</div>
							<small>Pengali digunakan untuk mengalikan kuantitas dengan jumlah tertentu, misal kain kuantitas 1 rol, panjang bisa 25 meter, bisa 25,1 meter, maka 25 dan 25,1 adalah pengali dengan akhiran huruf m (meter). Inputan pengali ada di samping inputan Qty di form penjualan dan form kasir</small>
							
							<div class="input-group">
								<span class="input-group-text">Teks Header</span>
								<input class="form-control" name="qty_pengali_text" value="<?=$setting_kasir['qty_pengali_text']?>"/>
							</div>
							<small>Text akan dimumculkan sebagai judul (tabel header) pengali pada Invoice</small>
						</div>
						
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Bersihkan Form</label>
					<div class="col-sm-9">
						<?=options(['name' => 'bersihkan_form'], 
									['tidak' => 'Tidak'
									, 'setelah_bayar' => 'Setelah Klik Bayar'
									, 'setelah_cetak_nota' => 'Setelah Cetak Nota'
									, 'setelah_cetak_invoice' => 'Setelah Cetak Invoice'
									, 'setelah_klik_close' => 'Setelah Klik Tombol Close Pada Jendela Cetak Invoice'
									]
								, $setting_kasir['bersihkan_form'])?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label">Display Item</label>
					<div class="col-sm-9">
						<?=options(['name' => 'item_layout'], ['grid' => 'Grid', 'list' => 'List'], $setting_kasir['item_layout'])?>
					</div>
				</div>
				<div id="grid-layout" <?=$display?>>
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Max Width</label>
						<div class="col-sm-9">
							<div class="d-flex">
								<input type="range" name="item_layout_grid_width" value="<?=$setting_kasir['item_layout_grid_width']?>" class="form-range me-3" min="100" max="250" id="item-width" oninput="this.nextElementSibling.value = this.value">
								<output><?=$setting_kasir['item_layout_grid_width']?></output>
							</div>
							<small>Lebar maksimal gambar dalam pixel (px)</small>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Max Height</label>
						<div class="col-sm-9">
							<div class="d-flex">
								<input type="range" name="item_layout_grid_height" value="<?=$setting_kasir['item_layout_grid_height']?>" class="form-range me-3" min="100" max="250" id="item-height" oninput="this.nextElementSibling.value = this.value">
								<output><?=$setting_kasir['item_layout_grid_height']?></output>
							</div>
							<small>Tinggi maksimal gambar dalam pixel (px)</small>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Item Perhalaman</label>
						<div class="col-sm-9">
							<input class="form-control" type="number" name="item_layout_grid_length" id="tarif" value="<?=$setting_kasir['item_layout_grid_length']?>" required="required"/>
							<small>Jumlah item yang ditampilkan per halaman</small>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-form-label">Tampilkan Stok</label>
						<div class="col-sm-9">
							<?=options(['name' => 'item_layout_grid_show_stok'], ['Y' => 'Ya', 'N' => 'Tidak'], $setting_kasir['item_layout_grid_show_stok'])?>
						</div>
					</div>
				</div>
				<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			</div>
		</form>
	</div>
</div>