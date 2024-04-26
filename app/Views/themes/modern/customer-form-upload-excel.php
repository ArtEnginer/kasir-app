<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
			helper(['html', 'format']);
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => base_url() . '/customer',
				'icon' => 'fa fa-arrow-circle-left',
				'label' => 'Daftar Customer'
			]);
		?>
		<hr/>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pilih File Excel</label>
					<div class="col-sm-5">
						<input type="file" class="file form-control" name="file_excel">
							<small class="small" style="display:block">Ekstensi file harus .xlsx. Baris pertama file excel harus disertakan dan tidak boleh dirubah, karena akan diidentifikasi sebagai nama kolom tabel database. Silakan download file excel berikut: <a href="<?=base_url() . '/public/files/Format Data Customer.xlsx" title="Format File Excel">Format Data Customer.xlsx</a>'?> </small>
						<div class="upload-file-thumb"><span class="file-prop"></span></div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>