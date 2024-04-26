<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="text-center text-sm-start">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
			<a href="<?=current_url()?>/upload-excel" class="btn btn-success btn-xs"><i class="fa fa-file-excel pe-1"></i> Upload Excel</a>
			<button class="btn btn-danger btn-delete-all-barang btn-xs"><i class="fas fa-trash me-2"></i>Hapus Semua Barang</button>
		</div>
		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'ignore_foto' => 'Foto'
					, 'nama_barang' => 'Nama Barang'
					, 'deskripsi' => 'Deskripsi'
					, 'barcode' => 'Barcode'
					, 'satuan' => 'Satuan'
					, 'stok' => 'Stok'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [2,'asc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		
		?>
		<div>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:auto">
					<div class="input-group-text">Tampilkan Barang</div>
					<form method="get">
					<select name="tampilkan" class="form-select" id="tampilkan-barang">
						<option value="semua_barang" <?=empty($_GET['tampilkan']) || @$_GET['tampilkan'] == 'semua_barang' ? 'selected="selected"' : ''?>>Semua Barang</option>
						<option value="dibawah_stok_minimum" <?=@$_GET['tampilkan'] == 'dibawah_stok_minimum' ? 'selected="selected"' : ''?>>Dibawah Stok Minimum</option>
					</select>
					</form>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf" disabled="disabled"><i class="fas fa-file-pdf me-2"></i>PDF</button>
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" disabled="disabled"><i class="fas fa-file-excel me-2"></i>XLSX</button>
					<button class="btn btn-outline-secondary btn-export btn-xs" type="button" id="btn-send-email" disabled="disabled"><i class="fas fa-paper-plane me-2"></i>Email</button>
				</div>
			</div>
		</div>
		<table id="table-data" class="table display table-striped table-bordered table-hover" style="width:100%">
		<thead>
			<tr>
				<?=$th?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?=$th?>
			</tr>
		</tfoot>
		</table>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
			
			$tampilkan = !empty($_GET['tampilkan']) && $_GET['tampilkan'] == 'dibawah_stok_minimum' ? '?tampilkan=' . $_GET['tampilkan'] : '';
			
		?>
		</div>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDT' . $tampilkan?></span>
	</div>
</div>