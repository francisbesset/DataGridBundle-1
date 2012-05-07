<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Twig;

use Sorien\DataGridBundle\Grid\Grid;

class DataGridExtension extends \Twig_Extension
{
    const DEFAULT_TEMPLATE = 'SorienDataGridBundle::blocks.html.twig';

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var \Twig_TemplateInterface[]
     */
    protected $templates;

    /**
     * @var string
     */
    protected $theme;

    /**
    * @var \Symfony\Component\Routing\Router
    */
    protected $router;

    /**
     * @var array
     */
    protected $names;

    /**
     * @var array
     */
    protected $params = array();

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'grid'              => new \Twig_Function_Method($this, 'getGrid', array('is_safe' => array('html'))),
            'grid_titles'       => new \Twig_Function_Method($this, 'getGridTitles', array('is_safe' => array('html'))),
            'grid_filters'      => new \Twig_Function_Method($this, 'getGridFilters', array('is_safe' => array('html'))),
            'grid_rows'         => new \Twig_Function_Method($this, 'getGridRows', array('is_safe' => array('html'))),
            'grid_pager'        => new \Twig_Function_Method($this, 'getGridPager', array('is_safe' => array('html'))),
            'grid_actions'      => new \Twig_Function_Method($this, 'getGridActions', array('is_safe' => array('html'))),
            'grid_url'          => new \Twig_Function_Method($this, 'getGridUrl'),
            'grid_filter'       => new \Twig_Function_Method($this, 'getGridFilter'),
            'grid_cell'         => new \Twig_Function_Method($this, 'getGridCell', array('is_safe' => array('html'))),
            'grid_no_data'      => new \Twig_Function_Method($this, 'getGridNoData', array('is_safe' => array('html'))),
            'grid_no_result'    => new \Twig_Function_Method($this, 'getGridNoResult', array('is_safe' => array('html'))),
            'grid_exports'      => new \Twig_Function_Method($this, 'getGridExports', array('is_safe' => array('html'))),
        );
    }

    /**
     * Render grid block
     *
     * @param \Sorien\DataGridBundle\Grid\Grid $grid
     * @param string $theme
     * @param string $id
     * @return string
     */
    public function getGrid($grid, $theme = null, $id = '', array $params = array())
    {
        $this->theme = $theme;

        // Export
        if (!is_array($theme)) {
            $grid->setTemplate($theme);
        }

        $this->names[$grid->getHash()] = $id == '' ? $grid->getId() : $id;
        $this->params = $params;
        $this->templates = array();

        return $this->renderBlock('grid', array('grid' => $grid->prepare()));
    }

    public function getGridTitles($grid)
    {
        return $this->renderBlock('grid_titles', array('grid' => $grid));
    }

    public function getGridFilters($grid)
    {
        return $this->renderBlock('grid_filters', array('grid' => $grid));
    }

    public function getGridRows($grid)
    {
        return $this->renderBlock('grid_rows', array('grid' => $grid));
    }

    public function getGridPager($grid)
    {
        return $this->renderBlock('grid_pager', array('grid' => $grid));
    }

    public function getGridActions($grid)
    {
        return $this->renderBlock('grid_actions', array('grid' => $grid));
    }

    public function getGridExports($grid)
    {
        return $this->renderBlock('grid_exports', array('grid' => $grid));
    }

    /**
     * Cell Drawing override
     *
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @param \Sorien\DataGridBundle\Grid\Row $row
     * @param \Sorien\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridCell($column, $row, $grid)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->router);

        if (($id = $this->names[$grid->getHash()]) != '')
        {
            if ($this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_cell'))
            {
                return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
            }

            if ($this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getType().'_cell'))
            {
                return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
            }
        }

        if ($this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell'))
        {
            return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
        }

        if ($this->hasBlock($block = 'grid_column_'.$column->getType().'_cell'))
        {
            return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
        }

        return $value;
    }

    /**
     * Filter Drawing override
     *
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @param \Sorien\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridFilter($column, $grid)
    {
        if (($id = $this->names[$grid->getHash()]) != '')
        {
            if ($this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_filter'))
            {
                return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
            }

            if ($this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getType().'_filter'))
            {
                return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
            }

            if ($this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_filter'))
            {
                return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
            }
        }

        if ($this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_filter'))
        {
            return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
        }

        if ($this->hasBlock($block = 'grid_column_type_'.$column->getType().'_filter'))
        {
            return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
        }

        if ($this->hasBlock($block = 'grid_column_type_'.$column->getParentType().'_filter'))
        {
            return $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash()));
        }

        return $column->renderFilter($grid->getHash());
    }

    /**
     * @param string $section
     * @param \Sorien\DataGridBundle\Grid\Grid $grid
     * @param \Sorien\DataGridBundle\Grid\Column\Column $param
     * @return string
     */
    public function getGridUrl($section, $grid, $param = null)
    {
        $separator =  strpos($grid->getRouteUrl(), '?') ? '&' : '?';

        if ($section == 'order')
        {
            if ($param->isSorted())
            {
                return $grid->getRouteUrl().$separator.$grid->getHash().'['.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|'.($param->getOrder() == 'asc' ? 'desc' : 'asc');
            }
            else
            {
                return $grid->getRouteUrl().$separator.$grid->getHash().'['.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|asc';
            }
        }
        elseif ($section == 'page')
        {
            return $grid->getRouteUrl().$separator.$grid->getHash().'['.Grid::REQUEST_QUERY_PAGE.']='.$param;
        }
        elseif ($section == 'limit')
        {
            return $grid->getRouteUrl().$separator.$grid->getHash().'['.Grid::REQUEST_QUERY_LIMIT.']=';
        }
    }

    public function getGridNoData($grid)
    {
        return $this->renderBlock('grid_no_data', array('grid' => $grid));
    }

    public function getGridNoResult($grid)
    {
        return $this->renderBlock('grid_no_result', array('grid' => $grid));
    }

    /**
     * Render block
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    private function renderBlock($name, $parameters)
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
     * Has block
     *
     * @param $name string
     * @return boolean
     */
    private function hasBlock($name)
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
     * Template Loader
     *
     * @return \Twig_TemplateInterface[]
     * @throws \Exception
     */
    private function getTemplates()
    {
        if (empty($this->templates))
        {
            //get template name
            if (is_array($this->theme)) // Export
            {
                foreach ($this->theme as $theme)
                {
                    if ($theme instanceof \Twig_Template) {
                        $this->templates[] = $theme;
                    } elseif (is_string($theme)) {
                        $this->templates = array_merge($this->templates, $this->getTemplatesFromString($theme));
                    }
                }
            }
            elseif ($this->theme instanceof \Twig_Template)
            {
                $this->templates[] = $this->theme;
                $this->templates[] = $this->environment->loadTemplate($this::DEFAULT_TEMPLATE);
            }
            elseif (is_string($this->theme))
            {
                $this->templates = $this->getTemplatesFromString($this->theme);
            }
            elseif (is_null($this->theme))
            {
                $this->templates[] = $this->environment->loadTemplate($this::DEFAULT_TEMPLATE);
            }
            else
            {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    private function getTemplatesFromString($theme)
    {
        $this->templates = array();

        $template = $this->environment->loadTemplate($theme);
        while ($template != null)
        {
            $this->templates[] = $template;
            $template = $template->getParent(array());
        }

        $this->templates[] = $this->environment->loadTemplate($theme);

        return $this->templates;
    }

    public function getName()
    {
        return 'datagrid_twig_extension';
    }
}

