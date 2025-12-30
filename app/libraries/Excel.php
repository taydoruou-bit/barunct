<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
 *  ============================================================================== 
 *  Author	: Hoang son
 *  Email	: hoangsondev212@gmail.com 
 *  For		: ESC/POS Print Driver for PHP
 *  License	: SCODEWEB License
 *  ============================================================================== 
 */
require_once APPPATH . "/third_party/PHPExcel/PHPExcel.php";

class Excel extends PHPExcel
{
    public function __construct()
    {
        parent::__construct();
    }
}
