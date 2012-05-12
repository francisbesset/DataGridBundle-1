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

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 *
 * XML
 *
 */
use Sorien\DataGridBundle\Grid\Grid;

class XMLExport extends Export
{
    protected $fileExtension = 'xml';

    protected $mimeType = 'application/xml';

    public function computeData($grid)
    {
        $xmlEncoder = new XmlEncoder();
        $xmlEncoder->setRootNodeName('grid');
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('xml' => $xmlEncoder));

        $data = $this->getData($grid);

        $convertData['titles'] = $data['titles'];
        $convertData['rows']['row'] = $data['rows'];

        $this->content = $serializer->serialize($convertData, 'xml');
    }
}
