<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;
use Symfony\Component\HttpFoundation\Request;

class DateColumn extends DateTimeColumn
{
    protected $timetype = \IntlDateFormatter::NONE;

    protected $fallbackFormat = 'Y-m-d';

    public function getFilters()
    {
        $result = array();

        $dateFrom = new \DateTime($this->data);
        $dateFrom->setTime(0, 0, 0);
        $result[] =  new Filter(self::OPERATOR_GTE, $dateFrom);

        $dateTo = new \DateTime($this->data);
        $dateTo->setTime(23, 59, 59);
        $result[] =  new Filter(self::OPERATOR_LTE, $dateTo);

        return $result;
    }

    public function getType()
    {
        return 'date';
    }
}
