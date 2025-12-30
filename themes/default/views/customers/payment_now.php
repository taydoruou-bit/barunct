<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('GIA HẠN DỊCH VỤ'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-payment_now-form');
        echo form_open_multipart("customers/payment_now", $attrib); ?>
        <div class="modal-body">                 

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group company" id="hansudung-wap">
                        <?= lang("Hết hạn vào ", "company"); ?>
                        <i style="color:#ea1108;font-size: 16px" id="hansudung" data-hsd="<?=date("Y-m-d",strtotime($this->site->DayUsingLeft()));?>"><?=date("d/m/Y",strtotime($this->site->DayUsingLeft()));?></i>
                        <b></b>
                    </div>
                    <?php 
                        $pos=$this->site->getPackageByUser();
                    ?>                   
                    
                     <div class="form-group">
                        <?= lang("Gói", "vat_no"); ?>
                        <b><?=$pos['title'];?></b>
                    </div>
                     
                    <div class="form-group">
                        <?= lang("Mã Code", "code"); ?>
                        <input type="text" name="code"  placeholder="Mã code kích hoạt" value="" class="form-control" id="code"/>
                    </div>
                 
                    <div class="form-group">
                        <?= lang("Ghi chú", "payment_note"); ?>
                        <?php echo form_textarea('payment_note', (isset($_POST['payment_note']) ? $_POST['note'] : ""), 'class="form-control skip" id="payment_note"'); ?>
                    </div>                 
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('payment_now', lang('Gia hạn'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

