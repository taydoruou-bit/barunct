<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Import khách hàng xls'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/import_xls", $attrib); ?>
        <div class="modal-body">
            <div class="well well-small">
                <a href="<?php echo base_url(); ?>assets/xls/customer2020.xls" class="btn btn-primary pull-right"><i
                        class="fa fa-download"></i> File mẫu</a>
                <span class="text-warning"><?= lang("Không thay đổi thứ tự và dữ liệu dòng đầu tiên"); ?></span>
            </div>
            <div class="form-group">
                <?= lang("upload_file", "userfile") ?>
                <input id="userfile" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-bv-notempty="true" data-show-upload="false"
                       data-show-preview="false" class="form-control file">
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('import', lang('import'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>