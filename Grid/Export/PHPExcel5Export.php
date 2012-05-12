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

        // Set properties
        //$this->excelObj->getProperties()->setCreator("Maarten Balliauw");
        //$this->excelObj->getProperties()->setLastModifiedBy("Maarten Balliauw");
        //$this->excelObj->getProperties()->setTitle("Office 2007 XLSX Test Document");
        //$this->excelObj->getProperties()->setSubject("Office 2007 XLSX Test Document");
        //$this->excelObj->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

        // Add some data
        $this->excelObj->setActiveSheetIndex(0);
        $this->excelObj->getActiveSheet()->SetCellValue('A1', 'Hello');
        //$this->excelObj->getActiveSheet()->SetCellValue('B2', 'world!');
        //$this->excelObj->getActiveSheet()->SetCellValue('C1', 'Hello');
        //$this->excelObj->getActiveSheet()->SetCellValue('D2', 'world!');

        // Rename sheet
        //$this->excelObj->getActiveSheet()->setTitle('Simple');

        // Save Excel 5 file
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
