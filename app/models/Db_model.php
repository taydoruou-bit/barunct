<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Db_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getLatestSales()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("sales", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLastestQuotes()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("quotes", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestPurchases()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("purchases", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestTransfers()
    {
        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->order_by('id', 'desc');
        $q = $this->db->get("transfers", 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestCustomers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where("companies", array('group_name' => 'customer'), 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getLatestSuppliers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get_where("companies", array('group_name' => 'supplier'), 5);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockValue()
    {
        $q = $this->db->query("SELECT SUM(qty*price) as stock_by_price, SUM(qty*cost) as stock_by_cost
        FROM (
            Select sum(COALESCE(" . $this->db->dbprefix('warehouses_products') . ".quantity, 0)) as qty, price, cost
            FROM " . $this->db->dbprefix('products') . "
            JOIN " . $this->db->dbprefix('warehouses_products') . " ON " . $this->db->dbprefix('warehouses_products') . ".product_id=" . $this->db->dbprefix('products') . ".id
            GROUP BY " . $this->db->dbprefix('warehouses_products') . ".id ) a");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBestSeller($start_date = NULL, $end_date = NULL)
    {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
        }
        if (!$end_date) {
            $end_date = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
        }

        $this->db
            ->select("product_name, product_code")
            ->select_sum('quantity')
            ->from('sale_items')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)
            ->where('date <', $end_date)
            ->group_by('product_name, product_code')
            ->order_by('sum(quantity)', 'desc')
            ->limit(10);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getDashboardSummary()
    {
        $today_start = date('Y-m-d') . ' 00:00:00';
        $today_end = date('Y-m-d') . ' 23:59:59';
        $month_start = date('Y-m-01') . ' 00:00:00';
        $month_end = date('Y-m-t') . ' 23:59:59';
        $user_filter = '';

        if ($this->Settings->restrict_user && !$this->Owner && !$this->Admin) {
            $user_id = (int) $this->session->userdata('user_id');
            $user_filter = " AND created_by = {$user_id}";
        }

        $query = "
            SELECT
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_today_count,
                (SELECT COALESCE(SUM(grand_total), 0) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_today_total,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_month_count,
                (SELECT COALESCE(SUM(grand_total), 0) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_month_total,
                (SELECT COALESCE(SUM(paid), 0) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_month_paid,
                (SELECT COALESCE(SUM(COALESCE(grand_total, 0) - COALESCE(paid, 0)), 0) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS sales_month_due,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('sales') . " WHERE date BETWEEN ? AND ? AND payment_status IN ('due', 'partial') {$user_filter}) AS sales_due_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('quotes') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS quotes_month_count,
                (SELECT COALESCE(SUM(grand_total), 0) FROM " . $this->db->dbprefix('quotes') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS quotes_month_total,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('quotes') . " WHERE date BETWEEN ? AND ? AND status = 'pending' {$user_filter}) AS quotes_pending_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('quotes') . " WHERE date BETWEEN ? AND ? AND status = 'sent' {$user_filter}) AS quotes_sent_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('quotes') . " WHERE date BETWEEN ? AND ? AND status = 'completed' {$user_filter}) AS quotes_completed_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('quotes') . " WHERE status IN ('pending', 'sent') {$user_filter}) AS quotes_open_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('purchases') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS purchases_month_count,
                (SELECT COALESCE(SUM(grand_total), 0) FROM " . $this->db->dbprefix('purchases') . " WHERE date BETWEEN ? AND ? {$user_filter}) AS purchases_month_total,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('companies') . " WHERE group_name = 'customer') AS customers_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('products') . ") AS products_count,
                (SELECT COUNT(*) FROM " . $this->db->dbprefix('products') . " p
                    LEFT JOIN (
                        SELECT product_id, SUM(COALESCE(quantity, 0)) AS quantity
                        FROM " . $this->db->dbprefix('warehouses_products') . "
                        GROUP BY product_id
                    ) wp ON wp.product_id = p.id
                    WHERE COALESCE(wp.quantity, 0) <= COALESCE(p.alert_quantity, 0)
                ) AS low_stock_count
        ";

        $params = array(
            $today_start, $today_end,
            $today_start, $today_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end,
            $month_start, $month_end
        );

        $q = $this->db->query($query, $params);
        return $q->num_rows() > 0 ? $q->row() : FALSE;
    }

}
