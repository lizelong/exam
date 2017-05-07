<?php
/************************************
 * 公共函数库
 * author：li_zelong@126.com 
 * time:2017-03-13		
 ************************************/

/**
 * 根据传入的秒数格式化时间
 * @param  int $time 秒数
 * @return string   超出60秒返回分
 */
function f_time($time)
{
	if ($time < 60) {
		return ceil($time).' 秒';
	} elseif ($time < 60*60) {
		return ceil($time / 60).' 分';
	} elseif ($time < 60*60*24) {
		return floor($time / (60*60)).' 小时 '.ceil(($time - (floor($time / (60*60)) * 3600))/60).' 分';
	} elseif ($time < 60*60*24*7) {
		return ceil($time / (60*60*24)).' 天';
	} elseif ($time < 60*60*24*31) {
		return ceil($time / (60*60*24*7)).' 周';
	} elseif ($time < 60*60*24*31*12) {
		return ceil($time / (60*60*24*31)).' 月';
	} else {
		return ceil($time / (60*60*24*31*12)).' 年';
	}
}

/**
 * 递归转换数组中的值为小写
 * @param  array $arr 要转换的数组
 * @return array      转换好的数组
 */
function deepTolower($arr) 
{
	if (is_array($arr)) {
		foreach ($arr as &$v) {
			$v = deepTolower($v);
		}
		return $arr;
	} else {
		return strtolower($arr);
	}
}

/**
 * 二维数组根据某个字段进行排序
 * @param  array $arr        要排序的数组
 * @param  string $field     要排序的字段
 * @param  int $direction 排序的规则常量：SORT_ASC升序；SORT_DESC降序
 * @return array            排序好的数组
 */
function multisort($arr, $field, $direction = SORT_ASC) 
{
    $arrSort = []; //准备用于排序的数组
    foreach($arr as $k => $v){
        foreach($v as $key=>$value){
            $arrSort[$key][$k] = $value;
        }
    }

    //数组全是引用传递的
    array_multisort(
        //以这个要排序的数组为基准进行排序
        $arrSort[$field], $direction, 
        $arr
    );

    return $arr;
}

/**
 * Excel导出
 * @param data 导出数据
 * @param title 表格的字段名 字段长度有限制，一般都够用，可以改变 $length1和$length2来增长
 * @return subject 表格主题 命名为主题+导出日期
 */
function exportExcel($data, $title, $subject)
{  
    Vendor('phpexcel.PHPExcel');
    Vendor('phpexcel.PHPExcel.IOFactory');
    Vendor('phpexcel.PHPExcel.Reader.Excel5');
    // Create new PHPExcel object  
    $objPHPExcel = new PHPExcel();   
    $length1 = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD'];
    $length2 = ['A1','B1','C1','D1','E1','F1','G1','H1','I1','J1','K1','L1','M1','N1','O1','P1','Q1','R1','S1','T1','U1','V1','W1','X1','Y1','Z1','AA1','AB1','AC1','AD1'];
    //循环设置单元格的宽度  
    for ($a = 0; $a < count($title); $a++){
        $objPHPExcel->getActiveSheet()->getColumnDimension($length1[$a])->setWidth(20); 
    }
    //设置表头字体大小  
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(15);  
    //设置表头文字加粗  
    $objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getFont()->setBold(true);
    //设置表头文字居中
   	$objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
   	//设置表头边框线
    $objPHPExcel->getActiveSheet()->getStyle($length2[0].':'.$length2[count($title)-1])->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
    
    // 循环设置表头文字  
    for($a = 0; $a < count($title); $a++){
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue($length2[$a], $title[$a]); 
    }
    for($i = 0; $i < count($data); $i++){ 
        $buffer = $data[$i];
        $number = 0;
        foreach ($buffer as $k=>$value) {
        	//判断单元格应该有的格式
        	$type = is_numeric($value) ? PHPExcel_Cell_DataType::TYPE_NUMERIC :PHPExcel_Cell_DataType::TYPE_STRING;
            $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($length1[$number].($i+2),$value,$type);

            /*不及格填充红色(未完善，只能填充当前单元格)*/
            if ($k == 'total_h' && (int)$value < 70) {
            	$objPHPExcel->getActiveSheet()->getStyle($length1[$number].($i+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle($length1[$number].($i+2))->getFill()->getStartColor()->setARGB('FFFF8080');
            }
            $number++;
        }
        unset($value);
        //垂直居中
        $objPHPExcel->getActiveSheet()->getStyle($length1[0].($i+2).':'.$length1[$number-1].($i+2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //边框  
        $objPHPExcel->getActiveSheet()->getStyle($length1[0].($i+2).':'.$length1[$number-1].($i+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
    }  
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet  
    $objPHPExcel->setActiveSheetIndex(0);  

    ob_end_clean();//清除缓冲区,避免乱码
    // Redirect output to a client’s web browser (Excel5)  
    header('Content-Type: application/vnd.ms-excel');  
    header('Content-Disposition: attachment;filename='.$subject.'.xls');  
    header('Cache-Control: max-age=0');  

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
    $objWriter->save('php://output');  
}  