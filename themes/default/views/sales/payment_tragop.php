<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel">Thu hồi trả góp</h4>
        </div>
        <?php 
        $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/modal_thuno/" . $inv['id'], $attrib); ?>
        <div class="modal-body" style="float: left;background: #fff;width: 100%;">
           
            <div class="clearfix"></div>
            <div id="payments">
                <div class="col-md-12">
                     <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("Đơn vị", "date"); ?>
                                <p><?=$inv['paid_by'];?></p>
                            </div>
                        </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <?= lang("reference_no", "reference_no"); ?>
                             <p><?=$inv['payment_ref'];?></p>
                        </div>
                    </div>
                    <input type="hidden" value="<?php echo $inv['id']; ?>" name="payment_id"/>
                </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("Tổng hóa đơn", "c_name"); ?>
                                <input name="c_name" disabled type="text" id="c_name" value="<?=$this->sma->formatMoney($inv['amount']);?>" class="form-control c_name"
                                       />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("Đã thanh toán", "c_name"); ?>
                                <input name="c_name" disabled type="text" id="c_name" value="<?=$this->sma->formatMoney($inv['sotien_tragop']);?>" class="form-control c_name"
                                       />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="payment">
                                <div class="form-group">
                                    <?= lang("Số tiền phải thu", "amount_1"); ?>
                                    <input name="amount-paid" type="text" id="amount_1"
                                           value="<?= $this->sma->formatDecimal($inv['amount'] - $inv['sotien_tragop']); ?>"
                                           class="pa form-control kb-pad amount" required="required"/>
                                </div>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin) { ?>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("date", "date"); ?>
                                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="clearfix"></div>   
                    <div class="form-group">
                        <?= lang("note", "note"); ?>
                        <?php echo form_textarea('note', $inv['note'], 'class="form-control skip" id="note"'); ?>
                    </div>                  
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php 
            if ($inv['sotien_tragop']!=$inv['amount']) {
                echo form_submit('add_payment', lang('add_payment'), 'class="btn btn-primary"');    
            }           
            //hien thi danh sach thanh toan by payment_id            
            if ($list_tragop) {
                ?>
                <table id="CompTable" cellpadding="0" cellspacing="0" border="0"
                       class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th style="width:15%;"><?= $this->lang->line("date"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("warehouse"); ?></th>
                        <th style="width:10%;"><?= $this->lang->line("amount"); ?></th>
                        <th style="width:auto;"><?= $this->lang->line("note"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("user"); ?></th>
                        <th style="width:15%;"><?= $this->lang->line("Lúc"); ?></th>
                        <th style="width:10%;"><?= $this->lang->line("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($list_tragop as $payment) {
                            ?>
                            <tr class="row<?= $payment->id ?>">
                                    <td><?= $this->sma->hrld($payment->date); ?></td>
                                    <td><?= $payment->warehouse; ?></td>                                    
                                    <td><?= $this->sma->formatMoney($payment->amount); ?></td>
                                    <td><?= $payment->note; ?></td>
                                    <td><?= $payment->nhanvien; ?></td>
                                    <td><?= $this->sma->hrld($payment->created); ?></td>     
                                    <td>
                                        <div class="text-center">                                                
                                            <a href="#" class="po"
                                               title="<b><?= $this->lang->line("delete_payment") ?></b>"
                                               data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' id='<?= $payment->id ?>' href='<?= site_url('sales/delete_payment_tragop/' . $payment->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn po-close'><?= lang('no') ?></button>"
                                               rel="popover"><i class="fa fa-trash-o"></i></a>
                                           
                                        </div>
                                    </td>
                                </tr>
                            <?php
                        }?>
                        </tbody>
                    </table>
                <?php
            }
             ?>
        </div>

    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>

<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {        
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
