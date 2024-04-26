<?php
helper('html');
?>
<form method="post" action="" class="form-horizontal p-3">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama_rekanan" value="<?=@$rekanan['nama_rekanan']?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Alamat</label>
			<div class="col-sm-9">
				<textarea class="form-control" type="text" name="alamat"/><?=@$rekanan['alamat']?></textarea>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">No. Telp</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="no_telp" value="<?=@$rekanan['no_telp']?>"/>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>