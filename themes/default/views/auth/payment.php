<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    
    <div class="col-sm-12">
        <div class="warning-pv">
            <?php 
                $pos=$this->site->getPackageByUser();
            ?>
            <h5><i class="fa fa-briefcase" aria-hidden="true"></i> Gói đang dùng: <b><?=$pos['title'];?></b></h5>
            <p>
                <i class="fa fa-usd"></i> Giá: <b><?=$pos['sotien_to']==0?'Miễn phí':number_format($pos['sotien_to']);?></b>
            </p>
            <p>
                <i class="fa fa-calendar"></i> Hạn sử dụng: <b><?=date("d/m/Y",strtotime($this->site->DayUsingLeft()));?></b>
            </p>
            <p>
                 <a class="btn btn-primary btncls "  href="<?= site_url('customers/payment_now'); ?>" data-toggle="modal" data-target="#myModal"> <i class="fa fa-dollar"></i><span class="text"> <?=lang('Gia hạn')?></span>
                 </a>
            </p>
        </div>

        <ul id="myTab" class="nav nav-tabs">
            <li class=""><a href="#packageall" class="tab-grey"><?= lang('Gói dịch vụ') ?></a></li>
            <li class=""><a href="#using" class="tab-grey"><?= lang('Lịch sử gia hạn dịch vụ') ?></a></li>        
        </ul>		
        <div class="tab-content">
            <div id="packageall" class="tab-pane fade in">
                <div class="box">                   
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-check"></i><?= lang('Gói dịch vụ'); ?></h2>
                        <div class="main-task-lhson">                           
                            
                        </div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12" id="table-lhson-v3">                                
                                <table id="table_package" class="table table-bordered table-condensed table-hover table-striped table-reponsive">
                                    <thead>
                                        <tr>
                                            <th>Tính năng</th> 
                                            <?php 
                                            if (!empty($package_api)) {
                                                foreach ($package_api as $pack) 
                                                {
                                                    
                                                    if ($pack['id']==$pos['id']) {
                                                        echo '<th class="active">'.$pack['title'].'</th>';  
                                                    }else{
                                                        echo '<th>'.$pack['title'].'</th>';  
                                                    }  
                                                }
                                            }
                                            ?>                                      
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Giá</td>
                                            <?php 
                                            if (!empty($package_api)) {
                                                foreach ($package_api as $pack) 
                                                {  
                                                 
                                                    
                                                    echo '<td>'.$pack['sotien'].'</td>';    
                                                }
                                            }
                                            ?> 
                                        </tr>                                        
                                        <tr>
                                            <td>Hạn sử dụng</td>
                                            <?php 
                                            if (!empty($package_api)) {
                                                foreach ($package_api as $pack) 
                                                {                                                    
                                                    
                                                    echo '<td>'.$pack['hansudung'].'</td>';    
                                                }
                                            }
                                            ?> 
                                        </tr>
                                        <tr>
                                            <td>Gói của bạn</td>
                                            <?php 
                                            if (!empty($package_api)) {
                                                foreach ($package_api as $pack) 
                                                {
                                                    if ($pack['id']==$pos['id']) {
                                                        echo "<td>Đang sử dụng <b>".date("d/m/Y",strtotime($this->site->DayUsingLeft()))."</b></td>";
                                                    }else{
                                                        echo "<td></td>";    
                                                    }
                                                    
                                                }
                                            }
                                            ?>
                                        </tr>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="using" class="tab-pane fade in">
                <div class="box">					
                    <div class="box-header">
                        <h2 class="blue"><i class="fa-fw fa fa-check"></i><?= lang('Lịch sử gia hạn dịch vụ'); ?></h2>
						<div class="main-task-lhson">							
							
						</div>
                    </div>
                    <div class="box-content">
                        <div class="row">
                            <div class="col-lg-12" id="table-lhson-v3">                                
                                <table id="table_using" class="table table-bordered table-condensed table-hover table-striped table-reponsive">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Mã Code</th>
                                            <th>Gói dịch vụ</th>
                                            <th>Giá tiền</th>
                                            <th>Ngày gia hạn</th>
                                            <th>Hạn sử dụng</th>
                                            <th>Ngày hết hạn</th>
                                            <th>IP</th>
                                            <th>Ghi chú</th>                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (!empty($list_using)) {
                                            foreach ($list_using as $key=>$using) {
                                                ?>
                                                <tr>
                                                    <td><?=($key+1)?></td>
                                                     <td><?=$using['CODE'];?></td>
                                                     <td><?=$using['goidichvu'];?></td>
                                                    <td><?=$using['sotien'];?></td>
                                                    <td><?=$using['ngaytao']?></td>
                                                    <td><?=$using['hansudung'];?></td>
                                                    <td><?=$using['hethan'];?></td>
                                                    <td><?=$using['IP'];?></td>
                                                    <td><?=$using['note'];?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?> 
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style type="text/css">	
table#table_using {
    float: left;
    width: 100%;
}
#table-lhson-v3{
    height: 660px;
    overflow: auto;

    margin: 10px 0px;
}
table#table_using th.active {
    background: #ea1108;
}

table#table_package th {
    background: #438eb9;
    color: #fff;
    text-align: center;
    text-shadow: none;
    text-transform: uppercase;
}

table#table_package th.active {
    background: #ea1108;
}

table#table_package tr td {
    text-align: center;
}

table#table_package tr td:first-child {
    font-weight: bold;
}
</style>