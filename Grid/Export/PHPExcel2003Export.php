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
 * PHPExcel_Excel 2003 Export (.xlsx)
 *
 */
class PHPExcel2003Export extends PHPExcel2007Export
{
    protected function getWriter()
    {
        $writer = parent::getWriter();
        $writer->setOffice2003Compatibility(true);

        return $writer;
    }
}
