<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->extend('themes/modern/layout-mobile');
	echo $this->section('content');
}
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php
			helper (['html', 'format']);
			// echo '<pre>'; print_r($form_data); die;
			
			if (empty($_GET['mobile'])) {
				echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
					'url' => $config->baseURL . 'barang/add',
					'icon' => 'fa fa-plus',
					'label' => 'Tambah Data'
				]);
				
				echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
					'url' => $config->baseURL . 'barang',
					'icon' => 'fa fa-arrow-circle-left',
					'label' => 'Data Barang'
				]);
				
				echo '<hr/>';
			}
		?>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		foreach ($satuan as $val) {
			$satuan_option[$val['id_satuan_unit']] = $val['nama_satuan'];
		}
		// echo '<pre>'; print_r($satuan); die;

		?>
		
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="bg-lightgrey p-3 ps-4 mb-4">
				<h5 class="mb-0">Barang</h5>
			</div>
			<div class="ps-3">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Barang</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama_barang" value="<?=set_value('nama_barang', @$form_data['nama_barang'])?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kode Barang</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="kode_barang" value="<?=set_value('kode_barang', @$form_data['kode_barang'])?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Barcode</label>
					<div class="col-sm-5" style="position:relative">
						<div class="input-group">
							<input class="form-control barcode" type="text" name="barcode" value="<?=set_value('barcode', @$form_data['barcode'])?>"/>
							<button class="btn btn-secondary generate-barcode" type="button">Generate</button>
							
						</div>
						<div class="spinner-border spinner text-secondary spinner-border-sm" style="display:none; position:absolute; top:10px; right:110px"></div>
						<small class="text-muted"><span class="jml-digit">0</span> digit | 13 digit, Misal 8993053131130. 899 adalah kode negara Indonesia</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="deskripsi" required="required"/><?=set_value('deskripsi', @$form_data['deskripsi'])?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kategori</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_barang_kategori', 'id' => 'list-kategori', 'style' => 'width:100%'], $list_kategori, set_value('id_barang_kategori', @$form_data['id_barang_kategori']))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Satuan</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_satuan_unit'], $satuan_option, set_value('id_satuan_unit', @$form_data['id_satuan_unit']))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Berat</label>
					<div class="col-sm-5">
						<div class="input-group mb-3">
							<input type="text" class="form-control number" name="berat" value="<?=set_value('berat', format_ribuan(str_replace('.', '', @$form_data['berat'])))?>" required="required">
							<span class="input-group-text" id="basic-addon2">Gram</span>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Daluarsa</label>
					<div class="col-sm-5">
						<?php 
							if (!empty($form_data['tgl_daluarsa']) && $form_data['tgl_daluarsa'] != '0000-00-00') {
								list($y, $m, $d) = explode('-', $form_data['tgl_daluarsa']);
								$tgl_daluarsa = $d . '-' . $m . '-' . $y;
							} else {
								$tgl_daluarsa = set_value('tgl_daluarsa', '');
							}
						?>
						<input type="text" class="form-control flatpickr" name="tgl_daluarsa" value="<?=$tgl_daluarsa?>" required="required">
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Image</label>
					<div class="col-sm-5">
						<div class="gallery-container" style="margin-top:0">
							<?php
														
							$initial_item = false;
							if (empty($form_data['images'])) {
								$initial_item = true;
								$form_data['images'][] = ['id_barang' => '', 'id_file_picker' => '', 'nama_file' => ''];
							}

							$display = $initial_item ? ' style="display:none"' : '';
							echo '<ul id="list-image-container" class="list-image-container">';
							foreach ($form_data['images'] as $val) 
							{
								$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
								?>
								<li class="thumbnail-item"<?=$data_initial_item?> id="barang-<?=$val['id_barang']?>"<?=$display?> data-id-file="<?=$val['id_file_picker']?>">
									<div class="toolbox">
										<?php if (@$id_kategori != '') { ?>
											<div class="grip"><i class="fas fa-grip-horizontal"></i></div>
										<?php } ?>
										<ul class="right-menu">
											<li><a class="grip" data-bs-toggle="tooltip" data-bs-placement="top" title="Move" href="javascript:void(0)"><i class="fas fa-grip-horizontal"></i></a>
											<li><a class="text-danger delete-image" href="javascript:void(0)"><i class="fas fa-times"></i></a>
										</ul>
									</div>
									<div class="img-container">
										<?php
										$src = '';
										if ($val['nama_file']) {
											$src = base_url() . '/public/files/uploads/' . $val['nama_file'];
										}
										?>
										<img class="jwd-img-thumbnail" src="<?=$src?>" />
									</div>
									<input type="hidden" name="id_file_picker[]" value="<?=$val['id_file_picker']?>"/>
								</li>	
							<?php 
							} 
							echo '</ul>';
							?>
							<a class="btn btn-secondary btn-xs" id="add-image" href="javascript:void(0)">Add Image</a>
						</div>
					</div>
				</div>

			</div>
			<div class="bg-lightgrey p-3 ps-4 mb-4">
				<h5 class="mb-0">Stok</h5>
			</div>
			<div class="ps-3">
				<?php

				foreach ($gudang as $index => $val) {
					
					$stok_awal = 0;
					// echo '<pre>'; print_r($stok); die;
					if (isset($_POST['stok_awal'][$index])) {
						$stok_awal = $_POST['stok_awal'][$index];
					} else {
						if (key_exists($val['id_gudang'], $stok)) {
							$stok_awal = $stok[$val['id_gudang']]['total_stok'];
						}
					}
					
					$value = 0;
					$stok_adjusment = 0;
					$stok_akhir = $stok_awal;
					if (!empty($_POST['adjusment'][$index])) {
						$value = $_POST['adjusment'][$index];
						$operator = ['plus' => '+', 'minus' => '-'];
						$stok_adjusment = $operator[$_POST['operator'][$index]] . $_POST['adjusment'][$index];
						
						if ($_POST['operator'][$index] == 'plus') {
							
							$stok_akhir = (int) $stok_awal + str_replace('.','',$value);
						} else {
							$stok_akhir = $stok_awal - str_replace('.','',$value);
						}
					}
					
				?>
					<div class="stok-container">
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Gudang</label>
							<div class="col-sm-5 stok"><?=$val['nama_gudang']?></div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Adjusment</label>
							<div class="col-sm-5 stok-number">
								<div class="form-inline">
									<?=options(['name' => 'operator[]', 'class' => 'operator me-2'], ['plus' => '+', 'minus' => '-'], set_value('operator['. $index . ']','plus'))?>
									<div class="input-group"  style="width:130px">
									  <button type="button" class="input-group-text decrement">-</button>
									  <input type="text" size="2" name="adjusment[]" value="<?=$value?>" class="form-control text-end stok">
									  <button type="button" class="input-group-text increment">+</button>
									</div>
								</div>
								<div class="text-muted adjusment fst-italic">
									Stok Awal: <span class="stok-awal"><?=format_ribuan($stok_awal)?></span>, Adjusment: <span class="stok-adjusment"><?=$stok_adjusment?></span>, Stok Akhir: <span class="stok-akhir"><?=format_ribuan($stok_akhir)?></span>
								</div>
								<input type="hidden" name="id_gudang[]" value="<?=$val['id_gudang']?>"/>
								<input type="hidden" name="stok_awal[]" value="<?=$stok_awal?>"/>
							</div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Adjusment</label>
							<?php 
							// echo '<pre>'; print_r($_POST); die;
								$tgl_adjusment = '';
								if (!empty($form_data['tgl_adjusment_stok'][$index]) && $form_data['tgl_adjusment_stok'][$index] != '0000-00-00') {
									list($y, $m, $d) = explode('-', $form_data['tgl_adjusment_stok'][$index]);
									$tgl_adjusment = $d . '-' . $m . '-' . $y;
								} else {
									if (!empty($_POST['tgl_adjusment_stok'][$index])) {
										$tgl_adjusment = $_POST['tgl_adjusment_stok'][$index];
									}
								}
							?>
							<div class="col-sm-5 form-inline"><input type="text" name="tgl_adjusment_stok[]" class="form-control flatpickr" value="<?=$tgl_adjusment?>"/></div>
						</div>
					</div>
				<?php
					if ( ($index + 1) < count($stok)) {
						echo '<hr/>';
					}
				}
				?>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Stok Alert</label>
					<div class="col-sm-5 stok">
							<input class="form-control" name="stok_minimum" style="width:auto" value="<?=@$form_data['stok_minimum']?>"/>
							<small>Notifikasi akan ditampilkan jika stok barang sama/dibawah stok alert (stok minimum)</small>
					</div>
					
				</div>
				
			</div>
			
			<div class="bg-lightgrey p-3 ps-4 mb-4">
				<h5 class="mb-0">Harga Pokok</h5>
			</div>
			<div class="row mb-3 ps-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Harga pokok</label>
				<div class="col-sm-5">
					<?php
					$adjusment_harga_pokok = 0;
					$harga_pokok_awal = $harga_pokok;
					
					if (!$harga_pokok_awal) {
						$harga_pokok_awal = 0;	
					}
					if (!empty($_POST['harga_pokok'])) {
						$harga_pokok_awal = $_POST['harga_pokok_awal'];
						
						
						$adjusment_harga_pokok = str_replace('.', '', $_POST['harga_pokok']) - str_replace('.','', $harga_pokok_awal);
					}
					
					?>
					<input name="harga_pokok" style="width:auto" class="form-control number harga-pokok" value="<?=set_value('harga_pokok', format_ribuan($harga_pokok))?>" />
					<div class="text-muted adjusment fst-italic">
						Harga awal: <span class="harga-pokok-awal"><?=empty($id) ? '0' : format_ribuan($harga_pokok_awal)?></span>, Adjusment: <span class="adjusment-harga-pokok"><?=format_ribuan($adjusment_harga_pokok)?></span>
					</div>
					<input type="hidden" name="adjusment_harga_pokok" value="<?=$harga_pokok?>"/>
					<input type="hidden" name="harga_pokok_awal" value="<?=$harga_pokok_awal?>"/>
				</div>
			</div>
			
			<div class="bg-lightgrey p-3 ps-4 mb-4">
				<h5>Harga Jual</h5>
			</div>
			<div class="ps-3">
				<?php
			
				foreach ($harga_jual as $index => $val) {
					$harga_jual_awal = $val['harga'];
					$adjusment_harga_jual = 0;
					
					
					if (isset($_POST['harga_jual'][$index])) {
						$harga_jual_awal = $_POST['harga_jual_awal'][$index];
						if (!$harga_jual_awal) {
							$harga_jual_awal = 0;
						}

						$adjusment_harga_jual = str_replace('.', '', $_POST['harga_jual'][$index]) - $harga_jual_awal;
					}
				?>
					<div class="stok-container">
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Harga</label>
							<div class="col-sm-5 stok"><?=$val['nama_jenis_harga']?></div>
						</div>
						<div class="row mb-3">
							<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Harga Jual</label>
							<div class="col-sm-5 stok-number">
								<div class="input-group"  style="width:170px">
								  <button type="button" class="input-group-text decrement">-</button>
								  <input type="text" size="2" name="harga_jual[]" value="<?=!empty($_POST['harga_jual'][$index]) ? $_POST['harga_jual'][$index] : format_ribuan(str_replace('.','',$val['harga']))?>" class="form-control text-end number harga-jual">
								  <button type="button" class="input-group-text increment">+</button>
								</div>
								<div class="text-muted adjusment fst-italic">
									Harga Awal: <span class="harga-jual-awal"><?=empty($id) ? '0' : format_ribuan($harga_jual_awal)?></span>, Adjusment: <span class="adjusment-harga-jual"><?=format_ribuan($adjusment_harga_jual)?></span>
								
								</div>
								<input type="hidden" name="id_jenis_harga[]" value="<?=$val['id_jenis_harga']?>"/>
								<input type="hidden" name="harga_jual_awal[]" value="<?=$harga_jual_awal?>"/>
							</div>
						</div>
					</div>
				<?php
					if ( ($index + 1) < count($harga_jual)) {
						echo '<hr/>';
					}
				}
				?>
			</div>
			<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
			<input type="hidden" name="id" value="<?=@$id?>"/>
		</form>
	</div>
</div>
<?php
if (@$_GET['mobile'] == 'true') {
	echo $this->endSection();
}
?>