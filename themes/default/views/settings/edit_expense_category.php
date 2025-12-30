<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Cập nhật danh mục thu / chi</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_expense_category/" . $category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', $category->code, 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', $category->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                Loại danh mục
                <?php
                $doituong[''] = 'Chọn phân loại';
				$doituong['1'] = "Loại phiếu thu";
				$doituong['0'] = "Loại phiếu chi";
                
                echo form_dropdown('type', $doituong,$category->type, 'id="type" class="form-control input-tip select" style="width:100%;" required="required"');
                ?>
            </div>
            <div class="form-group">
                <?= lang('Nợ', 'no'); ?>
                <?= form_input('no', $category->no, 'class="form-control" id="no" '); ?>
            </div>
            <div class="form-group">
                <?= lang('Có', 'co'); ?>
                <?= form_input('co', $category->co, 'class="form-control" id="co"'); ?>
            </div>
            <?php echo form_hidden('id', $category->id); ?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_expense_category', 'Cập nhật', 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>