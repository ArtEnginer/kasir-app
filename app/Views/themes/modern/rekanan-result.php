<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<div class="text-center text-sm-start">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-xs btn-add"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
			<button class="btn btn-danger btn-delete-all-data btn-xs" <?=$jml_data ? '' : 'disabled'?>><i class="fas fa-trash me-2"></i>Hapus Semua Data</button>
		</div>
		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					'ignore_urut' => 'No'
					, 'nama_rekanan' => 'Nama Rekanan'
					, 'alamat' => 'Alamat'
					, 'no_telp' => 'Telp'
					, 'ignore_action' => 'Action'
				];
		
		$settings['order'] = [1,'asc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th style="width:auto">' . $val . '</th>'; 
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		
		?>
		<div class="table-responsive" style="max-width:750px">
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
		<span id="dataTables-url" style="display:none"><?=base_url() . '/rekanan/getDataDT'?></span>
	</div>
</div>