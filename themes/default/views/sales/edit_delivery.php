<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('edit_delivery'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/edit_delivery/" . $delivery->id, $attrib); ?>
        <div class="modal-body">
            <div class="row">
            <div class="col-md-6">
                
            <?php if ($Owner || $Admin) { ?>
                <div class="form-group">
                    <?= lang("date", "date"); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($delivery->date)), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= lang("Ngày giao hàng", "ngaynhan"); ?>
                <?= form_input('ngaynhan', date("d/m/Y H:i",strtotime($delivery->ngaynhan)), 'class="form-control datetime" id="ngaynhan" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang("do_reference_no", "do_reference_no"); ?>
                <?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $delivery->do_reference_no), 'class="form-control tip" id="do_reference_no" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("sale_reference_no", "sale_reference_no"); ?>
                <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $delivery->sale_reference_no), 'class="form-control tip" id="sale_reference_no" required="required"'); ?>
            </div>
            <input type="hidden" value="<?= $delivery->sale_id; ?>" name="sale_id"/>

            <div class="form-group">
                <?= lang("Người nhận", "customer"); ?>
                <?= form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $delivery->customer), 'class="form-control" id="customer" required="required" '); ?>
            </div>
             <div class="form-group">
                <?= lang("phone", "phone"); ?>
                <?= form_input('phone', ($delivery->phone), 'class="form-control" id="phone" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang("address", "address"); ?>
                <?= form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $delivery->address), 'class="form-control skip" id="address" required="required"'); ?>
            </div>
            </div>
            <div class="col-md-6">

            <div class="form-group">
                <?= lang('status', 'status'); ?>
                <?php
                $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                ?>
                <?= form_dropdown('status', $opts, (isset($_POST['status']) ? $_POST['status'] : $delivery->status), 'class="form-control not" id="status" required="required" style="width:100%;"'); ?>
            </div>

			<div class="form-group" id="div_doitac_id">
				<?= lang("delivered_by", "delivered_by"); ?>
													
					 
					 <?php                                     
                                    
                        $dt["0"] = "Chọn đối tác";
                        foreach ($doitacs as $tax) { 
                            $dt[$tax->id] = $tax->name;   
                        }
                        echo form_dropdown('delivered_by', $dt, (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : $delivery->delivered_by), 'id="delivered_by" data-placeholder="' . lang("select") . ' ' . lang("doitac") . '" class="form-control input-tip select not" style="width:100%;" required');
                        ?>
			</div>
			<div class="form-group">
                        <?= lang("Phí giao hàng", "shipping"); ?>
                        <?= form_input('shipping', (isset($_POST['shipping']) ? $_POST['shipping'] : $delivery->shipping), 'class="form-control" id="shipping"'); ?>
                    </div>
          
            <div class="form-group">
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $delivery->note), 'class="form-control" id="note"'); ?>
            </div>
            </div>
            </div>

        </div>
        <div class="modal-footer">
            <?= form_submit('edit_delivery', lang('edit_delivery'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
	
	
    });
</script>
