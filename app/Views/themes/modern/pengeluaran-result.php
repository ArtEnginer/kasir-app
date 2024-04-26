<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="text-center text-sm-start">
			<?php if (has_permission('create')) { ?>
				<a href="<?=current_url()?>/add" class="btn btn-success btn-xs btn-add"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
				<a href="<?=current_url()?>/upload-excel" class="btn btn-success btn-xs"><i class="fa fa-file-excel pe-1"></i> Upload Excel</a>	
			<?php } if (has_permission('delete_all')) { ?>
				<button class="btn btn-danger btn-delete-all-data btn-xs" <?=$jml_data ? '' : 'disabled'?>><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
			<?php } ?>
		</div>
		<hr/>
		<?php 
		
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama_pengeluaran' => 'Nama Pengeluaran'
					, 'nama_kategori' => 'Kategori'
					, 'total_pengeluaran' => 'Jumlah'
					, 'tgl_pengeluaran' => 'Tgl. Pengeluaran'
					, 'nama_jenis_bayar' => 'Jenis'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [1,'asc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		
		helper ('html');
		$disabled = !$jml_data ? 'disabled="disabled"' : '';
		?>
		<div class="row">
			<div class="col-sm-6 mb-3 text-center text-sm-start">
				<div class="input-group d-flex flex-nowrap" style="width:350px">
					<div class="input-group">
						<span class="input-group-text">Periode</span>
						<input type="text" class="form-control" name="daterange" style="width:200px" id="daterange" value="<?=$start_date?> s.d. <?=$end_date?>" />
					</div>
				</div>
			</div>
			<div class="col-sm-6 mb-3 text-center text-sm-end" style="text-align:right">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel-pengeluaran" <?=$disabled?>><i class="fas fa-file-excel me-2"></i>XLSX</button>
				</div>
			</div>
		</div>
		<div class="table-responsive">
		<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
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
		</div>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/pengeluaran/getDataDT?daterange=' . @$_GET['daterange']?></span>
	</div>
</div>