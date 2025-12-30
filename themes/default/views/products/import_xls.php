<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>Import excel 2003</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = array('class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("products/import_xls", $attrib)
                ?>
                <div class="row">
                    <div class="col-md-12">

                        <div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/xls/sample_products.xls"
                               class="btn btn-primary pull-right"><i
                                    class="fa fa-download"></i> <?= lang("download_sample_file") ?></a>
                            <span class="text-warning"><?= lang("Dòng đầu tiên trong file xls được tải xuống phải giữ nguyên."); ?></span><br/><?= lang(" Vui lòng không thay đổi thứ tự các cột."); ?>
                                <p><?= lang('Hình ảnh sản phẩm upload lên thư mục assets/uploads'); ?></p>
                                 <p style="color:red"><?= lang('Mã sản phẩm tồn tại được xem là cập nhật thông tin sản phẩm'); ?></p>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="xls_file"><?= lang("upload_file"); ?></label>
                                <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="xls_file" required="required"/>
                            </div>

                            <div class="form-group">
                                <?php echo form_submit('import', $this->lang->line("import"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>