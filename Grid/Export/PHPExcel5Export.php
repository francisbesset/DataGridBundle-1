<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Export;

/**
 *
 * PHPExcel 5 Export (97-2003) (.xls)
 * 52 columns maximum
 *
 */
class PHPExcel5Export extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    public $excelObj;

    public function __construct($tilte, $fileName = 'export', $params = array())
    {
        $this->excelObj =  new \PHPExcel();

        parent::__construct($tilte, $fileName, $params);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatData($grid);

        $this->excelObj->setActiveSheetIndex(0);

        $row = 1;
        foreach ($data as $line) {
            $column = 'A';
            foreach ($line as $cell) {
                $this->excelObj->getActiveSheet()->SetCellValue($column.$row, $cell);

                if ($column == 'Z') {
                    $column = 'AA';
                } else {
                    $column++;
                }
            }
            $row++;
        }

        $objWriter = $this->getWriter();

        ob_start();

        $objWriter->save("php://output");

        $this->content = ob_get_contents();

        ob_end_clean();
    }

    protected function getWriter()
    {
        return new \PHPExcel_Writer_Excel5($this->excelObj);
    }
}
