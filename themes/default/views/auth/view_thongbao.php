
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header thongbao">
    <div class="modal-content">
        <div class="modal-body">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-4">
                        <label>Ngày gửi</label>
                    </div>
                    <div class="col-md-8">
                        <label><?=date('d/m/Y',strtotime($thongbao['created']));?></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4">
                        <label>Tiêu đề</label>
                    </div>
                    <div class="col-md-8">
                        <label><?=$thongbao['title'];?></label>
                    </div>
                </div>
                
                <?php 
                    if ($thongbao['hinhanh']!='')
                    {
                          ?>
                <div class="form-group">
                    <div class="col-md-12" style="margin:0 auto;text-align: center ;">
                        <a href="<?=$thongbao['lienket']!=''?$thongbao['lienket']:'#'?>"><img src="<?=$thongbao['hinhanh']?>" style="max-width: 100%";/></a>
                    </div>
                </div>
                          <?php    
                    }  
                ?>
                <div class="form-group" id="noidungvf" style="  padding: 15px;border: 1px solid #ccc;overflow: auto;max-height: 500px;">
                    <?php echo $thongbao['noidung']; ?> 
                </div>
            </div>
       </div>
       <div class="modal-footer">
            <button type="button" class="btn btn-default btn-sm btn-close" data-dismiss="modal"><i
                    class="fa fa-undo"></i> Đóng
            </button>
    </div>
    </div>
</div>
<style type="text/css">
    .thongbao .modal-body {
        float: left;
        width: 100%;
    }

    .thongbao .form-group {
        float: left;
        width: 100%;
    }

    .thongbao .col-md-12 {
        padding: 0px;
    }

    .thongbao img {
        max-height: 200px;
        width: auto;
    }
</style>