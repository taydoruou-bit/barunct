<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_xuatxu'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_xuatxu/" . $brand->id, $attrib); ?>
        <div class="modal-body">
		<div class="form-group">
                <?= lang('code', 'code'); ?>
                <?= form_input('code', $brand->code, 'class="form-control" id="code"'); ?>
            </div>

            <div class="form-group">
                <?= lang('name', 'name'); ?>
                <?= form_input('name', $brand->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                <?= lang('note', 'note'); ?>
                <?php echo form_textarea('note', $brand->note, 'class="form-control" id="note" style="margin-top: 10px; height: 100px;"'); ?>
            </div>
            <div class="form-group">
                <?= lang("image", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <?php echo form_hidden('id', $brand->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_xuatxu', lang('edit_xuatxu'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>