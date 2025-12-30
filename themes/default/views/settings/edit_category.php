<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/edit_category/".$category->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('update_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code', 'code'); ?>
                <?= form_input('code', set_value('code', $category->code), 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name', 'name'); ?>
                <?= form_input('name', set_value('name', $category->name), 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("category_image", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
            <div class="form-group" id="category_select_container">
                <?= lang("parent_category", "parent")  ?>

                <?php
                
                if ($parent_categories_array&&count($parent_categories_array)>1) {
                    sort($parent_categories_array);
                    foreach ($parent_categories_array as $cat) {
                        if ($cat->id!=$category->id) {
                            $subcategories=[];
                            foreach ($allcategories as $sub) {
                                if ($sub->parent_id == $cat->parent_id) {
                                    $subcategories[]=$sub;
                                }
                            }
                            ?>
                            <select name="parent[]" id="parent-<?= $cat->id; ?>" class="form-control select skip" data-select-id="<?= $cat->id; ?>" onchange="get_subcategories(this.value, '<?= $cat->id; ?>');">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($subcategories as $subcategory): ?>
                                    
                                    <option <?=$subcategory->id==$cat->id?'selected':'';?> value="<?php echo $subcategory->id; ?>"> <?= $subcategory->name; ?></option>

                                <?php endforeach; ?>
                            </select>
                            <?php
                        }
                    }
                }else{
                    $cat[''] = lang('select').' '.lang('parent_category');
                    foreach ($categories as $pcat) {
                        $cat[$pcat->id] = $pcat->name;
                    }
                    echo form_dropdown('parent', $cat,$category->parent_id, 'class="form-control select skip" id="parent" name="parent[]" style="width:100%" onchange="get_subcategories(this.value,'.(int)$category->parent_id.')"');
                }
                
                ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_category', lang('edit_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
<script>
    $('#category_select_container select').select2('destroy');

    function get_subcategories(category_id, data_select_id) {
        var subcategories = get_subcategories_array(category_id);
        var date = new Date();
        //reset subcategories
        $('#category_select_container select').each(function () {
            if (parseInt($(this).attr('data-select-id')) > parseInt(data_select_id)) {
                
               // var selid=$(this).attr('id');
                //console.log(selid);
                //$('#category_select_container #parent-'+selid).select2('destroy');
                $(this).remove();
                
            }
        });
        if (category_id == 0) {
            return false;
        }
        if (subcategories.length > 0) {
            var new_data_select_id = date.getTime();
            var select_tag = '<select class="form-control custom-select" name="parent[]" data-select-id="' + new_data_select_id + '" onchange="get_subcategories(this.value,' + new_data_select_id + ');">' +
                '<option value="">Chọn danh mục con</option>';
            for (i = 0; i < subcategories.length; i++) {
                select_tag += '<option value="' + subcategories[i].id + '">' + subcategories[i].name + '</option>';
            }
            select_tag += '</select>';
            $('#category_select_container').append(select_tag);
        }
    }

    function get_subcategories_array(category_id) {
        var categories_array = <?php echo json_encode($allcategories) ?>;
        var subcategories_array = [];
        for (i = 0; i < categories_array.length; i++) {
            if (categories_array[i].parent_id == category_id) {
                subcategories_array.push(categories_array[i]);
            }
        }
        return subcategories_array;
    }
</script>