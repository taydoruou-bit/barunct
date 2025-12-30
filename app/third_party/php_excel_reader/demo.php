<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
	// chuong trinh demo doc truc tiep file excel với tiếng việt unicode 
	$filename="demo.xls"; // file du lieu demmo chi gom 2 cot hoten va diem
	require_once 'excel_reader2_patch_applied.php'; // nhung thu vien xu ly ma nguon mo 
	$data = new Spreadsheet_Excel_Reader($filename,true,"UTF-8"); // khoi tao doi tuong doc file excel 
	$rowsnum = $data->rowcount($sheet_index=0); // lay so hang cua sheet
	$colsnum =  $data->colcount($sheet_index=0); // lay so cot cua sheet
	for ($i=2;$i<=$rowsnum;$i++) // doc tu hang so 2 vi hang 1 la tieu de roi!
		echo "<br>".$data->val($i,1)." , ".$data->val($i,2); // xuat cot so 1 va cot so 2 tren cung 1 hang			
?>
</body>
</html>
