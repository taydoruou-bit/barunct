<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>$(document).ready(function () {
        CURI = '<?= site_url('reports/profit_loss_thuan'); ?>';
    });</script>
<style>@media print {
        .fa {
            color: #EEE;
            display: none;
        }

        .small-box {
            border: 1px solid #CCC;
        }
    }</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bars"></i>Báo cáo kết quả hoạt động kinh doanh</h2>

        <div class="box-icon">
            <div class="form-group choose-date hidden-xs">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text"
                               value="<?= ($start ? $this->sma->hrld($start) : '') . ' - ' . ($end ? $this->sma->hrld($end) : ''); ?>"
                               id="daterange" class="form-control">
                        <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:ExportToExcel(this)" id="pdf2" class="tip" title="<?= lang('download_excel') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        
        <div class="row">
            <table class="table table-bordered table-hover table-striped table-reponsive" id="table-bc-v3">
                <thead>
                    <tr> 
                        <th>Ngày lập <?=date('d/m/Y H:i')?></th>  
                        <th colspan="5">
                            <h3>Báo cáo kết quả hoạt động kinh doanh</h3>
                        </th>                     
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <?=($start ? 'Từ '.date("d/m/Y",strtotime($start)) : '')?> 
                            <?=($end ? 'Đến '. date("d/m/Y",strtotime($end)) : '')?>
                           
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Doanh thu bán hàng (1)  
                        </td>
                        <td>    
                        <?= $this->sma->formatMoney($total_sales->total_over) ?>                        
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Giảm trừ Doanh thu (2 = 2.1+2.2)    
                        </td>
                        <td>   <?= $this->sma->formatMoney($total_sales->total_discount+$total_returned->total_amount) ?>                            
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Chiết khấu hóa đơn (2.1)     
                        </td>
                        <td>         
                        <?= $this->sma->formatMoney($total_sales->total_discount) ?>                       
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Giá trị hàng bán bị trả lại (2.2)     
                        </td>
                        <td>       
                        <?= $this->sma->formatMoney($total_returned->total_amount) ?>                     
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Doanh thu thuần (3=1-2)    
                        </td>
                        <td>                            
                            <?php 
                            $_dt_thuan=$total_sales->total_over-(float)$total_sales->total_discount-(float)$total_returned->total_amount;
                            echo $this->sma->formatMoney($_dt_thuan);
                            ?>
                        </td>
                        <td colspan="2">

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Giá vốn hàng bán (4)         
                        </td>
                        <td>          
                        <?= $this->sma->formatMoney($total_purchases) ?>                  
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>Lợi nhuận gộp về bán hàng (5=3-4)   
       
                        </td>
                        <td>  
                        <?php 
                        $_lai_gop=$_dt_thuan-$total_purchases;
                        echo $this->sma->formatMoney($_lai_gop);
                        ?>                          
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Chi phí (6)        
                        </td>
                        <td>     
                        <?php 
                        $_chiphi=$total_expenses->total_amount;
                        echo $this->sma->formatMoney($total_expenses->total_amount) ?>                       
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <!-- Tiên hành liệt kê chi phí theo danh mục chi phí-->
                    <?php 
                    if ($list_expenses) {
                        foreach ($list_expenses as $ex)
                        {
                            //if ($ex->total_amount>0) 
                            {                               
                            
                            ?>
                            <tr>
                                <td>
                                    <?=$ex->type;?>        
                                </td>
                                <td>      <?= $this->sma->formatMoney($ex->total_amount) ?>                       
                                </td>
                                <td colspan="2">                            
                                </td>
                            </tr>
                            <?php
                            }
                        }
                    }
                    ?>
                    <!-- Tiên hành liệt kê chi phí theo danh mục chi phí-->

                    <tr>
                        <td>
                            Lợi nhuận từ hoạt động kinh doanh (7=5-6)          
                        </td>
                        <td>        
                        <?php 
                        $_ln_kinhdoanh=$_lai_gop-$_chiphi;
                        echo $this->sma->formatMoney($_ln_kinhdoanh);
                        ?>                    
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Thu nhập khác (8)          
                        </td>
                        <td>
                        <?php 
                            $_thukhac=(float)$total_thukhac->total_amount;
                            echo $this->sma->formatMoney($_thukhac);
                        ?>                            
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <!-- Tiên hành liệt kê thu khac theo danh mục chi phí-->
                    <?php 
                    if ($list_thukhac) {
                        foreach ($list_thukhac as $ex)
                        {
                            if ($ex->total_amount>0) {                               
                            
                            ?>
                            <tr>
                                <td>
                                    <?=$ex->type;?>        
                                </td>
                                <td>      <?= $this->sma->formatMoney($ex->total_amount) ?>                       
                                </td>
                                <td colspan="2">                            
                                </td>
                            </tr>
                            <?php
                            }
                        }
                    }
                    ?>
                    <!-- Tiên hành liệt kê thu khac theo danh mục chi phí-->
                    <tr>
                        <td>
                            Chi phí khác (9)           
                        </td>
                        <td> 
                         <?php 
                            $_chikhac=0;
                            echo $this->sma->formatMoney($_chikhac);
                        ?>                           
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Lợi nhuận thuần (10=(7+8)-9)             
                        </td>
                        <td>   
                        <?php $ln_rong=$_ln_kinhdoanh+$_thukhac-$_chikhac;?>                         
                        <?php 
                            echo $this->sma->formatMoney($ln_rong);
                        ?>
                        </td>
                        <td colspan="2">                            
                        </td>
                    </tr>                    

                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/profit_loss_pdf')?>/" + encodeURIComponent('<?=$start?>') + "/" + encodeURIComponent('<?=$end?>');
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>

<style>
.small-box h4.bold {
    text-align: center;
    padding-top: 5px;
    font-size: 14px;
    font-weight: bold;
    color: rgba(255, 255, 255, 0.8);
}

.small-box p {
    font-size: 12px;
}
</style> 
<script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>

<script>
    function ExportToExcel()
    {
      let table = document.getElementsByTagName("table"); // you can use document.getElementById('tableId') as well by providing id to the table tag
      TableToExcel.convert(table[0], { // html code may contain multiple tables so here we are refering to 1st table tag
        name: `Bao_cao_kinh_doanh_<?=date("d-m-Y")?>.xlsx`, // fileName you could use any name
        sheet: {
          name: 'Sheet 1' // sheetName
        }
      });
    }
</script>
