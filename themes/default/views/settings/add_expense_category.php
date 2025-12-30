<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Thêm phân loại thu / chi</h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_expense_category", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
			<div class="form-group">
                Loại danh mục
                <?php
                $doituong[''] = 'Chọn phân loại';
				$doituong['1'] = "Loại phiếu thu";
				$doituong['0'] = "Loại phiếu chi";
                
                echo form_dropdown('type', $doituong, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" class="form-control input-tip select" style="width:100%;" required="required"');
                ?>
            </div>
            <div class="form-group">
                <?= lang('Nợ', 'no'); ?>
                <?= form_input('no', '', 'class="form-control" id="no"'); ?>
            </div>
            <div class="form-group">
                <?= lang('Có', 'co'); ?>
                <?= form_input('co', '', 'class="form-control" id="co"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('add_expense_category', 'Thêm loại', 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<?= $modal_js ?>