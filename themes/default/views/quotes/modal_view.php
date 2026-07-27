    <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
    <div class="modal-dialog modal-lg no-modal-header">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <div class="btn-group no-print pull-right" style="margin-right:15px;">
                    <button type="button" class="btn btn-xs btn-default" onclick="window.print();">
                        <i class="fa fa-print"></i> <?= lang('print'); ?>
                    </button>
                    <a href="<?= site_url('quotes/pdf/' . $inv->id . '/1') ?>"
                        target="_blank"
                        class="btn btn-xs btn-info tip"
                        title="Xem thử báo giá">
                        <i class="fa fa-eye"></i> Xem thử
                    </a>
                    <a href="<?= site_url('quotes/image/' . $inv->id) ?>"
                        target="_blank"
                        class="btn btn-xs btn-success tip"
                        title="Tải xuống ảnh PNG">
                        <i class="fa fa-file-image-o"></i> Tải ảnh
                    </a>
                </div>
                <?php if ($logo) { ?>
                    <div style=" padding: 15px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <!-- Logo bên trái -->
                            <div style="flex: 0 0 150px;">
                                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                                    alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>"
                                    style="max-width: 150px; height: auto;">
                            </div>

                            <!-- Thông tin công ty bên phải -->
                            <div style="flex: 1; text-align: center; padding-left: 20px;">
                                <h2 style="color: #dc143c; margin: 0 0 8px 0; font-size: 18px; font-weight: bold; text-transform: uppercase; line-height: 1.4;">
                                    CÔNG TY TNHH BARUN VIỆT NAM - VĂN PHÒNG ĐẠI DIỆN MIỀN TÂY
                                </h2>
                                <p style="margin: 0; font-size: 13px; line-height: 1.8;">
                                    <strong style="color: #44546A;">Địa chỉ:</strong> <strong style="color: #44546A;">165 đường D4, KDC Hồng Loan, P Hưng Thạnh, Q Cái Răng, TP Cần Thơ</strong><br>
                                    <strong style="color: #44546A;">Điện thoại:</strong> <strong style="color: #44546A;">0763.882.285 (Văn phòng) - 0917.225.931 (Ms Hằng)</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <!-- Tiêu đề báo giá -->
                <div style="text-align: center; margin: 20px 0;">
                    <h1 style="color: #0066cc; font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0;">
                        BẢNG BÁO GIÁ KIÊM XÁC NHẬN ĐẶT HÀNG
                    </h1>
                </div>

                <!-- Thông tin đơn hàng và khách hàng -->
                <div class="well well-sm">
                    <div class="row" style="display: flex; align-items: center;">
                        <!-- Cột trái: Thông tin đơn hàng -->
                        <div class="col-xs-6">
                            <p style="margin: 0; line-height: 1.8;">
                                <strong><?= lang("ref"); ?>:</strong> <?= $inv->reference_no; ?><br>
                                <strong>Ngày nhận đơn:</strong> <?= $this->sma->hrsd($inv->date); ?><br>
                                <strong>Ngày giao chành:</strong> <?= !empty($inv->delivery_date) ? $this->sma->hrsd($inv->delivery_date) : '-'; ?><br>
                                <strong>Ngày khách nhận dự kiến:</strong> <?= !empty($inv->received_date) ? $this->sma->hrsd($inv->received_date) : '-'; ?><br>
                                <strong>Ngày lắp đặt dự kiến:</strong> <?= !empty($inv->install_date) ? $this->sma->hrsd($inv->install_date) : '-'; ?><br>
                                <strong><?= lang("status"); ?>:</strong> <?= $inv->status; ?>
                            </p>
                        </div>

                        <div class="col-xs-6 order_barcodes" style="display: flex; align-items: flex-start; justify-content: flex-end; gap: 10px;">
    <div style="text-align: center;">
        <img src="<?= base_url('assets/uploads/ct1.png'); ?>" alt="ISO Certification" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
        <p style="margin: 3px 0 0 0; font-size: 11px; font-weight: bold;">Chứng nhận ISO</p>
    </div>
    <div style="text-align: center;">
        <img src="<?= base_url('assets/uploads/ct2.png'); ?>" alt="Vietnam Certification" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
        <p style="margin: 3px 0 0 0; font-size: 11px; font-weight: bold;">Chứng nhận kiểm định chất lượng</p>
    </div>
    <div style="text-align: center;">
        <img src="<?= base_url('assets/uploads/ct3.png'); ?>" alt="Vietnam No.1 Brand Awards 2025" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
        <p style="margin: 3px 0 0 0; font-size: 11px; font-weight: bold;">Chứng nhận thương hiệu số 1 Việt Nam</p>
    </div>
    <div style="text-align: center;">
        <img src="<?= base_url('assets/uploads/ct4.png'); ?>" alt="Door Certification" style="width: 75px; height: 75px; object-fit: contain; display: block; margin: 0 auto;">
        <p style="margin: 3px 0 0 0; font-size: 11px; font-weight: bold;">Chứng nhận thương hiệu tiêu biểu Châu Á</p>
    </div>
    <div style="text-align: center;">
        <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 2, 'style="width: 75px !important; height: 75px !important; display: block; margin: 0 auto;"'); ?>
        <p style="margin: 3px 0 0 0; font-size: 11px; font-weight: bold;">Xem online</p>
    </div>
</div>
                    </div>
                </div>

                <!-- Thông tin khách hàng -->
                <div class="well well-sm" style="background-color: #f9f9f9;">
                    <div class="row">
                        <div class="col-xs-12">
                            <p style="margin: 0; line-height: 1.8;">
                                <strong style="font-size: 16px;">Khách hàng: </strong>
                                <span style="font-size: 15px;"><?= $customer->company ? $customer->company : $customer->name; ?></span>
                                <?php if ($customer->company && $customer->name != $customer->company): ?>
                                    - <?= $customer->name; ?>
                                <?php endif; ?>
                                <br>
                                <strong>Địa chỉ:</strong> <?php
                                                            $address_parts = [];
                                                            if (!empty($customer->address) && $customer->address != '-') {
                                                                $address_parts[] = $customer->address;
                                                            }

                                                            $city_state = trim($customer->city . ' ' . $customer->postal_code . ' ' . $customer->state);
                                                            if (!empty($city_state) && $city_state != '-') {
                                                                $address_parts[] = $city_state;
                                                            }

                                                            if (!empty($customer->country) && $customer->country != '-') {
                                                                $address_parts[] = $customer->country;
                                                            }

                                                            echo implode(', ', $address_parts);
                                                            ?>
                                <br>
                                <strong>Điện thoại:</strong> <?= $customer->phone; ?>
                                <br>
                                <?php if (!empty($inv->shipping_info)): ?>
                                    <strong>Thông tin chành xe:</strong> <?= $this->sma->decode_html($inv->shipping_info); ?>
                                <?php endif; ?>
                                 <br>
<?php if (!empty($inv->construction_address)): ?>
    <strong>Địa chỉ công trình:</strong> 
    <?= str_replace(['<p>', '</p>'], '', $this->sma->decode_html($inv->construction_address)); ?>
<?php endif; ?>
                                <?php
                                // Hiển thị các trường custom nếu có
                                $custom_info = [];
                                if ($customer->vat_no != "-" && $customer->vat_no != "") {
                                    $custom_info[] = lang("vat_no") . ": " . $customer->vat_no;
                                }
                                if ($customer->cf1 != "-" && $customer->cf1 != "") {
                                    $custom_info[] = lang("ccf1") . ": " . $customer->cf1;
                                }
                                if ($customer->cf2 != "-" && $customer->cf2 != "") {
                                    $custom_info[] = lang("ccf2") . ": " . $customer->cf2;
                                }
                                if ($customer->cf3 != "-" && $customer->cf3 != "") {
                                    $custom_info[] = lang("ccf3") . ": " . $customer->cf3;
                                }
                                if ($customer->cf4 != "-" && $customer->cf4 != "") {
                                    $custom_info[] = lang("ccf4") . ": " . $customer->cf4;
                                }
                                if ($customer->cf5 != "-" && $customer->cf5 != "") {
                                    $custom_info[] = lang("ccf5") . ": " . $customer->cf5;
                                }
                                if ($customer->cf6 != "-" && $customer->cf6 != "") {
                                    $custom_info[] = lang("ccf6") . ": " . $customer->cf6;
                                }

                                if (!empty($custom_info)) {
                                    echo "<br>" . implode(" | ", $custom_info);
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped print-table order-table">

                        <thead>
                            <tr>
                                <th style="width:40px; text-align:center; background-color:#B4C6E7;"><strong>STT</strong></th>
                                <th style="width:120px; text-align:center; background-color:#B4C6E7;"><strong>Mẫu - Mã</strong></th>
                                <th style="width:120px; text-align:center; background-color:#B4C6E7;"><strong>Màu</strong></th>
                                <th style="width:120px; text-align:center; background-color:#B4C6E7;"><strong>Khóa</strong></th>
                                <!-- Custom columns -->
                                <?php
                                if (!empty($custom_columns)) {
                                    foreach ($custom_columns as $col) {
                                        echo '<th style="width:100px; text-align:center; background-color:#B4C6E7;"><strong>' . $col->column_name . '</strong></th>';
                                    }
                                }
                                ?>
                                <th style="width:100px; text-align:center; background-color:#B4C6E7;"><strong>Ghi chú</strong></th>
                                <th style="width:80px; text-align:center; background-color:#B4C6E7;"><strong><?= lang("quantity"); ?></strong></th>
                                <th style="width:100px; text-align:center; background-color:#B4C6E7;"><strong><?= lang("unit_price"); ?></strong></th>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<th style="width:100px; text-align:center;"><strong>' . lang("tax") . '</strong></th>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<th style="width:100px; text-align:center;"><strong>' . lang("discount") . '</strong></th>';
                                }
                                ?>
                                <th style="width:120px; text-align:center; background-color:#B4C6E7;"><strong><?= lang("subtotal"); ?></strong></th>
                            </tr>
                        </thead>


                        <tbody>
                            <?php
                            $r = 1;

                            // ✅ BƯỚC 1: TÁCH SẢN PHẨM THÀNH 2 NHÓM
                            $products_with_variants = []; // Sản phẩm có màu/khóa
                            $products_without_variants = []; // Sản phẩm không có màu/khóa (phí)

                            foreach ($rows as $group) {
                                $has_variants = ($group['color'] !== null || $group['lock'] !== null);
                                if ($has_variants) {
                                    $products_with_variants[] = $group;
                                } else {
                                    $products_without_variants[] = $group;
                                }
                            }

                            // ✅ BƯỚC 2: HIỂN THỊ CÁC SẢN PHẨM CÓ VARIANTS TRƯỚC
                            foreach ($products_with_variants as $group):
                                $main_row = $group['main'];
                                $color = $group['color'];
                                $lock = $group['lock'];
                            ?>
                                <tr>
                                    <!-- STT -->
                                    <td style="text-align:center; vertical-align:middle;"><?= $r; ?></td>

                                    <!-- TRƯỜNG HỢP CÓ COLOR/LOCK -->
                                    <td style="text-align:center; vertical-align:middle; padding:8px;">
                                        <div style="margin-bottom:8px;">
                                            <strong><?= $main_row->product_code; ?></strong>
                                            <?= $main_row->details ? '<br><small style="color:#777;">' . $main_row->details . '</small>' : ''; ?>
                                        </div>
                                        <?php
                                        $image = $group['image'] && $group['image'] != 'no_image.png'
                                            ? $group['image'] : 'no_image.png';
                                        ?>
                                        <div style="display:flex; justify-content:center; align-items:center;">
                                            <img src="<?= base_url('assets/uploads/' . $image); ?>"
                                                style="width:80px; height:80px; object-fit:cover; border:1px solid #ddd; border-radius:4px;">
                                        </div>
                                    </td>

                                    <!-- Màu -->
                                    <td style="text-align:center; vertical-align:middle; padding:8px;">
                                        <?php if ($color): ?>
                                            <div style="margin-bottom:5px;">
                                                <strong><?= $color->product_name; ?></strong>
                                            </div>
                                            <?php
                                            $color_image = $color->image && $color->image != 'no_image.png'
                                                ? $color->image : 'no_image.png';
                                            ?>
                                            <div style="display:flex; justify-content:center; align-items:center;">
                                                <img src="<?= base_url('assets/uploads/' . $color_image); ?>"
                                                    style="width:50px; height:50px; object-fit:cover; border:1px solid #ddd; border-radius:3px;">
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Khóa -->
                                    <td style="text-align:center; vertical-align:middle; padding:8px;">
                                        <?php if ($lock): ?>
                                            <div style="margin-bottom:5px;">
                                                <strong><?= $lock->product_name; ?></strong>
                                            </div>
                                            <?php
                                            $lock_image = $lock->image && $lock->image != 'no_image.png'
                                                ? $lock->image : 'no_image.png';
                                            ?>
                                            <div style="display:flex; justify-content:center; align-items:center;">
                                                <img src="<?= base_url('assets/uploads/' . $lock_image); ?>"
                                                    style="width:50px; height:50px; object-fit:cover; border:1px solid #ddd; border-radius:3px;">
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#999;">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Custom fields -->
                                    <?php
                                    if (!empty($custom_columns)) {
                                        foreach ($custom_columns as $col) {
                                            $value = isset($main_row->custom_fields[$col->column_name])
                                                ? $main_row->custom_fields[$col->column_name] : '';
                                            echo '<td style="text-align:center; vertical-align:middle;"><strong>' . $value . '</strong></td>';
                                        }
                                    }
                                    ?>

                                    <!-- Ghi chú -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <?= !empty($main_row->notes) ? '<strong>' . $main_row->notes . '</strong>' : ''; ?>
                                    </td>

                                    <!-- Số lượng -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <?= '<strong>' . $this->sma->formatQuantity($main_row->unit_quantity) . '</strong>'; ?>
                                    </td>

                                    <!-- Đơn giá -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <?php
                                        $total_unit_price = $main_row->unit_price;
                                        if ($color) {
                                            $total_unit_price += $color->unit_price;
                                        }
                                        echo '<strong>' . $this->sma->formatMoney($total_unit_price) . '</strong>';
                                        ?>
                                    </td>

                                    <!-- Thuế (nếu có) -->
                                    <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <?php
                                            $total_tax = $main_row->item_tax;
                                            if ($color) $total_tax += $color->item_tax;
                                            echo ($main_row->item_tax != 0 && $main_row->tax_code) ? '<small>(' . $main_row->tax_code . ')</small> ' : '';
                                            echo '<strong>' . $this->sma->formatMoney($total_tax) . '</strong>';
                                            ?>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Giảm giá (nếu có) -->
                                    <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <?php
                                            $total_discount = $main_row->item_discount;
                                            if ($color) $total_discount += $color->item_discount;
                                            echo ($main_row->discount != 0) ? '<small>(' . $main_row->discount . ')</small> ' : '';
                                            echo '<strong>' . $this->sma->formatMoney($total_discount) . '</strong>';
                                            ?>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Thành tiền -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <?php
                                        $total_subtotal = $main_row->subtotal;
                                        if ($color) {
                                            $total_subtotal += $color->subtotal;
                                        }
                                        echo '<strong>' . $this->sma->formatMoney($total_subtotal) . '</strong>';
                                        ?>
                                    </td>

                                </tr>
                            <?php
                                $r++;
                            endforeach;
                            // ✅ TÌM VÀ THAY THẾ TOÀN BỘ ĐOẠN NÀY (khoảng dòng 280-320)

                            // BƯỚC 3: HIỂN THỊ DÒNG KHÓA
                            $has_any_lock = false;
                            $total_lock_qty = 0;
                            $total_lock_price = 0;

                            foreach ($rows as $group) {
                                if ($group['lock']) {
                                    $has_any_lock = true;
                                    $total_lock_qty += $group['lock']->unit_quantity;
                                    $total_lock_price += $group['lock']->subtotal;
                                }
                            }

                            if ($has_any_lock && $total_lock_price > 0): ?>
                                <tr>
                                    <!-- STT -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $r; ?></strong>
                                    </td>

                                    <!-- Merge: Mẫu-Mã + Màu + Khóa + Custom Columns -->
                                    <?php
                                    $lock_merge_cols = 3; // Mẫu-Mã + Màu + Khóa
                                    if (!empty($custom_columns)) {
                                        $lock_merge_cols += count($custom_columns);
                                    }
                                    ?>
                                    <td colspan="<?= $lock_merge_cols; ?>" style="text-align:right; vertical-align:middle; padding:8px;">
                                        <strong>Khóa</strong>
                                    </td>

                                    <!-- Ghi chú -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <!-- Để trống -->
                                    </td>

                                    <!-- Số lượng -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatQuantity($total_lock_qty); ?></strong>
                                    </td>

                                    <!-- Đơn giá -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatMoney($total_lock_price / $total_lock_qty); ?></strong>
                                    </td>

                                    <!-- Thuế (nếu có) -->
                                    <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <strong>-</strong>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Giảm giá (nếu có) -->
                                    <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <strong>-</strong>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Thành tiền (Khóa) -->
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatMoney($total_lock_price); ?></strong>
                                    </td>

                                </tr>
                            <?php
                                $r++;
                            endif;

                            // ✅ BƯỚC 4: HIỂN THỊ CÁC SẢN PHẨM KHÔNG CÓ VARIANTS (PHÍ, THUẾ...)
                            foreach ($products_without_variants as $group):
                                $main_row = $group['main'];
                                $merge_cols = 3; // Mẫu-Mã + Màu + Khóa
                                if (!empty($custom_columns)) {
                                    $merge_cols += count($custom_columns);
                                }
                            ?>
                                <tr>
                                    <td style="text-align:center; vertical-align:middle;"><?= $r; ?></td>
                                    <td colspan="<?= $merge_cols; ?>" style="text-align:right; vertical-align:middle; padding:8px;">
                                        <strong><?= $main_row->product_name; ?></strong>
                                        <?= $main_row->details ? '<br><small style="color:#777;">' . $main_row->details . '</small>' : ''; ?>
                                    </td>

                                    <td style="text-align:center; vertical-align:middle;">
                                        <?= !empty($main_row->notes) ? '<strong>' . $main_row->notes . '</strong>' : ''; ?>
                                    </td>

                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatQuantity($main_row->unit_quantity); ?></strong>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatMoney($main_row->unit_price); ?></strong>
                                    </td>
                                    <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <strong><?= $this->sma->formatMoney($main_row->item_tax); ?></strong>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                        <td style="text-align:center; vertical-align:middle;">
                                            <strong><?= $this->sma->formatMoney($main_row->item_discount); ?></strong>
                                        </td>
                                    <?php endif; ?>
                                    <td style="text-align:center; vertical-align:middle;">
                                        <strong><?= $this->sma->formatMoney($main_row->subtotal); ?></strong>
                                    </td>

                                </tr>
                            <?php
                                $r++;
                            endforeach;
                            ?>
                        </tbody>

                        <?php
                        // ✅ THAY THẾ PHẦN TÍNH TOÁN COLSPAN NÀY

                        // TÍNH TOÁN SỐ CỘT COLSPAN
                        $col = 8; // STT + Mẫu-Mã + Màu + Khóa + Ghi chú + Quantity + Unit Price + Subtotal
                        if (!empty($custom_columns)) {
                            $col += count($custom_columns);
                        }
                        $adjustment_cols = 0;
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col++;
                            $adjustment_cols++;
                        }
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col++;
                            $adjustment_cols++;
                        }
                        $tcol = $col - 1;
                        $total_detail_colspan = $tcol - $adjustment_cols;
                        ?>

                        <tfoot>
    <?php if ($inv->grand_total != $inv->total) { ?>
        <tr>
            <td colspan="<?= $total_detail_colspan; ?>"
                style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total"); ?>
                (<?= $default_currency->code; ?>)
            </td>
            <?php
            if ($Settings->tax1 && $inv->product_tax > 0) {
                echo '<td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
            }
            if ($Settings->product_discount && $inv->product_discount != 0) {
                echo '<td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
            }
            ?>
            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
        </tr>
    <?php } ?>

    <?php if ($inv->order_discount != 0) {
        echo '<tr><td colspan="' . $tcol . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
    }
    ?>
    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
        echo '<tr><td colspan="' . $tcol . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
    }
    ?>
    <?php if ($inv->shipping != 0) {
        echo '<tr><td colspan="' . $tcol . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
    }
    ?>

    <!-- ✅ TỔNG CỘNG - CÓ ROWSPAN NẾU CÓ ĐẶT CỌC -->
    <!-- ✅ TỔNG CỘNG - CÓ ROWSPAN NẾU CÓ ĐẶT CỌC -->
<tr>
    <td colspan="<?= $tcol; ?>"
        style="text-align:right; font-weight:bold; color: #0066cc; background-color:#B4C6E7;">
        <?= lang("total_amount"); ?>
    </td>
    <td style="text-align:center; padding-right:10px; font-weight:bold; color: #dc143c; background-color:#B4C6E7;">
        <?= $this->sma->formatMoney($inv->grand_total); ?>
    </td>
</tr>


    <!-- ✅ TIỀN ĐẶT CỌC - KHÔNG CÓ <td> CHO CỘT GHI CHÚ -->
    <?php if ($inv->deposit_amount && $inv->deposit_amount > 0) { ?>
        <tr>
            <td colspan="<?= $tcol; ?>"
                style="text-align:right; font-weight:bold; color: #0066cc; background-color:#B4C6E7;">
                Tiền đặt cọc
            </td>
            <td style="text-align:center; padding-right:10px; font-weight:bold; color: #dc143c; background-color:#B4C6E7;">
                <?= $this->sma->formatMoney($inv->deposit_amount); ?>
            </td>
            <!-- ✅ KHÔNG CÓ <td> Ở ĐÂY VÌ ĐÃ DÙNG ROWSPAN -->
        </tr>

        <!-- ✅ CÒN LẠI - KHÔNG CÓ <td> CHO CỘT GHI CHÚ -->
        <tr>
            <td colspan="<?= $tcol; ?>"
                style="text-align:right; font-weight:bold; color: #0066cc; background-color:#B4C6E7;">
                Còn lại
            </td>
            <td style="text-align:center; padding-right:10px; font-weight:bold; color: #dc143c; background-color:#B4C6E7;">
                <?= $this->sma->formatMoney($inv->grand_total - $inv->deposit_amount); ?>
            </td>
            <!-- ✅ KHÔNG CÓ <td> Ở ĐÂY VÌ ĐÃ DÙNG ROWSPAN -->
        </tr>
    <?php } ?>
</tfoot>
                    </table>
                </div>



<div style="margin-top: 30px; padding: 20px; border-top: 2px solid #ddd;">
    <!-- Thông tin chuyển tiền - 1 dòng -->
    <div style="margin-bottom: 15px;">
        <p style="margin: 0; font-size: 13px; line-height: 1.6;">
            <strong style="color: #dc143c;">THÔNG TIN CHUYỂN TIỀN:</strong>
            <strong>BÙI THỊ HẰNG - STK: 7420179458 tại Ngân hàng TMCP Đầu tư và Phát triển Việt Nam (BIDV Bank)</strong>
        </p>
    </div>

    <!-- BẢNG 2 CỘT: THÔNG TIN SẢN PHẨM + BẢO HÀNH (TRÁI) VÀ QR (PHẢI) -->
    <table style="width: 100%; border: none; margin: 0; border-collapse: collapse; table-layout: fixed;">
        <tr>
            <!-- CỘT TRÁI: THÔNG TIN SẢN PHẨM & BẢO HÀNH (70%) -->
            <td style="width: 70%; vertical-align: top; border: none; padding-right: 20px;">
                <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: bold;">
                    <strong style="color: #dc143c;">THÔNG TIN SẢN PHẨM:</strong>
                </p>
                <div style="font-size: 12px; line-height: 1.5; margin-bottom: 12px;">
                    * Cánh cửa ABS trơn phẳng, dày 40mm (+-2mm).<br>
                    * Bề mặt cánh hoàn thiện là 2 tấm nhựa ABS (loại nhựa đặc biệt có khả năng chịu va đập)<br>
                    * Vật liệu cánh: Xung quanh bao bọc thanh nhựa PVC Bar chống nước, thanh LVL và honeycomb.<br>
                    * Khung cửa bằng nhựa rộng 110mm, thép gia cường dày 0.7-1mm<br>
                    * Nẹp khung bao di động 02 mặt: Lắp đặt linh động cho cửa tường dày 110-160mm<br>
                    * Màu cửa hoàn thiện: theo màu tiêu chuẩn của NSX
                </div>

                <!-- BẢO HÀNH SẢN PHẨM -->
                <p style="margin: 0 0 5px 0; line-height: 1.4; font-size: 13px;">
                    <strong style="color: #dc143c;">BẢO HÀNH SẢN PHẨM CỬA NHỰA ABS HÀN QUỐC BARUN VIỆT NAM</strong><br>
                    <em style="font-size: 12px;">02 năm kể từ ngày mua đối với cánh, khung, nẹp cửa</em><br>
                    <em style="font-size: 12px;">01 năm kể từ ngày mua đối với phụ kiện đi kèm</em>
                </p>
            </td>

            <!-- CỘ PHẢI: QR CHUYỂN KHOẢN (30%) -->
            <td style="width: 30%; text-align: center; vertical-align: middle; border: none; padding: 0;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px;">
                    <img src="<?= base_url('assets/uploads/qr_chuyen_khoan.jpg'); ?>"
                        alt="QR Chuyển khoản"
                        style="width: 120px; height: 120px; object-fit: contain; border-radius: 8px; margin-bottom: 8px;">
                    <p style="margin: 0; font-size: 12px; font-weight: bold; color: #0066cc; text-align: center;">
                        QUÉT MÃ CHUYỂN KHOẢN
                    </p>
                </div>
            </td>
        </tr>
    </table>

    <!-- PHẦN CHỮ KÝ DƯỚI CÙNG -->
    <div style="display: flex; justify-content: space-between; margin-top: 40px;">
        <div style="text-align: center; flex: 1;">
            <p style="margin: 0 0 80px 0; font-size: 13px;">
                <strong>CÔNG TY TNHH BARUN VIỆT NAM</strong>
            </p>
        </div>
        <div style="text-align: center; flex: 1;">
            <p style="margin: 0 0 5px 0; font-size: 13px;">
                <strong>NGƯỜI LẬP PHIẾU</strong>
            </p>
            <p style="margin: 75px 0 0 0; font-size: 13px;">
                <strong><?= $biller->name; ?></strong>
            </p>
        </div>
    </div>
</div>

                    


                <?php if (!$Supplier || !$Customer) { ?>
                    <div class="buttons">
                        <?php if ($inv->attachment) { ?>
                            <div class="btn-group">
                                <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                    <i class="fa fa-chain"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="btn-group btn-group-justified">
                            <div class="btn-group">
                                <a href="<?= site_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_sale') ?>">
                                    <i class="fa fa-heart"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('create_sale') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= site_url('purchases/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_purchase') ?>">
                                    <i class="fa fa-star"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= site_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                    <i class="fa fa-envelope-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= site_url('quotes/pdf/' . $inv->id . '/1') ?>" target="_blank" class="tip btn btn-success" title="Xem thử báo giá">
                                    <i class="fa fa-eye"></i>
                                    <span class="hidden-sm hidden-xs">Xem thử</span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="<?= site_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                    <i class="fa fa-download"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                                </a>
                            </div>

                            <div class="btn-group">
                                <a href="<?= site_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                    <i class="fa fa-edit"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                    data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                    data-html="true" data-placement="top">
                                    <i class="fa fa-trash-o"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.tip').tooltip();
        });

        // ✅ HÀM TẢI ẢNH PNG
        function downloadQuoteAsImage() {
            // Hiển thị loading
            var loadingMsg = $('<div class="loading-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; display:flex; align-items:center; justify-content:center;"><div style="background:white; padding:30px; border-radius:10px; text-align:center;"><i class="fa fa-spinner fa-spin fa-3x" style="color:#0066cc;"></i><p style="margin-top:15px; font-size:16px; font-weight:bold;">Đang tạo ảnh, vui lòng chờ...</p></div></div>');
            $('body').append(loadingMsg);

            // Ẩn các nút không cần thiết
            $('.buttons, .close, button').hide();

            // Lấy phần tử cần chụp (toàn bộ modal-body)
            var element = $('.modal-body')[0];

            html2canvas(element, {
                scale: 2, // Độ phân giải cao (1-3, càng cao càng nét nhưng file càng lớn)
                useCORS: true, // Cho phép load ảnh từ domain khác
                allowTaint: true,
                logging: false,
                width: element.scrollWidth,
                height: element.scrollHeight,
                windowWidth: 1200, // Độ rộng cố định
                backgroundColor: '#ffffff',
                onclone: function(clonedDoc) {
                    // Đảm bảo ảnh hiển thị đúng trong bản sao
                    var clonedElement = clonedDoc.querySelector('.modal-body');
                    if (clonedElement) {
                        clonedElement.style.width = '1200px';
                        clonedElement.style.padding = '20px';
                    }
                }
            }).then(function(canvas) {
                // Xóa loading
                $('.loading-overlay').remove();

                // Hiện lại các nút
                $('.buttons, .close, button').show();

                // Tạo tên file
                var filename = 'BaoGia_<?= $inv->reference_no ?>.png';

                // Chuyển canvas thành ảnh và download
                canvas.toBlob(function(blob) {
                    var link = document.createElement('a');
                    link.download = filename;
                    link.href = URL.createObjectURL(blob);
                    link.click();

                    // Thông báo thành công
                    alert('✅ Đã tải ảnh thành công: ' + filename);
                }, 'image/png', 1.0);

            }).catch(function(error) {
                // Xóa loading
                $('.loading-overlay').remove();

                // Hiện lại các nút
                $('.buttons, .close, button').show();

                console.error('Lỗi khi tạo ảnh:', error);
                alert('❌ Không thể tạo ảnh. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
            });
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        /* ===================================
        CSS CHO MÀN HÌNH - GIỮ NGUYÊN
        =================================== */
        .order-table td img {
            max-width: 100%;
            height: auto;
        }

        /* ===================================
        CSS CHO IN ẤN - FIXED HOÀN TOÀN
        =================================== */
        @media print {

            /* ẨN TẤT CẢ, CHỈ HIỆN MODAL */
            body * {
                visibility: hidden !important;
            }

            .modal,
            .modal * {
                visibility: visible !important;
            }

            /* QUAN TRỌNG: BỎ TẤT CẢ GIỚI HẠN */
            .modal {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                z-index: 1 !important;
                overflow: visible !important;
                display: block !important;
            }

            .modal-dialog {
                position: static !important;
                left: 0 !important;
                top: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: none !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                transform: none !important;
            }

            .modal-content {
                position: static !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                box-shadow: none !important;
                border: none !important;
            }

            .modal-body {
                position: static !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                padding: 15px !important;
            }

            /* ĐẢM BẢO TABLE HIỂN THỊ ĐẦY ĐỦ */
            .table-responsive {
                overflow: visible !important;
                height: auto !important;
                max-height: none !important;
            }

            table {
                page-break-inside: auto !important;
                width: 100% !important;
                border-collapse: collapse !important;
            }

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }

            tbody {
                display: table-row-group !important;
            }

            tr {
                page-break-inside: auto !important;
                page-break-after: auto !important;
                height: auto !important;
            }

            td,
            th {
                page-break-inside: avoid !important;
                vertical-align: middle !important;
                padding: 8px !important;
            }

            .modal-dialog,
            .modal-content,
            .modal-body,
            .table-responsive {
                display: block !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                page-break-inside: auto !important;
            }

            /* ===================================
        FIX ẢNH - QUAN TRỌNG - CẢI THIỆN
        =================================== */
            /* Reset tất cả ảnh về mặc định */
            img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                max-width: 100% !important;
                height: auto !important;
                display: block !important;
            }

            /* Ảnh sản phẩm chính trong table - CHỈ HIỂN THỊ ẢNH, KHÔNG CHỒNG */
            table td img {
                width: auto !important;
                height: auto !important;
                max-width: 100% !important;
                object-fit: contain !important;
                border: 1px solid #ddd !important;
                margin: 2px auto !important;
                display: block !important;
            }

            /* Container ảnh sản phẩm */
            .order-table td div[style*="display:flex"] {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                margin: 5px 0 !important;
                width: 100% !important;
                height: auto !important;
            }

            /* Logo công ty */
            img[alt*="Barun"],
            div[style*="flex: 0 0 150px"] img {
                max-width: 150px !important;
                height: auto !important;
                display: block !important;
            }

            /* Chứng chỉ cuối trang */
            img[alt*="ISO"],
            img[alt*="Certification"],
            img[alt*="Vietnam"],
            img[src*="ct1.png"],
            img[src*="ct2.png"],
            img[src*="ct3.png"],
            img[src*="ct4.png"] {
                height: 60px !important;
                width: auto !important;
                margin: 0 5px !important;
            }

            /* Barcode và QR code */
            .order_barcodes img {
                max-width: 100px !important;
                height: auto !important;
                margin: 2px !important;
            }

            /* GIỮ MÀU SẮC */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .well,
            .well-sm {
                background-color: #f9f9f9 !important;
                border: 1px solid #ddd !important;
                padding: 15px !important;
            }

            h1 {
                color: #0066cc !important;
            }

            h2 {
                color: #dc143c !important;
            }

            .table-bordered,
            .table-bordered th,
            .table-bordered td {
                border: 1px solid #ddd !important;
            }

            thead th {
                background-color: #f5f5f5 !important;
                font-weight: bold !important;
            }

            /* THIẾT LẬP TRANG IN */
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            html,
            body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            /* FIX BOOTSTRAP GRID */
            .col-xs-6 {
                width: 50% !important;
                float: left !important;
            }

            .col-xs-12 {
                width: 100% !important;
            }

            /* FIX BOOTSTRAP MODAL PRINT */
            .modal-open {
                overflow: visible !important;
            }

            .modal-backdrop {
                display: none !important;
            }

            /* BẮT BUỘC HIỂN THỊ TOÀN BỘ NỘI DUNG */
            * {
                overflow: visible !important;
                max-height: none !important;
            }

            /* ĐẢM BẢO DIV KHÔNG BỊ CẮT */
            div {
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }

            /* FIX CHO CÁC PHẦN CUỐI TRANG */
            .row {
                page-break-inside: avoid !important;
            }

            /* ẨN CÁC NÚT VÀ PHẦN TỬ KHÔNG CẦN */
            .no-print,
            .close,
            .buttons,
            button,
            .btn,
            .modal-header,
            .modal-footer {
                display: none !important;
                visibility: hidden !important;
            }
        }
        /* ===================================
   FIX KÍCH THƯỚC QR CODE VÀ CHỨNG CHỈ - 75x75px
=================================== */
.order_barcodes img {
    width: 75px !important;
    height: 75px !important;
    max-width: 75px !important;
    max-height: 75px !important;
    min-width: 75px !important;
    min-height: 75px !important;
    object-fit: contain !important;
}

/* QR Code - Target cụ thể hơn */
.order_barcodes > div > img,
.order_barcodes div img {
    width: 75px !important;
    height: 75px !important;
    max-width: 75px !important;
    max-height: 75px !important;
}
/* ===================================
   FIX KÍCH THƯỚC QR CODE - TARGET CLASS qrimg
=================================== */
.qrimg,
img.qrimg,
.order_barcodes .qrimg,
.order_barcodes img.qrimg {
    width: 75px !important;
    height: 75px !important;
    max-width: 75px !important;
    max-height: 75px !important;
    min-width: 75px !important;
    min-height: 75px !important;
    object-fit: contain !important;
    float: none !important;
    display: block !important;
    margin: 0 auto !important;
}

/* Force tất cả ảnh trong order_barcodes */
.order_barcodes img,
.order_barcodes > div > img,
.order_barcodes div img {
    width: 75px !important;
    height: 75px !important;
    max-width: 75px !important;
    max-height: 75px !important;
    min-width: 75px !important;
    min-height: 75px !important;
    object-fit: contain !important;
}

/* CSS cho print - đảm bảo QR code hiển thị đúng khi in */
@media print {
    .qrimg,
    img.qrimg,
    .order_barcodes .qrimg {
        width: 75px !important;
        height: 75px !important;
        max-width: 75px !important;
        max-height: 75px !important;
        float: none !important;
        display: block !important;
    }
    
    .order_barcodes img {
        width: 75px !important;
        height: 75px !important;
    }
}
    </style>
