<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("quote") . " " . $inv->reference_no; ?></title>
    <link href="<?php echo $assets ?>styles/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style type="text/css">
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    html, body { 
        height: auto !important;
        background: #FFF !important; 
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    
    .product-info-content br {
        display: block !important;
        content: "";
        font-weight: normal !important;
    }
    
    #wrap {
        width: 1120px !important;
        max-width: calc(100vw - 48px) !important;
        height: auto !important;
        padding: 0 !important;
        margin-left: auto !important;
        margin-right: auto !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        background: white;
        max-height: none !important;
        overflow: visible !important;
        box-sizing: border-box !important;
    }

    body:before, body:after { 
        display: none !important; 
    }
    
    .row {
        padding: 20px !important;
    }
   
    @media print {
        div[style*="display: flex; align-items: center; justify-content: center"] {
            gap: 5px !important;
            flex-wrap: nowrap !important;
        }

        div[style*="display: flex; align-items: center; justify-content: center"] img {
            height: 50px !important;
            width: auto !important;
            max-width: 55px !important;
            flex-shrink: 0 !important;
        }
    }
    
    div[style*="display: flex; align-items: center; justify-content: center"] img {
        height: 55px !important;
        width: auto !important;
        max-width: 60px !important;
    }

    img[alt="QR Chuyển khoản"] {
        width: 90px !important;
        height: 90px !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto !important;
    }
    
    /* ========== PHẦN HEADER ========== */
    .header-section {
        font-size: 10px !important;
        margin-bottom: 10px;
    }
    
    .header-section h2 {
        font-size: 12px !important;
        margin: 0 0 3px 0 !important;
        line-height: 1.2 !important;
        color: #dc143c !important;
        font-weight: bold !important;
    }
    
    .header-section p {
        font-size: 10px !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        font-weight: normal !important;
    }
    
    .company-logo { 
        max-width: 100px !important;
    }
    
    /* ========== TIÊU ĐỀ CHÍNH H1 ========== */
    h1 {
        color: #0066cc !important;
        font-size: 14px !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        margin: 8px 0 !important;
        text-align: center !important;
    }
    
    /* ========== PHẦN THÔNG TIN (WELL) ========== */
    .well, .well-sm {
        background-color: #f9f9f9 !important;
        border: 1px solid #ddd !important;
        padding: 8px !important;
        margin-bottom: 8px !important;
        font-size: 12px !important;
    }
    
    .well p, .well-sm p {
        font-size: 10px !important;
        line-height: 1.4 !important;
        margin: 0 !important;
    }
    
    .well strong, .well-sm strong {
        font-size: 10px !important;
        font-weight: bold !important;
    }
    
    .well .data-text, .well-sm .data-text {
        font-size: 10px !important;
        font-weight: normal !important;
    }
    
    .qr-code {
        max-width: 60px !important;
    }
    
    /* ========== BẢNG SẢN PHẨM ========== */
/* ========== BẢNG SẢN PHẨM ========== */
.table {
    width: 100% !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
    margin-bottom: 15px !important;
    border: 1px solid #000 !important;
}

.table thead th { 
    text-align: center !important; 
    padding: 5px 4px !important; 
    font-weight: bold !important;
    background-color: #B4C6E7 !important; 
    border-right: 1px solid #000 !important;
    border-bottom: 1px solid #000 !important;
    font-size: 11px !important;
    color: #000 !important;
    width: auto !important;
}

.table thead th:first-child {
    border-left: none !important;
}

.table thead th strong {
    font-weight: bold !important;
    font-size: 11px !important;
}

.table tbody td { 
    padding: 5px 4px !important; 
    text-align: center !important;
    vertical-align: middle !important;
    border-right: 1px solid #000 !important;
    border-bottom: 1px solid #000 !important;
    font-weight: bold !important;
    font-size: 9px !important;
    color: #000 !important;
}

.table tbody td:first-child {
    border-left: none !important;
}

.table tfoot td {
    padding: 5px 4px !important;
    text-align: center !important;
    vertical-align: middle !important;
    border-right: 1px solid #000 !important;
    border-bottom: 1px solid #000 !important;
    font-size: 9px !important;
    font-weight: bold !important;
    color: #000 !important;
}

.table tfoot td:first-child {
    border-left: none !important;
}

.table tfoot tr:last-child td {
    border-bottom: none !important;
}
    
    /* ========== HÌNH ẢNH SẢN PHẨM ========== */
    .product-img {
        width: 70px !important;
        height: 70px !important;
        object-fit: cover !important;
        border-radius: 4px !important;
    }
    
    .variant-img {
        width: 50px !important;
        height: 50px !important;
        object-fit: cover !important;
        border-radius: 3px !important;
    }
    
    .product-cell {
        text-align: center !important;
        vertical-align: middle !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }

    .quote-stt-col {
        width: 4% !important;
    }

    .quote-model-col,
    .quote-color-col,
    .quote-lock-col {
        width: 11% !important;
    }

    .quote-note-col,
    .quote-note-cell {
        text-align: center !important;
        vertical-align: middle !important;
        width: 11% !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }

    .quote-direction-col,
    .quote-direction-cell {
        width: 9% !important;
    }

    .quote-qty-col,
    .quote-qty-cell {
        width: 7% !important;
    }

    .quote-price-col,
    .quote-price-cell,
    .quote-total-col,
    .quote-total-cell {
        width: 10% !important;
    }

    .product-cell strong {
        font-weight: bold !important;
        font-size: 9px !important;
    }

    .product-img-container {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        margin: 8px 0 !important;
    }
    
    /* ========== PHẦN THÔNG TIN SẢN PHẨM (NOTE) ========== */
    .product-info-section {
        font-size: 10px !important;
        margin: 15px 0 !important;
    }
    
    .product-info-section p {
        font-size: 10px !important;
        line-height: 1.5 !important;
        margin: 0 0 5px 0 !important;
        font-weight: bold !important;
    }
    
    .product-info-section strong {
        font-size: 10px !important;
        font-weight: bold !important;
    }
    
    .product-info-section div {
        padding: 8px !important;
    }
    
    /* Điều chỉnh phần thông tin sản phẩm */
    .product-info-wrapper {
        position: relative !important;
        isolation: isolate !important;
    }

    .product-info-wrapper * {
        position: relative !important;
        float: none !important;
        clear: both !important;
    }

    .product-info-content * {
        max-width: 100% !important;
        position: static !important;
    }

    .product-info-content p,
    .product-info-content div,
    .product-info-content span {
        position: relative !important;
        margin: 3px 0 !important;
        line-height: 1.4 !important;
        font-size: 10px !important;
        font-weight: normal !important;
    }

    .product-info-content {
        font-weight: normal !important;
    }

    .product-info-content strong {
        font-weight: normal !important;
    }
    
    .product-info-content table {
        width: 100% !important;
        border-collapse: collapse !important;
        table-layout: auto !important;
        margin: 8px 0 !important;
    }

    .product-info-content table td {
        padding: 5px !important;
        border: 1px solid #000 !important;
        vertical-align: top !important;
        line-height: 1.4 !important;
        font-size: 10px !important;
    }

    .product-info-content table tr {
        page-break-inside: avoid !important;
    }

    .product-info-content table tbody tr td[rowspan] {
        height: auto !important;
        display: table-cell !important;
    }
    
    /* ========== PHẦN FOOTER ========== */
    .footer-section {
        font-size: 8px !important;
        margin-top: 8px !important;
        padding: 8px !important;
        border-top: 2px solid #ddd !important;
    }
    
    .footer-section p {
        font-size: 10px !important;
        line-height: 1.4 !important;
        margin: 0 0 5px 0 !important;
        padding: 0 !important;
        font-weight: normal !important;
    }
    
    .footer-section strong {
        font-size: 10px !important;
        font-weight: bold !important;
        color: #dc143c !important;
    }
    
    .cert-img {
        max-height: 35px !important;
        width: auto !important;
    }
    
    /* ========== CÀI ĐẶT MÀU VÀ IN ========== */
    .table tfoot td[style*="color: #0066cc"] {
        color: #0066cc !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .table tfoot td[style*="color: #dc143c"] {
        color: #dc143c !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Đảm bảo in màu chính xác cho TFOOT */
    .table tfoot tr td {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    img {
        max-width: 100% !important;
        height: auto !important;
    }

    /* ========== MỞ RỘNG - ĐẢM BẢO FONT-WEIGHT ========== */
    table.table tbody td strong {
        font-weight: bold !important;
    }

    table.table tfoot td strong {
        font-weight: bold !important;
    }

    .well strong, .well-sm strong,
    .footer-section strong,
    .header-section strong {
        font-weight: bold !important;
    }

    /* ========== ĐỀU ĐẶN VĂN BẢN ========== */
    .header-section span[style*="color"],
    .footer-section span[style*="color"] {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ========== BUTTON VÀ CÁC YẾU TỐ KHÁC ========== */
    button, input, select, textarea {
        font-size: 10px !important;
    }
    
    * {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif !important;
    }

    html, body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif !important;
    }

    p, span, div, table, td, th, h1, h2, h3, h4, h5, h6 {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif !important;
    }

    .table, .table th, .table td {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif !important;
    }

    .header-section, .well, .well-sm, .footer-section {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif !important;
    }

    /* ========== FIX PHẦN QR CHUYỂN KHOẢN ========== */
    .footer-section div[style*="width: 100%; clear: both"] {
        margin: 8px 0 0 0 !important;
        padding: 0 !important;
        display: block !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table {
        width: 100% !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table tr {
        display: table-row !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table td {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        vertical-align: middle !important;
        box-sizing: border-box !important;
    }

    /* CỘT TRÁI - THÔNG TIN SẢN PHẨM */
    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 70%"] {
        width: 70% !important;
        padding: 0 12px 0 0 !important;
        vertical-align: top !important;
        margin: 0 !important;
    }

    /* CỘT PHẢI - MÃ QR */
    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] {
        width: 30% !important;
        padding: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        vertical-align: middle !important;
        display: table-cell !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] > div {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 140px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* THÔNG TIN SẢN PHẨM - TEXT */
    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 70%"] p {
        margin: 0 0 5px 0 !important;
        padding: 0 !important;
        font-size: 10px !important;
        line-height: 1.4 !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 70%"] > div {
        font-size: 10px !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* QR IMAGE */
    .footer-section div[style*="width: 100%; clear: both"] img[alt="QR Chuyển khoản"] {
        width: 100px !important;
        height: 100px !important;
        object-fit: contain !important;
        display: block !important;
        margin: 0 auto 8px auto !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
    }

    /* TEXT DƯỚI QR */
    .footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] p {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 9px !important;
        font-weight: bold !important;
        color: #0066cc !important;
        text-align: center !important;
        display: block !important;
        width: 100% !important;
        clear: both !important;
        line-height: 1.2 !important;
    }

    /* ĐẢM BẢO CỘT PHẢI KHÔNG BỊ WRAP */
    .footer-section div[style*="width: 100%; clear: both"] table {
        table-layout: fixed !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table tr {
        display: table-row !important;
    }

    .footer-section div[style*="width: 100%; clear: both"] table td {
        display: table-cell !important;
    }

    .footer-section > table {
        width: 100% !important;
        border: none !important;
        margin-top: 15px !important;
    }

    .footer-section > table td {
        border: none !important;
        text-align: center !important;
        vertical-align: top !important;
        padding: 0 !important;
    }
    /* ===================================
   FIX KÍCH THƯỚC QR CODE - TARGET CLASS qrimg
=================================== */
.qrimg,
img.qrimg,
.order_barcodes .qrimg,
.order_barcodes img.qrimg {
    width: 50px !important;
    height: 50px !important;
    max-width: 50px !important;
    max-height: 50px !important;
    min-width: 50px !important;
    min-height: 50px !important;
    object-fit: contain !important;
    float: none !important;
    display: block !important;
    margin: 0 auto !important;
}

/* Force tất cả ảnh trong order_barcodes */
.order_barcodes img,
.order_barcodes > div > img,
.order_barcodes div img {
    width: 50px !important;
    height: 50px !important;
    max-width: 50px !important;
    max-height: 50px !important;
    min-width: 50px !important;
    min-height: 50px !important;
    object-fit: contain !important;
}

/* CSS cho print - đảm bảo QR code hiển thị đúng khi in */
@media print {
    .qrimg,
    img.qrimg,
    .order_barcodes .qrimg {
        width: 50px !important;
        height: 50px !important;
        max-width: 50px !important;
        max-height: 50px !important;
        float: none !important;
        display: block !important;
    }
    
    .order_barcodes img {
        width: 50px !important;
        height: 50px !important;
    }
}
/* ===================================
   FIX CỘT GHI CHÚ TRONG TFOOT - ƯU TIÊN CAO
=================================== */
.table tfoot td[rowspan] {
    text-align: left !important;
    vertical-align: middle !important;
    padding: 8px !important;
    font-size: 9px !important;
    line-height: 1.3 !important;
    word-wrap: break-word !important;
    white-space: normal !important;
    overflow: visible !important;
}

.table tfoot td[rowspan] strong {
    font-size: 9px !important;
    line-height: 1.3 !important;
    display: block !important;
    width: 100% !important;
}
</style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) { ?>
            <table class="header-section" style="width: 100%; border: none; margin-bottom: 10px;">
                <tr>
                    <td style="width: 100px; vertical-align: middle; border: none;">
                        <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                            alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>"
                            class="company-logo">
                    </td>
                    <td style="vertical-align: middle; text-align: center; border: none;">
                        <h2>CÔNG TY TNHH BARUN VIỆT NAM - VĂN PHÒNG ĐẠI DIỆN MIỀN TÂY</h2>
                        <p style="font-weight: bold !important;">
                            <span style="color: #44546A;">Địa chỉ:</span> 165 đường D4, KDC Hồng Loan, P Hưng Thạnh, Q Cái Răng, TP Cần Thơ<br>
                            <span style="color: #44546A;">Điện thoại:</span> 0763.882.285 (Văn phòng) - 0917.225.931 (Ms Hằng)
                        </p>
                    </td>
                </tr>
            </table>
            <?php } ?>
            
            <h1>BẢNG BÁO GIÁ KIÊM XÁC NHẬN ĐẶT HÀNG</h1>

            <div class="well well-sm">
    <table style="width: 100%; border: none;">
        <tr>
            <!-- ✅ CỘT TRÁI: THÔNG TIN ĐỠN HÀNG - 20% -->
            <td style="width: 30%; vertical-align: top; border: none;">
                <p>
                    <strong><?= lang("ref"); ?>:</strong> <span class="data-text"><?= $inv->reference_no; ?></span><br>
                    <strong>Ngày nhận đơn:</strong> <span class="data-text"><?= $this->sma->hrsd($inv->date); ?></span><br>
                    <strong>Ngày giao chành:</strong> <span class="data-text"><?= !empty($inv->shipping_date) ? $this->sma->hrsd($inv->shipping_date) : '-'; ?></span><br>
                    <strong>Ngày khách nhận dự kiến:</strong> <span class="data-text"><?= !empty($inv->expected_delivery_date) ? $this->sma->hrsd($inv->expected_delivery_date) : '-'; ?></span><br>
                    <strong>Ngày lắp đặt dự kiến:</strong> <span class="data-text"><?= !empty($inv->expected_installation_date) ? $this->sma->hrsd($inv->expected_installation_date) : '-'; ?></span><br>
                    <strong><?= lang("status"); ?>:</strong> <span class="data-text"><?= $inv->status; ?></span>
                </p>
            </td>
            
            <!-- ✅ CỘT PHẢI: CHỨNG CHỈ + QR - 80% -->
            <td style="width: 70%; text-align: center; vertical-align: middle; border: none;">
                <div style="display: flex; align-items: flex-start; justify-content: center; gap: 8px; flex-wrap: nowrap; width: 100%;">
                    <div style="text-align: center;">
                        <img src="<?= base_url('assets/uploads/ct1.png'); ?>" alt="ISO" style="width: 50px !important; height: 50px !important; max-width: 50px !important; object-fit: contain; display: block; margin: 0 auto;">
                        <p style="margin: 2px 0 0 0; font-size: 9px !important; font-weight: bold;">Chứng nhận ISO</p>
                    </div>
                    <div style="text-align: center;">
                        <img src="<?= base_url('assets/uploads/ct2.png'); ?>" alt="Vietnam" style="width: 50px !important; height: 50px !important; max-width: 50px !important; object-fit: contain; display: block; margin: 0 auto;">
                        <p style="margin: 2px 0 0 0; font-size: 9px !important; font-weight: bold;">Chứng nhận kiểm định chất lượng</p>
                    </div>
                    <div style="text-align: center;">
                        <img src="<?= base_url('assets/uploads/ct3.png'); ?>" alt="Brand" style="width: 50px !important; height: 50px !important; max-width: 50px !important; object-fit: contain; display: block; margin: 0 auto;">
                        <p style="margin: 2px 0 0 0; font-size: 9px !important; font-weight: bold;">Chứng nhận thương hiệu số 1 Việt Nam</p>
                    </div>
                    <div style="text-align: center;">
                        <img src="<?= base_url('assets/uploads/ct4.png'); ?>" alt="Door" style="width: 50px !important; height: 50px !important; max-width: 50px !important; object-fit: contain; display: block; margin: 0 auto;">
                        <p style="margin: 2px 0 0 0; font-size: 9px !important; font-weight: bold;">Chứng nhận thương hiệu tiêu biểu Châu Á</p>
                    </div>
                    <div style="text-align: center; min-width: 60px;">
                        <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 2, 'style="width: 50px !important; height: 50px !important; max-width: 50px !important; display: block; margin: 0 auto;"'); ?>
                        <p style="margin: 2px 0 0 0; font-size: 9px !important; font-weight: bold; white-space: nowrap;">Xem online</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

            <div class="well well-sm">
                <p>
                    <strong style="font-size: 10px;">Khách hàng: </strong>
                    <span style="font-size: 10px;"><?= $customer->company ? $customer->company : $customer->name; ?></span>
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
                        <strong>Thông tin chành xe:</strong> <span class="data-text"><?= $this->sma->decode_html($inv->shipping_info); ?></span>
                    <?php endif; ?>
                    <br>
<?php if (!empty($inv->construction_address)): ?>
    <strong>Địa chỉ công trình:</strong> 
    <?= str_replace(['<p>', '</p>'], '', $this->sma->decode_html($inv->construction_address)); ?>
<?php endif; ?>
                </p>
            </div>

            <!-- BẢNG SẢN PHẨM -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="quote-stt-col"><strong>STT</strong></th>
                        <th class="quote-model-col"><strong>Mẫu - Mã</strong></th>
                        <th class="quote-color-col"><strong>Màu</strong></th>
                        <th class="quote-lock-col"><strong>Khóa</strong></th>
                        <?php
                        if (!empty($custom_columns)) {
                            foreach ($custom_columns as $col) {
                                $column_class = (strpos($col->column_name, 'Hướng') !== false || strpos($col->column_name, 'huong') !== false) ? ' quote-direction-col' : '';
                                echo '<th class="' . $column_class . '"><strong>' . $col->column_name . '</strong></th>';
                            }
                        }
                        ?>
                        <th class="quote-note-col"><strong>Ghi chú</strong></th>
                        <th class="quote-qty-col"><strong><?= lang("quantity"); ?></strong></th>
                        <th class="quote-price-col"><strong><?= lang("unit_price"); ?></strong></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th style="width:100px;"><strong>' . lang("tax") . '</strong></th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th style="width:100px;"><strong>' . lang("discount") . '</strong></th>';
                        }
                        ?>
                        <th class="quote-total-col"><strong><?= lang("subtotal"); ?></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $r = 1;
                    $products_with_variants = [];
                    $products_without_variants = [];

                    foreach ($rows as $group) {
                        $has_variants = ($group['color'] !== null || $group['lock'] !== null);
                        if ($has_variants) {
                            $products_with_variants[] = $group;
                        } else {
                            $products_without_variants[] = $group;
                        }
                    }

                    foreach ($products_with_variants as $group):
                        $main_row = $group['main'];
                        $color = $group['color'];
                        $lock = $group['lock'];
                    ?>
                    <tr>
                        <td class="product-cell"><strong><?= $r; ?></strong></td>
                        <td class="product-cell">
                            <strong><?= $main_row->product_code; ?></strong>
                            <?= $main_row->details ? '<br><small><strong>' . $main_row->details . '</strong></small>' : ''; ?>
                            <br>
                            <?php
                            $image = $group['image'] && $group['image'] != 'no_image.png'
                                ? $group['image'] : 'no_image.png';
                            ?>
                            <div class="product-img-container">
                                <img src="<?= base_url('assets/uploads/' . $image); ?>" class="product-img">
                            </div>
                        </td>
                        <td class="product-cell">
                            <?php if ($color): ?>
                                <strong><?= $color->product_name; ?></strong><br>
                                <?php
                                $color_image = $color->image && $color->image != 'no_image.png'
                                    ? $color->image : 'no_image.png';
                                ?>
                                <div class="product-img-container">
                                    <img src="<?= base_url('assets/uploads/' . $color_image); ?>" class="variant-img">
                                </div>
                            <?php else: ?>
                                <strong>-</strong>
                            <?php endif; ?>
                        </td>
                        <td class="product-cell">
                            <?php if ($lock): ?>
                                <strong><?= $lock->product_name; ?></strong><br>
                                <?php
                                $lock_image = $lock->image && $lock->image != 'no_image.png'
                                    ? $lock->image : 'no_image.png';
                                ?>
                                <div class="product-img-container">
                                    <img src="<?= base_url('assets/uploads/' . $lock_image); ?>" class="variant-img">
                                </div>
                            <?php else: ?>
                                <strong>-</strong>
                            <?php endif; ?>
                        </td>
                        <?php
                        if (!empty($custom_columns)) {
                            foreach ($custom_columns as $col) {
                                $value = isset($main_row->custom_fields[$col->column_name])
                                    ? $main_row->custom_fields[$col->column_name] : '';
                                $column_class = (strpos($col->column_name, 'Hướng') !== false || strpos($col->column_name, 'huong') !== false) ? ' quote-direction-cell' : '';
                                echo '<td class="product-cell' . $column_class . '"><strong>' . $this->sma->decode_html($value) . '</strong></td>';
                            }
                        }
                        ?>
                        <td class="product-cell quote-note-cell">
                            <strong><?= !empty($main_row->notes) ? $this->sma->decode_html($main_row->notes) : ''; ?></strong>
                        </td>
                        <td class="product-cell quote-qty-cell">
                            <strong><?= $this->sma->formatQuantity($main_row->unit_quantity); ?></strong>
                        </td>
                        <td class="product-cell quote-price-cell">
                            <strong><?php
                            $total_unit_price = $main_row->unit_price;
                            if ($color) {
                                $total_unit_price += $color->unit_price;
                            }
                            echo $this->sma->formatMoney($total_unit_price);
                            ?></strong>
                        </td>
                        <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                            <td class="product-cell">
                                <strong><?php
                                $total_tax = $main_row->item_tax;
                                if ($color) $total_tax += $color->item_tax;
                                echo $this->sma->formatMoney($total_tax);
                                ?></strong>
                            </td>
                        <?php endif; ?>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                            <td class="product-cell">
                                <strong><?php
                                $total_discount = $main_row->item_discount;
                                if ($color) $total_discount += $color->item_discount;
                                echo $this->sma->formatMoney($total_discount);
                                ?></strong>
                            </td>
                        <?php endif; ?>
                        <td class="product-cell quote-total-cell">
                            <strong><?php
                            $total_subtotal = $main_row->subtotal;
                            if ($color) {
                                $total_subtotal += $color->subtotal;
                            }
                            echo $this->sma->formatMoney($total_subtotal);
                            ?></strong>
                        </td>
                    </tr>
                    <?php
                        $r++;
                    endforeach;

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
                            <td class="product-cell"><strong><?= $r; ?></strong></td>
                            <?php
                            $lock_merge_cols = 3;
                            if (!empty($custom_columns)) {
                                $lock_merge_cols += count($custom_columns);
                            }
                            ?>
                            <td colspan="<?= $lock_merge_cols; ?>" style="text-align:right !important;">
                                <strong>Khóa</strong>
                            </td>
                            <td class="product-cell quote-note-cell"></td>
                            <td class="product-cell quote-qty-cell">
                                <strong><?= $this->sma->formatQuantity($total_lock_qty); ?></strong>
                            </td>
                            <td class="product-cell quote-price-cell">
                                <strong><?= $this->sma->formatMoney($total_lock_price / $total_lock_qty); ?></strong>
                            </td>
                            <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                <td class="product-cell"><strong>-</strong></td>
                            <?php endif; ?>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                <td class="product-cell"><strong>-</strong></td>
                            <?php endif; ?>
                            <td class="product-cell quote-total-cell">
                                <strong><?= $this->sma->formatMoney($total_lock_price); ?></strong>
                            </td>
                        </tr>
                    <?php
                        $r++;
                    endif;

                    foreach ($products_without_variants as $group):
                        $main_row = $group['main'];
                        $merge_cols = 3;
                        if (!empty($custom_columns)) {
                            $merge_cols += count($custom_columns);
                        }
                        $merge_cols += 1;
                    ?>
                        <tr>
                            <td class="product-cell"><strong><?= $r; ?></strong></td>
                            <td colspan="<?= $merge_cols - 1; ?>" style="text-align:right !important;">
                                <strong><?= $main_row->product_name; ?></strong>
                                <?= $main_row->details ? '<br><small><strong>' . $main_row->details . '</strong></small>' : ''; ?>
                            </td>
                            <td class="product-cell quote-note-cell">
                                <strong><?= !empty($main_row->notes) ? $this->sma->decode_html($main_row->notes) : ''; ?></strong>
                            </td>
                            <td class="product-cell quote-qty-cell">
                                <strong><?= $this->sma->formatQuantity($main_row->unit_quantity); ?></strong>
                            </td>
                            <td class="product-cell quote-price-cell">
                                <strong><?= $this->sma->formatMoney($main_row->unit_price); ?></strong>
                            </td>
                            <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                <td class="product-cell">
                                    <strong><?= $this->sma->formatMoney($main_row->item_tax); ?></strong>
                                </td>
                            <?php endif; ?>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                <td class="product-cell">
                                    <strong><?= $this->sma->formatMoney($main_row->item_discount); ?></strong>
                                </td>
                            <?php endif; ?>
                            <td class="product-cell quote-total-cell">
                                <strong><?= $this->sma->formatMoney($main_row->subtotal); ?></strong>
                            </td>
                        </tr>
                    <?php
                        $r++;
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <?php
                    $col = 7;
                    if (!empty($custom_columns)) {
                        $col += count($custom_columns);
                    }
                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        $col++;
                    }
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        $col++;
                    }
                    $col++;

                    if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                        $tcol = $col - 3;
                    } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                        $tcol = $col - 2;
                    } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                        $tcol = $col - 2;
                    } else {
                        $tcol = $col - 1;
                    }
                    
                    // ✅ TÍNH SỐ DÒNG FOOTER DỰA VÀO CÓ DEPOSIT HAY KHÔNG
                    $footer_rows = 1; // Mặc định chỉ có 1 dòng (Tổng cộng)
                    if ($inv->deposit_amount && $inv->deposit_amount > 0) {
                        $footer_rows = 3; // Có 3 dòng: Tổng cộng + Tiền đặt cọc + Còn lại
                    }
                    ?>
                    
                    <!-- ✅ TỔNG CỘNG -->
                    <tr style="background-color: #B4C6E7 !important;">
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right !important; font-weight:bold; color: #0066cc !important; background-color: #B4C6E7 !important;">
                            TỔNG CỘNG 
                        </td>
                        <td style="text-align:center; font-weight:bold; color: #dc143c !important; background-color: #B4C6E7 !important;">
                            <?= $this->sma->formatMoney($inv->grand_total); ?>
                        </td>
                        <!-- ✅ ROWSPAN ĐỘNG - CHO TẤT CẢ TRƯỜNG HỢP -->
                        <td rowspan="<?= $footer_rows; ?>" style="background-color: #B4C6E7 !important; vertical-align: middle !important; text-align: center !important; padding: 8px !important;">
                            <?php if (!empty($inv->note)): ?>
                                <strong style="font-size: 9px !important;"><?= nl2br($this->sma->decode_html($inv->note)); ?></strong>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- ✅ TIỀN ĐẶT CỌC - CHỈ HIỂN THỊ NẾU CÓ DEPOSIT -->
                    <?php if ($inv->deposit_amount && $inv->deposit_amount > 0) { ?>
                    <tr style="background-color: #B4C6E7 !important;">
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right !important; font-weight:bold; color: #0066cc !important; background-color: #B4C6E7 !important;">
                            TIỀN ĐẶT CỌC
                        </td>
                        <td style="text-align:center; font-weight:bold; color: #dc143c !important; background-color: #B4C6E7 !important;">
                            <?= $this->sma->formatMoney($inv->deposit_amount); ?>
                        </td>
                        <!-- ✅ KHÔNG CÓ <td> GHI CHÚ VÌ ĐÃ DÙNG ROWSPAN Ở DÒNG TRÊN -->
                    </tr>

                    <!-- ✅ CÒN LẠI -->
                    <tr style="background-color: #B4C6E7 !important;">
                        <td colspan="<?= $tcol - 1; ?>" style="text-align:right !important; font-weight:bold; color: #0066cc !important; background-color: #B4C6E7 !important;">
                            CÒN LẠI 
                        </td>
                        <td style="text-align:center; font-weight:bold; color: #dc143c !important; background-color: #B4C6E7 !important;">
                            <?= $this->sma->formatMoney($inv->grand_total - $inv->deposit_amount); ?>
                        </td>
                        <!-- ✅ KHÔNG CÓ <td> GHI CHÚ VÌ ĐÃ DÙNG ROWSPAN Ở DÒNG TRÊN -->
                    </tr>
                    <?php } ?>
                </tfoot>
            </table>

            

            <div class="footer-section" style="border-top: 2px solid #ddd; margin-top: 5px !important;">
    <p style="font-weight: bold !important; ">
        <span style="color: #dc143c;">THÔNG TIN CHUYỂN TIỀN:</span>
        BÙI THỊ HẰNG - STK: 7420179458 tại Ngân hàng TMCP Đầu tư và Phát triển Việt Nam (BIDV Bank)
    </p>
<?php if (true) { ?>
<div style="width: 100%; clear: both; margin-top: 3px !important;">
    <table style="width: 100%; border: none; margin: 0; border-collapse: collapse; table-layout: fixed;">
        <tr>
            <!-- CỘT TRÁI: THÔNG TIN SẢN PHẨM -->
            <td style="width: 60%; vertical-align: top; border: none; box-sizing: border-box;">
                <p style="font-weight: bold; margin: 0 0 3px 0;">
                    <strong>THÔNG TIN SẢN PHẨM: </strong>
                </p>
                <div style="font-size: 10px; line-height: 1.3; margin-bottom: 3px;">
                    * Cánh cửa ABS trơn phẳng, dày 40mm (+-2mm).<br>
                    * Bề mặt cánh hoàn thiện là 2 tấm nhựa ABS (loại nhựa đặc biệt có khả năng chịu va đập)<br>
                    * Vật liệu cánh: Xung quanh bao bọc thanh nhựa PVC Bar chống nước, thanh LVL và honeycomb.<br>
                    * Khung cửa bằng nhựa rộng 110mm, thép gia cường dày 0.7-1mm<br>
                    * Nẹp khung bao di động 02 mặt: Lắp đặt linh động cho cửa tường dày 110-160mm<br>
                    * Màu cửa hoàn thiện: theo màu tiêu chuẩn của NSX
                </div>

                <!-- BẢO HÀNH SẢN PHẨM -->
                <p style="margin: 0 0 5px 0; line-height: 1.3; font-size: 10px; margin-top: 5px !important;">
                    <strong style="color: #dc143c;">BẢO HÀNH SẢN PHẨM CỬA NHỰA ABS HÀN QUỐC BARUN VIỆT NAM</strong><br>
                    <em style="font-size: 9px;">02 năm kể từ ngày mua đối với cánh, khung, nẹp cửa</em><br>
                    <em style="font-size: 9px;">01 năm kể từ ngày mua đối với phụ kiện đi kèm</em>
                </p>
            </td>

            <!-- CỘT PHẢI: MÃ QR - CĂNG GIỮA -->
            <td style="width: 40%; text-align: center; vertical-align: middle; border: none; padding: 0; box-sizing: border-box; display: flex; align-items: center; justify-content: center; min-height: 140px;">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
                    <img src="<?= base_url('assets/uploads/qr_chuyen_khoan.jpg'); ?>"
                        alt="QR Chuyển khoản"
                        style="width: 100px !important; height: 100px !important; object-fit: contain; display: block; margin: 0 auto 8px auto;">
                    <p style="margin: 0; font-size: 10px; font-weight: bold !important; color: #0066cc; text-align: center; width: 100%;">
                        QUÉT MÃ CHUYỂN KHOẢN
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>
<?php } ?>

                <table style="width: 100%; margin-top: 20px; border: none;">
                    <tr>
                        <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                            <p style="margin: 0 0 60px 0; color: #000 !important;">
                                <strong style="color: #000 !important;">CÔNG TY TNHH BARUN VIỆT NAM</strong>
                            </p>
                        </td>
                        <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                            <p style="margin: 0; color: #000 !important;">
                                <strong style="color: #000 !important;">NGƯỜI LẬP PHIẾU</strong>
                            </p>
                            <br><br><br>
                            <p style="margin: 0;">
                                <strong style="color: #000 !important;"><?= $biller->name; ?></strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- ✅ SỬ DỤNG html-to-image THAY VÌ html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
<script>
window.onload = function() {
    Promise.all([waitForFonts(), waitForImages()]).then(function() {
        setTimeout(renderQuoteAsImage, 300);
    });
};

function waitForFonts() {
    return document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
}

function waitForImages() {
    var images = Array.prototype.slice.call(document.images || []);
    return Promise.all(images.map(function(img) {
        if (img.complete && img.naturalWidth !== 0) {
            return Promise.resolve();
        }
        return new Promise(function(resolve) {
            img.onload = resolve;
            img.onerror = resolve;
        });
    }));
}

function renderQuoteAsImage() {
    var loading = document.createElement('div');
    loading.id = 'loading';
    loading.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:99999; display:flex; align-items:center; justify-content:center;';
    loading.innerHTML = '<div style="background:white; padding:40px; border-radius:15px; text-align:center; max-width:400px;"><div style="font-size:50px; margin-bottom:20px;">📸</div><h2 style="color:#0066cc; margin:0 0 15px 0;">Đang tạo ảnh báo giá</h2><p style="color:#666; margin:0;">Đang lấy đầy đủ nội dung như bản PDF...</p><div style="margin-top:20px; width:100%; height:4px; background:#eee; border-radius:2px; overflow:hidden;"><div id="progress" style="width:0%; height:100%; background:#0066cc; transition:width 0.3s;"></div></div></div>';
    document.body.appendChild(loading);
    
    var progress = 0;
    var progressBar = setInterval(function() {
        progress += Math.random() * 30;
        if (progress > 90) progress = 90;
        document.getElementById('progress').style.width = progress + '%';
    }, 200);
    
    setTimeout(function() {
        var element = document.getElementById('wrap');
        
        var rect = element.getBoundingClientRect();
        var actualWidth = Math.ceil(Math.max(element.scrollWidth, element.offsetWidth, rect.width));
        var actualHeight = Math.ceil(Math.max(element.scrollHeight, element.offsetHeight, rect.height));
        var maxCanvasPixels = 24000000;
        var pixelRatio = 2;
        if ((actualWidth * actualHeight * pixelRatio * pixelRatio) > maxCanvasPixels) {
            pixelRatio = Math.max(1, Math.sqrt(maxCanvasPixels / (actualWidth * actualHeight)));
        }

        document.documentElement.style.width = actualWidth + 'px';
        document.documentElement.style.minHeight = actualHeight + 'px';
        document.body.style.width = actualWidth + 'px';
        document.body.style.minHeight = actualHeight + 'px';
        document.body.style.overflow = 'visible';
        
        htmlToImage.toJpeg(element, {
            quality: 0.95,
            pixelRatio: pixelRatio,
            backgroundColor: '#ffffff',
            cacheBust: true,
            width: actualWidth,
            height: actualHeight,
            canvasWidth: Math.ceil(actualWidth * pixelRatio),
            canvasHeight: Math.ceil(actualHeight * pixelRatio),
            windowWidth: actualWidth,
            windowHeight: actualHeight,
            style: {
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Helvetica Neue", Arial, sans-serif',
                margin: '0',
                padding: '0',
                width: actualWidth + 'px',
                height: actualHeight + 'px',
                overflow: 'visible'
            }
        })
        .then(function(dataUrl) {
            clearInterval(progressBar);
            document.getElementById('progress').style.width = '100%';
            
            var filename = 'BaoGia_<?= $inv->reference_no ?>.jpeg';
            
            var link = document.createElement('a');
            link.download = filename;
            link.href = dataUrl;
            link.click();
            
            setTimeout(function() {
                document.getElementById('loading').innerHTML = '<div style="background:white; padding:40px; border-radius:15px; text-align:center; max-width:400px;"><div style="font-size:60px; margin-bottom:15px;">✅</div><h2 style="color:#28a745; margin:0 0 10px 0;">Tạo ảnh thành công!</h2><p style="color:#666; margin:0 0 20px 0;">File <strong>' + filename + '</strong> đã được tải xuống</p><button onclick="window.close();" style="padding:12px 30px; background:#0066cc; color:white; border:none; border-radius:5px; font-size:16px; cursor:pointer; font-weight:bold;">Đóng cửa sổ</button></div>';
            }, 300);
            
            setTimeout(function() {
                window.close();
            }, 3000);
        })
        .catch(function(error) {
            clearInterval(progressBar);
            console.error('Lỗi:', error);
            document.getElementById('loading').innerHTML = '<div style="background:white; padding:40px; border-radius:15px; text-align:center; max-width:400px;"><div style="font-size:60px; margin-bottom:15px;">❌</div><h2 style="color:#dc3545; margin:0 0 10px 0;">Lỗi tạo ảnh!</h2><p style="color:#666; margin:0 0 20px 0;">Vui lòng thử lại hoặc liên hệ hỗ trợ</p><button onclick="window.close();" style="padding:12px 30px; background:#dc3545; color:white; border:none; border-radius:5px; font-size:16px; cursor:pointer; font-weight:bold;">Đóng</button></div>';
        });
    }, 1000);
}
</script>
</body>
</html>
