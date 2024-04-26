<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal form-pendapatan-lain px-3" enctype="multipart/form-data">
	<div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Nama Rekanan</label>
			<div class="col-sm-9">
				<div class="input-group">
					<input class="form-control" type="text" id="nama-rekanan" name="nama_rekanan" disabled="disabled" readonly="readonly" value="<?=set_value('nama_rekanan', @$pengeluaran['nama_rekanan'] ?: '')?>"/>
					<?php
					$display = @$pengeluaran['id_rekanan'] ? '' : ' style="display: none"';
					echo '<button type="button" class="btn btn-outline-danger del-rekanan" ' . $display . '><i class="fas fa-times"></i></button>';
					?>
					<button type="button" class="btn btn-outline-secondary cari-rekanan"><i class="fas fa-search"></i> Cari</button>
					<a href="<?=base_url()?>/pendapatan-lain/add" target="_blank" class="btn btn-outline-success btn-add-rekanan" id="add-rekanan" href="javascript:void(0)"><i class="fas fa-plus"></i> Tambah</a>
				</div>
				<input type="hidden" name="id_rekanan" id="id-rekanan" value="<?=set_value('id_rekanan', @$pengeluaran['id_rekanan'])?>"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Bukti</label>
			<div class="col-sm-9">
				<div class="input-group">
					<span class="input-group-text">No.</span>
					<input class="form-control" type="text" name="no_bukti" value="<?=set_value('no_bukti', @$pengeluaran['no_bukti'])?>"/>
					<span class="input-group-text">Tgl.</span>
					<input class="form-control flatpickr tanggal-invoice flatpickr" type="text" name="tgl_bukti" style="max-width:150px" value="<?=@$pengeluaran['tgl_bukti'] ? format_tanggal(@$pengeluaran['tgl_bukti'], 'dd-mm-yyyy') : ''?>" required="required"/>
				</div>
			</div>
		</div>
		<div class="form-group row mb-2">
			<label class="col-sm-3 col-form-label">File Bukti</label>
			<div class="col-sm-9">
				<div id="list-file-bukti" class="d-flex flex-wrap gallery-container" style="margin-top:0">
					<?php
						helper('filepicker');
						
						$initial_item = false;
						if (empty($pengeluaran['file'])) {
							$initial_item = true;
							$pengeluaran['file'][] = ['id_pengeluaran' => '', 'id_file_picker' => '', 'nama_file' => ''];
						}
						
						$display = $initial_item ? ' style="display:none"' : '';
						echo '<ul id="list-image-container" class="list-image-container">';
							
						foreach ($pengeluaran['file'] as $val) {
							// $icon_file = $list_file_type[$val['mime_type']]['extension'];
							
							$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
							?>
							<li class="thumbnail-item"<?=$data_initial_item?> id="barang-<?=$val['id_pengeluaran']?>"<?=$display?> data-id-file="<?=$val['id_file_picker']?>">
								<div class="toolbox">
									<?php if (@$id_kategori != '') { ?>
										<div class="grip"><i class="fas fa-grip-horizontal"></i></div>
									<?php } ?>
									<ul class="right-menu">
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
				</div>
				<button type="button" class="add-file btn btn-outline-secondary btn-xs"><i class="fas fa-plus me-2"></i>Add File</button>
			</div>
		</div>
		<hr/>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Tgl. Pengeluaran</label>
			<div class="col-sm-9">
				<input class="form-control flatpickr tanggal-bayar flatpickr" type="text" name="tgl_pengeluaran" value="<?=@$pengeluaran['tgl_pengeluaran'] ? format_tanggal(@$pengeluaran['tgl_pengeluaran'], 'dd-mm-yyyy') : date('d-m-Y')?>" placeholder="Tanggal Pengeluaran" required="required"/>
			</div>
		</div>
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Kategori</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_pengeluaran_kategori', 'class' => 'list-kategori', 'style' => 'width:100%'], $list_kategori, set_value('id_pengeluaran_kategori', @$pengeluaran['id_pengeluaran_kategori']))?>
			</div>
		</div>	
		<div class="form-group row mb-3">
			<label class="col-sm-3 col-form-label">Sumber</label>
			<div class="col-sm-9">
				<?=options(['name' => 'id_pendapatan_jenis', 'class' => 'select2'], $sumber_dana, @$pengeluaran['id_pendapatan_jenis'])?>
			</div>
		</div>
		<div class="form-group row mb-3">
			<div class="col-sm-12">
				<?php
				helper('html');
				
				echo '
				<table style="width:100%" id="tabel-list-item-pengeluaran" class="table table-stiped table-bordered mt-3">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Pengeluaran</th>
							<th>Keterangan</th>
							<th style="width:130px">Jumlah</th>
							<th style="width:20px"></th>
						</tr>
					</thead>
					<tbody>';
						
						$no = 1;
						// Barang
						$display = '';
						$sub_total = 0;
						if (empty($pengeluaran['detail'])) {
							$pengeluaran['detail'][] = [];
						}
	
						$total = 0;
						foreach ($pengeluaran['detail'] as $index => $val) 
						{
							$total += @$val['nilai_pengeluaran'];
							$jumlah_pengeluaran = @$val['nilai_pengeluaran'] ? $val['nilai_pengeluaran'] : '';
							$button = $index == 0 ? '<button type="button" class="btn btn-outline-success add-row"><i class="fas fa-plus"></i></button>' :
										'<button type="button" class="btn btn-outline-danger del-row"><i class="fas fa-times"></i></button>';
										
							echo '<tr class="row-item-bayar">
								<td>' . $no . '</td>
								<td>
									<input type="text" class="form-control mb-2" name="nama_pengeluaran[]" value="' . @$val['nama_pengeluaran'] . '"/>
								</td>
								<td><textarea class="form-control" style="height:auto" name="keterangan[]">' . @$val['keterangan'] . '</textarea></td>
								<td><input class="form-control number item-nilai-bayar text-end" name="nilai_pengeluaran[]" value="' . format_number($jumlah_pengeluaran) . '"/><textarea style="display:none" name="detail_jenis_pembayaran[]">' . json_encode($val) . '</textarea></td>
								<td>' . $button . '</td>
							</tr>';
							
							$no++;
						}
						
						$total = $total ? format_number($total) : '';
						
						echo '
							<tfoot>
							<tr id="row-total-bayar">
								<td></td>
								<td colspan="2"><div class="d-flex justify-content-between">Total ' . options(['name' => 'id_jenis_bayar', 'style' => 'width:auto'], $metode_pembayaran, set_value('id_jenis_bayar', @$pengeluaran['id_jenis_bayar']))  . '</div></td>
								<td class="text-end fw-bold" style="padding-right:17px" id="total-item-nilai-bayar">' . $total . '</td>
								<td></td>
							</tr>
							</tfoot>';
					echo '
					</tbody>
				</table>';
				?>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" id="id-pengeluaran" value="<?=set_value('id', @$pengeluaran['id_pengeluaran'])?>"/>
</form>