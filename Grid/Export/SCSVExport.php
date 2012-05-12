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
 * Semi-Colon-Separated Values
 *
 */
class SCSVExport extends DSVExport
{
    protected $fileExtension = 'csv';

    protected $mimeType = 'text/comma-separated-values';

    protected $delimiter = ';';

    public function __construct($tilte, $fileName)
    {
        parent::__construct($tilte, $fileName);
    }
}
