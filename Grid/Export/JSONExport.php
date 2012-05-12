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
 * JSON
 *
 */
class JSONExport extends Export
{
    protected $fileExtension = 'json';

    public function computeData($grid)
    {
        $this->content = json_encode($this->getData($grid));
    }
}
