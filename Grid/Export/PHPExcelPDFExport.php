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
 * PHPExcel PDF Export
 *
 */
class PHPExcelPDFExport extends PHPExcel5Export
{
    protected $fileExtension = 'pdf';

    protected $mimeType = 'application/pdf';

    protected function getWriter()
    {
        //$this->excelObj->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        //$this->excelObj->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //$this->excelObj->getActiveSheet()->getPageSetup()->setScale(50);
        $writer = new \PHPExcel_Writer_PDF($this->excelObj);
        //$writer->setSheetIndex(0);
        //$writer->setPaperSize("A4");
        $writer->writeAllSheets();

        return $writer;
    }
}
