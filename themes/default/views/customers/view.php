<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h4>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="margin-bottom:0;">
                    <tbody>
                    <tr>
                        <td><strong><?= lang("customer_group"); ?></strong></td>
                        <td><?= $customer->customer_group_name; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Loại khách"); ?></strong></td>
                        <td>
                            <?php
                            $style="style='display:none'";
                                if ($customer->loaikhach==0) {
                                    $style="";
                                   echo "Công ty";
                                }else{
                                    echo "Cá nhân";
                                } 
                                ?>
                            
                        </td>
                    </tr>
                    <tr <?=$style?>>
                        <td><strong><?= lang("company"); ?></strong></td>
                        <td><?= $customer->company; ?></strong></td>
                    </tr>
                    <tr <?=$style?>>
                        <td><strong><?= lang("Mã số thuế"); ?></strong></td>
                        <td><?= $customer->vat_no; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Họ và Tên"); ?></strong></td>
                        <td><?= $customer->name; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Ngày sinh"); ?></strong></td>
                        <td><?php 
                         $ngaysinh=Date("d/m/Y",strtotime($customer->ngaysinh));
                         if ($customer->ngaysinh=='0000-00-00') {
                             $ngaysinh='';
                         }; 
                         echo $ngaysinh;
                         ?>
                          <?php 
                        if ($customer->ngaysinh!='0000-00-00') {
                            if ($sotuoi!='Unknown') {
                                echo " (".$sotuoi." tuổi)";    
                            }                
                        }
                        ?>
                     </td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Giới tính"); ?></strong></td>
                        <td>
                            <?php
                                if ($customer->gioitinh==0) {
                                   echo "Nữ";
                                }else{
                                    echo "Nam";
                                } 
                                ?>
                            
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("Facebook"); ?></strong></td>
                        <td><a href="https://facebook.com/<?= $customer->facebook; ?>" target="_blank"><?= $customer->facebook; ?></a></td>
                    </tr>

                    <tr>
                        <td><strong><?= lang("email"); ?></strong></td>
                        <td><?= $customer->email; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("phone"); ?></strong></td>
                        <td><strong><?= $customer->phone; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("address"); ?></strong></td>
                        <td><?= $customer->address; ?></td>
                    </tr>
                     <tr>
                        <td><strong><?= lang("Ghi chú"); ?></strong></td>
                        <td><textarea id="ghichu" style="width: 100%;" name="ghichu" class="skip"><?=$customer->ghichu;?></textarea></td>
                    </tr>
					<tr>
                        <td><strong><?= lang("Nợ ban đầu"); ?></strong></td>
                        <td><?= number_format($customer->nobandau); ?></td>
                    </tr>
                    
                    <tr>
                        <td><strong><?= lang("award_points"); ?></strong></td>
                        <td><?= $customer->award_points; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("ccf1"); ?></strong></td>
                        <td><?= $customer->cf1; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang("ccf2"); ?></strong></td>
                        <td><?= $customer->cf2; ?></td>
                    </tr>
                    </tbody> 
                </table>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                <?php if ($Owner || $Admin || $GP['customers-edit']) { ?>
                    <a href="<?=site_url('customers/docan/'.$customer->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('Ghi độ cận'); ?></a>
                <?php } ?>
                <?php if ($Owner || $Admin || $GP['reports-customers']) { ?>
                    <a href="<?=site_url('reports/customer_report/'.$customer->id);?>" target="_blank" class="btn btn-primary"><?= lang('customers_report'); ?></a>
                <?php } ?>
                <?php if ($Owner || $Admin || $GP['customers-edit']) { ?>
                    <a href="<?=site_url('customers/edit/'.$customer->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_customer'); ?></a>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>