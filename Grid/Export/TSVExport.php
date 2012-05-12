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
 * Tab-Separated Values
 *
 */
class TSVExport extends DSVExport
{
    protected $fileExtension = 'tsv';

    protected $mimeType = 'text/tab-separated-values';

    protected $delimiter = "\t";

    public function __construct($tilte, $fileName)
    {
        parent::__construct($tilte, $fileName);
    }
}
