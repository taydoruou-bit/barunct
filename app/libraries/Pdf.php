<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*
 *  ============================================================================== 
 *  Author	: Hoang son
 *  Email	: hoangsondev212@gmail.com 
 *  For		: ESC/POS Print Driver for PHP
 *  License	: SCODEWEB License
 *  ============================================================================== 
 */
include(APPPATH . "third_party/MPDF/mpdf.php");

class Pdf extends mPDF
{
    public function __construct()
    {
        parent::__construct();
    }
}
