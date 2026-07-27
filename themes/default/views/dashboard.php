<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
function row_status($x)
{
    if ($x == null) {
        return '';
    } elseif ($x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' . lang($x) . '</span></div>';
    } elseif ($x == 'completed' || $x == 'paid' || $x == 'sent' || $x == 'received') {
        return '<div class="text-center"><span class="label label-success">' . lang($x) . '</span></div>';
    } elseif ($x == 'partial' || $x == 'transferring') {
        return '<div class="text-center"><span class="label label-info">' . lang($x) . '</span></div>';
    } elseif ($x == 'due') {
        return '<div class="text-center"><span class="label label-danger">' . lang($x) . '</span></div>';
    } else {
        return '<div class="text-center"><span class="label label-default">' . lang($x) . '</span></div>';
    }
}

$summary = isset($dashboard_summary) && $dashboard_summary ? $dashboard_summary : (object) array();
$sales_today_total = isset($summary->sales_today_total) ? $summary->sales_today_total : 0;
$sales_month_total = isset($summary->sales_month_total) ? $summary->sales_month_total : 0;
$sales_month_paid = isset($summary->sales_month_paid) ? $summary->sales_month_paid : 0;
$sales_month_due = isset($summary->sales_month_due) ? $summary->sales_month_due : 0;
$quotes_month_total = isset($summary->quotes_month_total) ? $summary->quotes_month_total : 0;
$purchases_month_total = isset($summary->purchases_month_total) ? $summary->purchases_month_total : 0;
$collection_rate = $sales_month_total > 0 ? round(($sales_month_paid / $sales_month_total) * 100) : 0;
$sales_month_count = isset($summary->sales_month_count) ? (int) $summary->sales_month_count : 0;
$quotes_month_count = isset($summary->quotes_month_count) ? (int) $summary->quotes_month_count : 0;
$quotes_completed_count = isset($summary->quotes_completed_count) ? (int) $summary->quotes_completed_count : 0;
$quote_conversion_rate = $quotes_month_count > 0 ? round(($quotes_completed_count / $quotes_month_count) * 100) : 0;
$average_sale_value = $sales_month_count > 0 ? ($sales_month_total / $sales_month_count) : 0;
$can_products = $Owner || $Admin || !empty($GP['products-index']);
$can_sales = $Owner || $Admin || !empty($GP['sales-index']);
$can_quotes = $Owner || $Admin || !empty($GP['quotes-index']);
$can_purchases = $Owner || $Admin || !empty($GP['purchases-index']);
$can_transfers = $Owner || $Admin || !empty($GP['transfers-index']);
$can_customers = $Owner || $Admin || !empty($GP['customers-index']);
$can_suppliers = $Owner || $Admin || !empty($GP['suppliers-index']);
?>

<style>
    .dashboard-modern {
        margin-bottom: 20px;
    }
    .dashboard-hero {
        background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 52%, #2563eb 100%);
        border-radius: 22px;
        color: #fff;
        padding: 26px 28px;
        box-shadow: 0 18px 45px rgba(15, 118, 110, .22);
        overflow: hidden;
        position: relative;
    }
    .dashboard-hero:after {
        background: rgba(255, 255, 255, .12);
        border-radius: 50%;
        content: "";
        height: 260px;
        position: absolute;
        right: -70px;
        top: -100px;
        width: 260px;
    }
    .dashboard-hero h1 {
        color: #fff;
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 8px;
    }
    .dashboard-hero p {
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
        margin: 0;
    }
    .dashboard-hero-actions {
        margin-top: 18px;
    }
    .dashboard-hero-actions .btn {
        border: 0;
        border-radius: 999px;
        font-weight: 700;
        margin: 0 8px 8px 0;
        padding: 10px 16px;
    }
    .metric-card, .modern-panel, .modern-action {
        background: #fff;
        border: 1px solid #e8eef5;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .06);
    }
    .metric-card {
        margin-top: 16px;
        min-height: 116px;
        padding: 16px;
        position: relative;
    }
    .metric-icon {
        align-items: center;
        border-radius: 12px;
        color: #fff;
        display: flex;
        font-size: 15px;
        height: 34px;
        justify-content: center;
        position: absolute;
        right: 14px;
        top: 14px;
        width: 34px;
    }
    .metric-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        margin-bottom: 10px;
        text-transform: uppercase;
    }
    .metric-value {
        color: #0f172a;
        font-size: 19px;
        font-weight: 900;
        line-height: 1.1;
        padding-right: 38px;
    }
    .metric-sub {
        color: #64748b;
        font-size: 12px;
        margin-top: 10px;
    }
    .bg-teal { background: linear-gradient(135deg, #14b8a6, #0f766e); }
    .bg-blue { background: linear-gradient(135deg, #38bdf8, #2563eb); }
    .bg-orange { background: linear-gradient(135deg, #fb923c, #ea580c); }
    .bg-red { background: linear-gradient(135deg, #fb7185, #e11d48); }
    .modern-panel {
        margin-top: 16px;
        padding: 18px;
    }
    .modern-panel-title {
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
        margin-bottom: 14px;
    }
    .modern-actions-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
    .modern-action {
        color: #0f172a;
        display: block;
        font-weight: 800;
        padding: 14px;
        text-decoration: none;
        transition: all .18s ease;
    }
    .modern-action:hover {
        color: #2563eb;
        text-decoration: none;
        transform: translateY(-2px);
    }
    .modern-action i {
        color: #2563eb;
        font-size: 17px;
        margin-right: 8px;
        vertical-align: middle;
    }
    .insight-tabs {
        border-bottom: 1px solid #e8eef5;
        margin-bottom: 14px;
    }
    .insight-tabs > li > a {
        border: 0 !important;
        border-radius: 999px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        margin: 0 8px 10px 0;
        padding: 9px 14px;
    }
    .insight-tabs > li.active > a,
    .insight-tabs > li.active > a:hover,
    .insight-tabs > li > a:hover {
        background: #eef6ff !important;
        color: #0369a1 !important;
    }
    .insight-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    .insight-item {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 14px;
    }
    .insight-label {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        margin-bottom: 8px;
    }
    .insight-value {
        color: #0f172a;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.2;
    }
    .insight-note {
        color: #64748b;
        font-size: 12px;
        margin-top: 7px;
    }
    .mini-data-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .mini-data-list li {
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        gap: 10px;
        justify-content: space-between;
        padding: 10px 0;
    }
    .mini-data-list li:last-child {
        border-bottom: 0;
    }
    .mini-data-title {
        color: #0f172a;
        font-weight: 800;
    }
    .mini-data-meta {
        color: #64748b;
        font-size: 12px;
        margin-top: 2px;
    }
    .mini-data-amount {
        color: #0f172a;
        font-weight: 900;
        white-space: nowrap;
    }
    .activity-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .activity-list li {
        border-bottom: 1px solid #eef2f7;
        display: flex;
        gap: 12px;
        padding: 11px 0;
    }
    .activity-list li:last-child {
        border-bottom: 0;
    }
    .activity-dot {
        border-radius: 50%;
        flex: 0 0 10px;
        height: 10px;
        margin-top: 7px;
        width: 10px;
    }
    .activity-main {
        flex: 1;
        min-width: 0;
    }
    .activity-title {
        color: #0f172a;
        font-weight: 800;
    }
    .activity-meta {
        color: #64748b;
        font-size: 12px;
        margin-top: 3px;
    }
    @media (max-width: 767px) {
        .dashboard-hero {
            padding: 22px 18px;
        }
        .dashboard-hero h1 {
            font-size: 23px;
        }
    }
</style>

<div class="dashboard-modern">
    <div class="dashboard-hero">
        <div class="row">
            <div class="col-sm-8">
                <h1>Chào <?= $this->session->userdata('first_name') ? $this->session->userdata('first_name') : 'Barun Door'; ?> 👋</h1>
                <p>Tổng quan nhanh hoạt động bán hàng, báo giá, thu tiền và tồn kho trong tháng <?= date('m/Y'); ?>.</p>
                <div class="dashboard-hero-actions">
                    <?php if ($can_quotes) { ?><a href="<?= site_url('quotes/add') ?>" class="btn btn-default"><i class="fa fa-plus"></i> Tạo báo giá</a><?php } ?>
                    <?php if ($can_sales) { ?><a href="<?= site_url('sales') ?>" class="btn btn-default"><i class="fa fa-shopping-cart"></i> Xem đơn hàng</a><?php } ?>
                    <?php if ($can_products) { ?><a href="<?= site_url('products') ?>" class="btn btn-default"><i class="fa fa-cubes"></i> Sản phẩm</a><?php } ?>
                </div>
            </div>
            <div class="col-sm-4 text-right hidden-xs">
                <div style="font-size:13px;color:rgba(255,255,255,.78);font-weight:700;">Hôm nay</div>
                <div style="font-size:34px;font-weight:900;"><?= date('d/m/Y'); ?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.78);">Cập nhật dữ liệu realtime từ hệ thống</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-teal"><i class="fa fa-line-chart"></i></div>
                <div class="metric-label">Doanh số hôm nay</div>
                <div class="metric-value"><?= $this->sma->formatMoney($sales_today_total); ?></div>
                <div class="metric-sub"><?= isset($summary->sales_today_count) ? (int) $summary->sales_today_count : 0; ?> đơn phát sinh</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-blue"><i class="fa fa-calendar-check-o"></i></div>
                <div class="metric-label">Doanh số tháng</div>
                <div class="metric-value"><?= $this->sma->formatMoney($sales_month_total); ?></div>
                <div class="metric-sub"><?= isset($summary->sales_month_count) ? (int) $summary->sales_month_count : 0; ?> đơn trong tháng</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-teal"><i class="fa fa-credit-card"></i></div>
                <div class="metric-label">Đã thu tháng</div>
                <div class="metric-value"><?= $this->sma->formatMoney($sales_month_paid); ?></div>
                <div class="metric-sub">Tỷ lệ thu <?= $collection_rate; ?>%</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-orange"><i class="fa fa-file-text-o"></i></div>
                <div class="metric-label">Báo giá tháng</div>
                <div class="metric-value"><?= $this->sma->formatMoney($quotes_month_total); ?></div>
                <div class="metric-sub"><?= isset($summary->quotes_month_count) ? (int) $summary->quotes_month_count : 0; ?> báo giá · <?= isset($summary->quotes_open_count) ? (int) $summary->quotes_open_count : 0; ?> đang mở</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-blue"><i class="fa fa-truck"></i></div>
                <div class="metric-label">Nhập hàng tháng</div>
                <div class="metric-value"><?= $this->sma->formatMoney($purchases_month_total); ?></div>
                <div class="metric-sub"><?= isset($summary->purchases_month_count) ? (int) $summary->purchases_month_count : 0; ?> phiếu nhập</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="metric-card">
                <div class="metric-icon bg-red"><i class="fa fa-warning"></i></div>
                <div class="metric-label">Tồn kho cần chú ý</div>
                <div class="metric-value"><?= isset($summary->low_stock_count) ? (int) $summary->low_stock_count : 0; ?></div>
                <div class="metric-sub"><?= isset($summary->products_count) ? (int) $summary->products_count : 0; ?> sản phẩm · <?= isset($summary->customers_count) ? (int) $summary->customers_count : 0; ?> khách hàng</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="modern-panel">
                <div class="modern-panel-title"><i class="fa fa-pie-chart"></i> Góc nhìn điều hành</div>
                <ul id="dashboardInsightTab" class="nav nav-tabs insight-tabs">
                    <li class="active"><a href="#insight-quotes" data-toggle="tab"><i class="fa fa-files-o"></i> Báo giá</a></li>
                    <li><a href="#insight-revenue" data-toggle="tab"><i class="fa fa-line-chart"></i> Doanh thu</a></li>
                    <li><a href="#insight-related" data-toggle="tab"><i class="fa fa-database"></i> Dữ liệu liên quan</a></li>
                </ul>
                <div class="tab-content" style="border:0;padding:0;">
                    <div id="insight-quotes" class="tab-pane fade in active">
                        <div class="insight-grid">
                            <div class="insight-item">
                                <div class="insight-label">Giá trị báo giá tháng</div>
                                <div class="insight-value"><?= $this->sma->formatMoney($quotes_month_total); ?></div>
                                <div class="insight-note"><?= $quotes_month_count; ?> báo giá phát sinh</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Báo giá đang theo</div>
                                <div class="insight-value"><?= isset($summary->quotes_open_count) ? (int) $summary->quotes_open_count : 0; ?></div>
                                <div class="insight-note">Pending/Sent cần chăm sóc</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Đã chốt trong tháng</div>
                                <div class="insight-value"><?= $quotes_completed_count; ?></div>
                                <div class="insight-note">Tỷ lệ chốt <?= $quote_conversion_rate; ?>%</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Trạng thái báo giá</div>
                                <div class="insight-value"><?= isset($summary->quotes_pending_count) ? (int) $summary->quotes_pending_count : 0; ?> / <?= isset($summary->quotes_sent_count) ? (int) $summary->quotes_sent_count : 0; ?></div>
                                <div class="insight-note">Chờ xử lý / đã gửi</div>
                            </div>
                        </div>
                    </div>
                    <div id="insight-revenue" class="tab-pane fade">
                        <div class="insight-grid">
                            <div class="insight-item">
                                <div class="insight-label">Doanh thu tháng</div>
                                <div class="insight-value"><?= $this->sma->formatMoney($sales_month_total); ?></div>
                                <div class="insight-note"><?= $sales_month_count; ?> đơn hàng</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Đã thu</div>
                                <div class="insight-value"><?= $this->sma->formatMoney($sales_month_paid); ?></div>
                                <div class="insight-note">Tỷ lệ thu <?= $collection_rate; ?>%</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Còn phải thu</div>
                                <div class="insight-value"><?= $this->sma->formatMoney($sales_month_due); ?></div>
                                <div class="insight-note"><?= isset($summary->sales_due_count) ? (int) $summary->sales_due_count : 0; ?> đơn còn công nợ</div>
                            </div>
                            <div class="insight-item">
                                <div class="insight-label">Giá trị đơn TB</div>
                                <div class="insight-value"><?= $this->sma->formatMoney($average_sale_value); ?></div>
                                <div class="insight-note">Theo doanh thu tháng</div>
                            </div>
                        </div>
                    </div>
                    <div id="insight-related" class="tab-pane fade">
                        <div class="row">
                            <div class="col-sm-6">
                                <ul class="mini-data-list">
                                    <?php if (!empty($quotes)) {
                                        foreach (array_slice($quotes, 0, 4) as $item) { ?>
                                            <li>
                                                <div>
                                                    <div class="mini-data-title">BG <?= $item->reference_no; ?></div>
                                                    <div class="mini-data-meta"><?= $item->customer; ?> · <?= $this->sma->hrld($item->date); ?></div>
                                                </div>
                                                <div class="mini-data-amount"><?= $this->sma->formatMoney($item->grand_total); ?></div>
                                            </li>
                                        <?php }
                                    } else { ?>
                                        <li><div class="mini-data-meta">Chưa có báo giá mới.</div></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul class="mini-data-list">
                                    <?php if (!empty($sales)) {
                                        foreach (array_slice($sales, 0, 4) as $item) { ?>
                                            <li>
                                                <div>
                                                    <div class="mini-data-title">Đơn <?= $item->reference_no; ?></div>
                                                    <div class="mini-data-meta"><?= $item->customer; ?> · <?= $this->sma->hrld($item->date); ?></div>
                                                </div>
                                                <div class="mini-data-amount"><?= $this->sma->formatMoney($item->grand_total); ?></div>
                                            </li>
                                        <?php }
                                    } else { ?>
                                        <li><div class="mini-data-meta">Chưa có đơn bán mới.</div></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="modern-panel">
                <div class="modern-panel-title"><i class="fa fa-bolt"></i> Tác vụ nhanh</div>
                <div class="modern-actions-grid">
                    <?php if ($can_products) { ?><a class="modern-action" href="<?= site_url('products') ?>"><i class="fa fa-barcode"></i> Sản phẩm</a><?php } ?>
                    <?php if ($can_sales) { ?><a class="modern-action" href="<?= site_url('sales') ?>"><i class="fa fa-shopping-cart"></i> Bán hàng</a><?php } ?>
                    <?php if ($can_quotes) { ?><a class="modern-action" href="<?= site_url('quotes') ?>"><i class="fa fa-files-o"></i> Báo giá</a><?php } ?>
                    <?php if ($can_purchases) { ?><a class="modern-action" href="<?= site_url('purchases') ?>"><i class="fa fa-cart-plus"></i> Nhập hàng</a><?php } ?>
                    <?php if ($can_transfers) { ?><a class="modern-action" href="<?= site_url('transfers') ?>"><i class="fa fa-refresh"></i> Chuyển kho</a><?php } ?>
                    <?php if ($can_customers) { ?><a class="modern-action" href="<?= site_url('customers') ?>"><i class="fa fa-users"></i> Khách hàng</a><?php } ?>
                    <?php if ($can_suppliers) { ?><a class="modern-action" href="<?= site_url('suppliers') ?>"><i class="fa fa-home"></i> Nhà cung cấp</a><?php } ?>
                    <a class="modern-action" href="<?= site_url('notifications') ?>"><i class="fa fa-bell"></i> Thông báo</a>
                    <?php if ($Owner) { ?><a class="modern-action" href="<?= site_url('system_settings') ?>"><i class="fa fa-cogs"></i> Cài đặt</a><?php } ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-panel">
                <div class="modern-panel-title"><i class="fa fa-clock-o"></i> Hoạt động mới</div>
                <ul class="activity-list">
                    <?php if (!empty($sales)) {
                        foreach (array_slice($sales, 0, 2) as $item) { ?>
                            <li>
                                <span class="activity-dot bg-teal"></span>
                                <div class="activity-main">
                                    <div class="activity-title">Đơn bán <?= $item->reference_no; ?></div>
                                    <div class="activity-meta"><?= $item->customer; ?> · <?= $this->sma->formatMoney($item->grand_total); ?></div>
                                </div>
                            </li>
                        <?php }
                    } ?>
                    <?php if (!empty($quotes)) {
                        foreach (array_slice($quotes, 0, 2) as $item) { ?>
                            <li>
                                <span class="activity-dot bg-orange"></span>
                                <div class="activity-main">
                                    <div class="activity-title">Báo giá <?= $item->reference_no; ?></div>
                                    <div class="activity-meta"><?= $item->customer; ?> · <?= $this->sma->formatMoney($item->grand_total); ?></div>
                                </div>
                            </li>
                        <?php }
                    } ?>
                    <?php if (empty($sales) && empty($quotes)) { ?>
                        <li>
                            <span class="activity-dot bg-blue"></span>
                            <div class="activity-main">
                                <div class="activity-title">Chưa có hoạt động mới</div>
                                <div class="activity-meta">Dữ liệu sẽ hiển thị khi có đơn hoặc báo giá phát sinh.</div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $Admin) { ?>
<div class="row dashboard-legacy-quick-links" style="display:none; margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa fa-th"></i><span class="break"></span><?= lang('quick_links') ?></h2>
            </div>
            <div class="box-content droadborlhson">
                <div class="col-md-2 col-xs-6">
                    <a class="bblue white quick-button small" href="<?= site_url('products') ?>">
                        <i class="fa fa-barcode"></i>

                        <p><?= lang('products') ?></p>
                    </a>
                </div>
                <div class="col-md-2 col-xs-6">
                    <a class="bdarkGreen white quick-button small" href="<?= site_url('sales') ?>">
                        <i class="fa fa-shopping-cart"></i>

                        <p><?= lang('sales') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="blightOrange white quick-button small" href="<?= site_url('quotes') ?>">
                        <i class="fa fa-files-o"></i>

                        <p><?= lang('quotes') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="bred white quick-button small" href="<?= site_url('purchases') ?>">
                        <i class="fa fa-cart-plus"></i>

                        <p><?= lang('purchases') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="bpink white quick-button small" href="<?= site_url('transfers') ?>">
                        <i class="fa fa-refresh"></i>

                        <p><?= lang('transfers') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="bgrey white quick-button small" href="<?= site_url('customers') ?>">
                        <i class="fa fa-users"></i>

                        <p><?= lang('customers') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="bgrey white quick-button small" href="<?= site_url('suppliers') ?>">
                        <i class="fa fa-home"></i>

                        <p><?= lang('suppliers') ?></p>
                    </a>
                </div>

                <div class="col-md-2 col-xs-6">
                    <a class="blightBlue white quick-button small" href="<?= site_url('notifications') ?>">
                        <i class="fa fa-bell"></i>

                        <p><?= lang('notifications') ?></p>
                        <!--<span class="notification green">4</span>-->
                    </a>
                </div>

                <?php if ($Owner) { ?>
                    <div class="col-md-2 col-xs-6">
                        <a class="bblue white quick-button small" href="<?= site_url('auth/users') ?>">
                            <i class="fa fa-user-plus"></i>
                            <p><?= lang('users') ?></p>
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <a class="bblue white quick-button small" href="<?= site_url('system_settings') ?>">
                            <i class="fa fa-cogs"></i>

                            <p><?= lang('settings') ?></p>
                        </a>
                    </div>
                <?php } ?>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="row dashboard-legacy-quick-links" style="display:none; margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa fa-th"></i><span class="break"></span><?= lang('quick_links') ?></h2>
            </div>
            <div class="box-content droadborlhson"> 
            <?php if ($GP['products-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bblue white quick-button small" href="<?= site_url('products') ?>">
                        <i class="fa fa-barcode"></i>
                        <p><?= lang('products') ?></p>
                    </a>
                </div>
            <?php } if ($GP['sales-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bdarkGreen white quick-button small" href="<?= site_url('sales') ?>">
                        <i class="fa fa-shopping-cart"></i>
                        <p><?= lang('sales') ?></p>
                    </a>
                </div>
            <?php } if ($GP['quotes-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="blightOrange white quick-button small" href="<?= site_url('quotes') ?>">
                        <i class="fa fa-files-o"></i>
                        <p><?= lang('quotes') ?></p>
                    </a>
                </div>
            <?php } if ($GP['purchases-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bred white quick-button small" href="<?= site_url('purchases') ?>">
                        <i class="fa fa-cart-plus"></i>
                        <p><?= lang('purchases') ?></p>
                    </a>
                </div>
            <?php } if ($GP['transfers-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bpink white quick-button small" href="<?= site_url('transfers') ?>">
                        <i class="fa fa-refresh"></i>
                        <p><?= lang('transfers') ?></p>
                    </a>
                </div>
            <?php } if ($GP['customers-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bgrey white quick-button small" href="<?= site_url('customers') ?>">
                        <i class="fa fa-home"></i>
                        <p><?= lang('customers') ?></p>
                    </a>
                </div>
            <?php } if ($GP['suppliers-index']) { ?>
                <div class="col-md-2 col-xs-6">
                    <a class="bgrey white quick-button small" href="<?= site_url('suppliers') ?>">
                        <i class="fa fa-home"></i>

                        <p><?= lang('suppliers') ?></p>
                    </a>
                </div>
            <?php } ?>
            <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<?php if (($Owner || $Admin) && $chatData) {
    foreach ($chatData as $month_sale) {
        $months[] = date('M-Y', strtotime($month_sale->month));
        $msales[] = $month_sale->sales;
        $mtax1[] = $month_sale->tax1;
        $mtax2[] = $month_sale->tax2;
        $mpurchases[] = $month_sale->purchases;
        $mtax3[] = $month_sale->ptax;
    }
    ?>
    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('overview_chart'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-md-12">
                    <p class="introtext"><?php echo lang('overview_chart_heading'); ?></p>

                    <div id="ov-chart" style="width:100%; height:450px;"></div>
                    <p class="text-center"><?= lang("chart_lable_toggle"); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-tasks"></i> <?= lang('latest_five') ?></h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">

                        <ul id="dbTab" class="nav nav-tabs">
                            <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                            <li class=""><a href="#sales"><?= lang('sales') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['quotes-index']) { ?>
                            <li class=""><a href="#quotes"><?= lang('quotes') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['purchases-index']) { ?>
                            <li class=""><a href="#purchases"><?= lang('purchases') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['transfers-index']) { ?>
                            <li class=""><a href="#transfers"><?= lang('transfers') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['customers-index']) { ?>
                            <li class=""><a href="#customers"><?= lang('customers') ?></a></li>
                            <?php } if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                            <li class=""><a href="#suppliers"><?= lang('suppliers') ?></a></li>
                            <?php } ?>
                        </ul>

                        <div class="tab-content">
                        <?php if ($Owner || $Admin || $GP['sales-index']) { ?>

                            <div id="sales" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="sales-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("customer"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("total"); ?></th>
                                                    <th><?= $this->lang->line("payment_status"); ?></th>
                                                    <th><?= $this->lang->line("paid"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($sales)) {
                                                    $r = 1;
                                                    foreach ($sales as $order) {
                                                        echo '<tr id="' . $order->id . '" class="' . ($order->pos ? "receipt_link" : "invoice_link") . '"><td>' . $r . '</td>
                                                            <td>' . $this->sma->hrld($order->date) . '</td>
                                                            <td>' . $order->reference_no . '</td>
                                                            <td>' . $order->customer . '</td>
                                                            <td>' . row_status($order->sale_status) . '</td>
                                                            <td class="text-right">' . $this->sma->formatMoney($order->grand_total) . '</td>
                                                            <td>' . row_status($order->payment_status) . '</td>
                                                            <td class="text-right">' . $this->sma->formatMoney($order->paid) . '</td>
                                                        </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="7"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['quotes-index']) { ?>

                            <div id="quotes" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="quotes-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("customer"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($quotes)) {
                                                    $r = 1;
                                                    foreach ($quotes as $quote) {
                                                        echo '<tr id="' . $quote->id . '" class="quote_link"><td>' . $r . '</td>
                                                        <td>' . $this->sma->hrld($quote->date) . '</td>
                                                        <td>' . $quote->reference_no . '</td>
                                                        <td>' . $quote->customer . '</td>
                                                        <td>' . row_status($quote->status) . '</td>
                                                        <td class="text-right">' . $this->sma->formatMoney($quote->grand_total) . '</td>
                                                    </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['purchases-index']) { ?>

                            <div id="purchases" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="purchases-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("supplier"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($purchases)) {
                                                    $r = 1;
                                                    foreach ($purchases as $purchase) {
                                                        echo '<tr id="' . $purchase->id . '" class="purchase_link"><td>' . $r . '</td>
                                                    <td>' . $this->sma->hrld($purchase->date) . '</td>
                                                    <td>' . $purchase->reference_no . '</td>
                                                    <td>' . $purchase->supplier . '</td>
                                                    <td>' . row_status($purchase->status) . '</td>
                                                    <td class="text-right">' . $this->sma->formatMoney($purchase->grand_total) . '</td>
                                                </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['transfers-index']) { ?>

                            <div id="transfers" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="transfers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("date"); ?></th>
                                                    <th><?= $this->lang->line("reference_no"); ?></th>
                                                    <th><?= $this->lang->line("from"); ?></th>
                                                    <th><?= $this->lang->line("to"); ?></th>
                                                    <th><?= $this->lang->line("status"); ?></th>
                                                    <th><?= $this->lang->line("amount"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($transfers)) {
                                                    $r = 1;
                                                    foreach ($transfers as $transfer) {
                                                        echo '<tr id="' . $transfer->id . '" class="transfer_link"><td>' . $r . '</td>
                                                <td>' . $this->sma->hrld($transfer->date) . '</td>
                                                <td>' . $transfer->transfer_no . '</td>
                                                <td>' . $transfer->from_warehouse_name . '</td>
                                                <td>' . $transfer->to_warehouse_name . '</td>
                                                <td>' . row_status($transfer->status) . '</td>
                                                <td class="text-right">' . $this->sma->formatMoney($transfer->grand_total) . '</td>
                                            </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="7"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['customers-index']) { ?>

                            <div id="customers" class="tab-pane fade in">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="customers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("company"); ?></th>
                                                    <th><?= $this->lang->line("name"); ?></th>
                                                    <th><?= $this->lang->line("email"); ?></th>
                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                    <th><?= $this->lang->line("address"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($customers)) {
                                                    $r = 1;
                                                    foreach ($customers as $customer) {
                                                        echo '<tr id="' . $customer->id . '" class="customer_link pointer"><td>' . $r . '</td>
                                            <td>' . $customer->company . '</td>
                                            <td>' . $customer->name . '</td>
                                            <td>' . $customer->email . '</td>
                                            <td>' . $customer->phone . '</td>
                                            <td>' . $customer->address . '</td>
                                        </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } if ($Owner || $Admin || $GP['suppliers-index']) { ?>

                            <div id="suppliers" class="tab-pane fade">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="suppliers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                   class="table table-bordered table-hover table-striped"
                                                   style="margin-bottom: 0;">
                                                <thead>
                                                <tr>
                                                    <th style="width:30px !important;">#</th>
                                                    <th><?= $this->lang->line("company"); ?></th>
                                                    <th><?= $this->lang->line("name"); ?></th>
                                                    <th><?= $this->lang->line("email"); ?></th>
                                                    <th><?= $this->lang->line("phone"); ?></th>
                                                    <th><?= $this->lang->line("address"); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($suppliers)) {
                                                    $r = 1;
                                                    foreach ($suppliers as $supplier) {
                                                        echo '<tr id="' . $supplier->id . '" class="supplier_link pointer"><td>' . $r . '</td>
                                        <td>' . $supplier->company . '</td>
                                        <td>' . $supplier->name . '</td>
                                        <td>' . $supplier->email . '</td>
                                        <td>' . $supplier->phone . '</td>
                                        <td>' . $supplier->address . '</td>
                                    </tr>';
                                                        $r++;
                                                    }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6"
                                                            class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } ?>

                        </div>


                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.order').click(function () {
            window.location.href = '<?=site_url()?>orders/view/' + $(this).attr('id') + '#comments';
        });
        $('.invoice').click(function () {
            window.location.href = '<?=site_url()?>orders/view/' + $(this).attr('id');
        });
        $('.quote').click(function () {
            window.location.href = '<?=site_url()?>quotes/view/' + $(this).attr('id');
        });
    });
</script>

<?php if (($Owner || $Admin) && $chatData) { ?>
    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
    <script src="<?= $assets; ?>js/hc/highcharts.js"></script>
    <script type="text/javascript">
        $(function () {
            Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
                return {
                    radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                    stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
                };
            });
            $('#ov-chart').highcharts({
                chart: {},
                credits: {enabled: false},
                title: {text: ''},
                xAxis: {categories: <?= json_encode($months); ?>},
                yAxis: {min: 0, title: ""},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        if (this.key) {
                            return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        } else {
                            var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                s += '<tr><td style="color:{series.color};padding:0">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                currencyFormat(this.y) + '</b></td></tr>';
                            });
                            s += '</table></div>';
                            return s;
                        }
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                series: [{
                    type: 'column',
                    name: '<?= lang("sp_tax"); ?>',
                    data: [<?php
                    echo implode(', ', $mtax1);
                    ?>]
                },
                    {
                        type: 'column',
                        name: '<?= lang("order_tax"); ?>',
                        data: [<?php
                    echo implode(', ', $mtax2);
                    ?>]
                    },
                    {
                        type: 'column',
                        name: '<?= lang("sales"); ?>',
                        data: [<?php
                    echo implode(', ', $msales);
                    ?>]
                    }, {
                        type: 'spline',
                        name: '<?= lang("purchases"); ?>',
                        data: [<?php
                    echo implode(', ', $mpurchases);
                    ?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'spline',
                        name: '<?= lang("pp_tax"); ?>',
                        data: [<?php
                    echo implode(', ', $mtax3);
                    ?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'pie',
                        name: '<?= lang("stock_value"); ?>',
                        data: [
                            ['', 0],
                            ['', 0],
                            ['<?= lang("stock_value_by_price"); ?>', <?php echo $stock->stock_by_price; ?>],
                            ['<?= lang("stock_value_by_cost"); ?>', <?php echo $stock->stock_by_cost; ?>],
                        ],
                        center: [80, 42],
                        size: 80,
                        showInLegend: false,
                        dataLabels: {
                            enabled: false
                        }
                    }]
            });
        });
    </script>

    <script type="text/javascript">
        $(function () {
            <?php if ($lmbs) { ?>
            $('#lmbschart').highcharts({
                chart: {type: 'column'},
                title: {text: ''},
                credits: {enabled: false},
                xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
                yAxis: {min: 0, title: {text: ''}},
                legend: {enabled: false},
                series: [{
                    name: '<?=lang('sold');?>',
                    data: [<?php
                    foreach ($lmbs as $r) {
                        if($r->quantity > 0) {
                            echo "['".$r->product_name."<br>(".$r->product_code.")', ".$r->quantity."],";
                        }
                    }
                    ?>],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#000',
                        align: 'right',
                        y: -25,
                        style: {fontSize: '12px'}
                    }
                }]
            });
            <?php } if ($bs) { ?>
            $('#bschart').highcharts({
                chart: {type: 'column'},
                title: {text: ''},
                credits: {enabled: false},
                xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
                yAxis: {min: 0, title: {text: ''}},
                legend: {enabled: false},
                series: [{
                    name: '<?=lang('sold');?>',
                    data: [<?php
                foreach ($bs as $r) {
                    if($r->quantity > 0) {
                        echo "['".$r->product_name."<br>(".$r->product_code.")', ".$r->quantity."],";
                    }
                }
                ?>],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#000',
                        align: 'right',
                        y: -25,
                        style: {fontSize: '12px'}
                    }
                }]
            });
            <?php } ?>
        });
    </script>
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i
                            class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers'), ' (' . date('M-Y', time()) . ')'; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i
                            class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="lmbschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<style>
.col-lg-1.col-md-2.col-xs-6 a {
    min-height: 82px;
}
</style>
