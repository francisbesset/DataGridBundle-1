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
 * Delimiter-Separated Values
 *
 */
use Sorien\DataGridBundle\Grid\Grid;

class DSVExport extends Export
{
    protected $fileExtension = null;

    protected $mimeType = 'application/octet-stream';

    protected $delimiter = null;


    public function __construct($tilte, $fileName = 'export', $params = array())
    {
        $this->parameters['delimiter'] = $this->delimiter;

        parent::__construct($tilte, $fileName, $params);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatData($grid);

        // Array to dsv
        $outstream = fopen("php://temp", 'r+');

        foreach ($data as $line) {
            fputcsv($outstream, $line, $this->getDelimiter(), '"');
        }

        rewind($outstream);

        $content = '';
        while (($buffer = fgets($outstream)) !== false) {
            $content .= $buffer;
        }

        fclose($outstream);

        $this->content = $content;
    }

    /**
     * get delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->parameters['delimiter'];
    }

    /**
     * set delimiter
     *
     * @param string $separator
     *
     * @return self
     */
    public function setDelimiter($delimiter)
    {
        return $this->parameters['delimiter'] = $delimiter;
    }
}
