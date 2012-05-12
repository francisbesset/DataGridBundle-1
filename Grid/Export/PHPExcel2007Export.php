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
 * PHPExcel 2007 Export
 *
 */
class PHPExcel2007Export extends PHPExcel5Export
{
    protected $fileExtension = 'xlsx';

    protected $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected function getWriter()
    {
        $writer = new \PHPExcel_Writer_Excel2007($this->excelObj);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
