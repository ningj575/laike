<?php

/*
 *  Excel
 * */

namespace app\common\server\common;

class ExcelServer extends \app\common\server\BaseServer {

    public function readExecl($file = '', $sheet = 0) {
        $file = iconv('utf-8', 'gb2312', $file); //转码
        if (empty($file) || !file_exists($file)) {
            return [];
        }
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls'); // Xlsx
        if (!$reader->canRead($file)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            if (!$reader->canRead($file)) {
                return [];
            }
        }
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($file); //载入excel表格
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // 总行数
        $highestColumn = $worksheet->getHighestColumn(); // 总列数
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL',
            'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',);
        $columnCnt = array_search($highestColumn, $cellName);
        $data = array();
        for ($_row = 1; $_row <= $highestRow; ++$_row) {
            //读取内容
            for ($_column = 0; $_column <= $columnCnt; ++$_column) {
                $cellId = $cellName[$_column] . $_row;
                $cellValue = (string) $worksheet->getCell($cellId)->getValue();
                if ($cellValue instanceof \PHPExcel_RichText) {
                    //富文本转换字符串
                    $cellValue = $cellValue->__toString();
                }
                $data[$_row][$cellName[$_column]] = $cellValue;
            }
        }
        //删除单元格名称
        $data = array_splice($data, 1);
        return json_decode(json_encode($data), 1);
    }

}
