<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data">
			<div>
				<div class="bg-lightgrey p-3 ps-4 mb-3">
					<h5>Nota</h5>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Lebar Kertas</label>
					<div class="col-sm-5">
						<?php
						echo options(['name' => 'lebar_kertas_nota'], ['58mm' => '58mm', '80' => '80mm'], set_value('lebar_kertas_nota', @$setting['lebar_kertas_nota']));
						?>
					</div>
				</div>
				<div class="bg-lightgrey p-3 ps-4 mb-3">
					<h5>Invoice</h5>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Ukuran Kertas</label>
					<div class="col-sm-5">
						<div class="d-flex">
							<?php
							$readonly = @$setting['ukuran_kertas'] == 'custom' ? '' : 'readonly="readonly"';
							?>
							<?=options(['name' => 'ukuran_kertas', 'style' => 'width:auto', 'id' =>'paper-size', 'class' => 'me-2'], ['a4' => 'A4', 'f4' => 'F4', 'custom' => 'Custom'], set_value('ukuran_kertas', @$setting['ukuran_kertas']))?>
							<div class="input-group">
								<span class="input-group-text">W</span>
								<input type="text" class="form-control text-end" name="paper_width" id="paper-size-width" value="<?=set_value('paper_width', @$setting['paper_width'])?>" <?=$readonly?>/>
								<span class="input-group-text bg-light">mm</span>
								<span class="input-group-text">X</span>
								<span class="input-group-text">H</span>
								<input type="text" class="form-control text-end" name="paper_height" id="paper-size-height" value="<?=set_value('paper_height', @$setting['paper_height'])?>" <?=$readonly?>/>
								<span class="input-group-text bg-light">mm</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Auto Page Break</label>
					<div class="col-sm-5">
						<?php
						echo options(['name' => 'auto_page_break'], ['N' => 'Tidak', 'Y' => 'Ya'], set_value('auto_page_break', @$setting['auto_page_break']));
						?>
						<small>Otomatis bersambung ke halaman berikutnya. Terkadang menghasilkan layout yang kurang bagus.</small>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Margin</label>
					<div class="col-sm-5">
						<div class="d-flex mb-2">
							<div class="input-group">
								<span class="input-group-text">Kiri</span>
								<input type="text" class="form-control text-end" name="margin_left" value="<?=@$setting['margin_left']?>"/>
								<span class="input-group-text bg-light">mm</span>
								<span class="input-group-text">Kanan</span>
								<input type="text" class="form-control text-end" name="margin_right" value="<?=@$setting['margin_right']?>"/>
								<span class="input-group-text bg-light">mm</span>
							</div>
						</div>
						<div class="d-flex">
						<div class="input-group">
								<span class="input-group-text">Atas</span>
								<input type="text" class="form-control text-end" name="margin_top" value="<?=@$setting['margin_top']?>"/>
								<span class="input-group-text bg-light">mm</span>
								<span class="input-group-text">Bawah</span>
								<input type="text" class="form-control text-end" name="margin_bottom" value="<?=@$setting['margin_bottom']?>"/>
								<span class="input-group-text bg-light">mm</span>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>