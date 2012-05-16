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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class Export implements ContainerAwareInterface
{
    const DEFAULT_TEMPLATE = 'SorienDataGridBundle::blocks.html.twig';

    protected $title;

    protected $fileName;

    protected $fileExtension = null;

    protected $mimeType = 'application/octet-stream';

    protected $parameters = array();

    protected $container;

    protected $template;

    protected $templates;

    protected $twig;

    protected $grid;

    protected $params = array();

    protected $content;


    public function __construct($title, $fileName = 'export', $params = array())
    {
        $this->title = $title;
        $this->fileName = $fileName;
        $this->params = $params;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    abstract public function computedata($grid);

    public function getResponse()
    {
        // Response
        if (function_exists('mb_strlen')) {
            $filesize = mb_strlen($this->content, $this->container->getParameter('kernel.charset'));
        } else {
            $filesize = strlen($this->content);
        }

        $headers = array(
            'Content-Description' => 'File Transfer',
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $this->getBaseName()),
            'Content-Transfer-Encoding' => 'binary',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => $filesize
        );

        return new Response($this->content, 200, $headers);
    }

    /**
     * Get data form the grid
     *
     * @param Grid $grid
     *
     * @return array
     *
     * array(
     *     'titles' => array(
     *         'column_id_1' => 'column_title_1',
     *         'column_id_2' => 'column_title_2'
     *     ),
     *     'rows' =>array(
     *          array(
     *              'column_id_1' => 'column_value_row_1',
     *              'column_id_2' => 'column_value_row_1'
     *          ),
     *          array(
     *              'column_id_1' => 'column_value_row_2',
     *              'column_id_2' => 'column_value_row_2'
     *          )
     *     )
     * )
     */
    protected function getData($grid)
    {
        $result = array();

        $this->twig = $this->container->get('twig');
        $this->grid = $grid;

        if (is_null($this->template)) {
            $this->template = $this->grid->getTemplate();
        }

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getTitles();
        }

        $result['rows']  = $this->getRows();

        return $result;
    }

    /**
     * Get data form the grid in a flat array
     *
     * @param Grid $grid
     *
     * @return array
     *
     * array(
     *     '0' => array(
     *         'column_id_1' => 'column_title_1',
     *         'column_id_2' => 'column_title_2'
     *     ),
     *     '1' => array(
     *          'column_id_1' => 'column_value_row_1',
     *          'column_id_2' => 'column_value_row_1'
     *      ),
     *     '2' => array(
     *          'column_id_1' => 'column_value_row_2',
     *          'column_id_2' => 'column_value_row_2'
     *      )
     * )
     */
    protected function getFlatData($grid)
    {
        $data = $this->getData($grid);

        $flatData = array();
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return array_merge($flatData, $data['rows']);;
    }

    protected function getTitles()
    {
        $titlesHTML = $this->twig->loadTemplate($this->template)->renderBlock('grid_titles', array('grid' => $this->grid));

        preg_match_all('#<th[^>]*?>(.*)?</th>#isU', $titlesHTML, $matches);

        if (empty($matches)) {
            preg_match_all('#<td[^>]*?>(.*)?</td>#isU', $titlesHTML, $matches);
        }

        if (empty($matches)) {
            new \Exception('Table header (th or td) tags not found.');
        }

        $titlesClean = array_map(array($this, 'cleanHTML'), $matches[0]);

        $i = 0;
        $titles = array();
        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                if (!isset($titlesClean[$i])) {
                    throw new \OutOfBoundsException('There are more more visible columns than titles found.');
                }
                $titles[$column->getId()] = $titlesClean[$i++];
            }
        }

        return $titles;
    }

    protected function getRows()
    {
        $rows = array();
        foreach ($this->grid->getRows() as $i => $row) {
            foreach ($this->grid->getColumns() as $column) {
                if ($column->isVisible(true)) {
                    $cellHTML = $this->getGridCell($column, $row);
                    $rows[$i][$column->getId()] = $this->cleanHTML($cellHTML);
                }
            }
        }

        return $rows;
    }

    protected function getGridCell($column, $row)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->container->get('router'));

        if ($this->hasBlock($block = 'grid_'.$this->grid->getHash().'_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_'.$this->grid->getHash().'_column_'.$column->getType().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getType().'_cell'))
        {
            return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
        }

        return $value;
    }

    /**
     * Has block
     *
     * @param $name string
     * @return boolean
     */
    protected function hasBlock($name)
    {
        foreach ($this->getTemplates() as $template)
        {
            if ($template->hasBlock($name))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Render block
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    protected function renderBlock($name, $parameters)
    {
        foreach ($this->getTemplates() as $template)
        {
            if ($template->hasBlock($name))
            {
                return $template->renderBlock($name, array_merge($parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, 'ee'));
    }

    /**
     * Template Loader
     *
     * @return \Twig_TemplateInterface[]
     * @throws \Exception
     */
    protected function getTemplates()
    {
        $template = $this->grid->getTemplate();

        if (empty($this->templates))
        {
            //get template name
            if ($template instanceof \Twig_Template)
            {
                $this->templates[] = $template;
                $this->templates[] = $this->environment->loadTemplate($this::DEFAULT_TEMPLATE);
            }
            elseif (is_string($template))
            {
                $this->templates = $this->getTemplatesFromString($template);
            }
            elseif (is_null($this->theme))
            {
                $this->templates[] = $this->twig->loadTemplate($this::DEFAULT_TEMPLATE);
            }
            else
            {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    protected function getTemplatesFromString($theme)
    {
        $templates = array();

        $template = $this->twig->loadTemplate($theme);
        while ($template != null)
        {
            $templates[] = $template;
            $template = $template->getParent(array());
        }

        $templates[] = $this->twig->loadTemplate($theme);

        return $templates;
    }

    protected function cleanHTML($value)
    {
        $value = trim($value);

        // Clean indent
        $value = preg_replace('/>[\s\n\t\r]*</', '><', $value);

        // Clean HTML tags
        $value = strip_tags($value);

        // Convert Special Characters in HTML
        $value = html_entity_decode($value);

        return $value;
    }

    /**
     * get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set title
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * set file name
     *
     * @param string $fileName
     *
     * @return self
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * get file extension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * set file extension
     *
     * @param string $fileExtension
     *
     * @return self
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    /**
     * get base name
     *
     * @return string
     */
    public function getBaseName()
    {
        return $this->fileName.(isset($this->fileExtension) ? ".$this->fileExtension" : '');
    }

    /**
     * get response mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * set response mime type
     *
     * @param string $mimeType
     *
     * @return self
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * set parameters
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * has parameter
     *
     * @return mixed
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * get parameter
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!hasParameter($name)) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * set parameter
     *
     * @param array $template
     *
     * @return self
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }




    /**
     * get template
     *
     * @return boolean
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * set template
     *
     * @param string $template
     *
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }
}
