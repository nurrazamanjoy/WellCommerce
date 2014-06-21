<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */
namespace WellCommerce\Plugin\Layout\DataGrid;

use Illuminate\Database\Capsule\Manager;
use WellCommerce\Core\Component\DataGrid\AbstractDataGrid;
use WellCommerce\Core\Component\DataGrid\Column\ColumnCollection;
use WellCommerce\Core\Component\DataGrid\Column\ColumnInterface;
use WellCommerce\Core\Component\DataGrid\Column\DataGridColumn;
use WellCommerce\Core\Component\DataGrid\DataGridInterface;

/**
 * Class LayoutThemeDataGrid
 *
 * @package WellCommerce\Plugin\LayoutTheme\DataGrid
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class LayoutThemeDataGrid extends AbstractDataGrid implements DataGridInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'availability';
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return [
            'edit' => $this->generateUrl('admin.availability.edit')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initColumns(ColumnCollection $columns)
    {
        $columns->add(new DataGridColumn([
            'id'         => 'id',
            'source'     => 'layout_theme.id',
            'caption'    => $this->trans('Id'),
            'sorting'    => [
                'default_order' => ColumnInterface::SORT_DIR_DESC
            ],
            'appearance' => [
                'width'   => 90,
                'visible' => false
            ],
            'filter'     => [
                'type' => ColumnInterface::FILTER_BETWEEN
            ]
        ]));

        $columns->add(new DataGridColumn([
            'id'         => 'name',
            'source'     => 'layout_theme.name',
            'caption'    => $this->trans('Name'),
            'appearance' => [
                'width' => 150,
                'align' => ColumnInterface::ALIGN_LEFT
            ],
            'filter'     => [
                'type' => ColumnInterface::FILTER_INPUT
            ]
        ]));

        $columns->add(new DataGridColumn([
            'id'         => 'folder',
            'source'     => 'layout_theme.folder',
            'caption'    => $this->trans('Folder'),
            'appearance' => [
                'width' => 60,
                'align' => ColumnInterface::ALIGN_LEFT
            ],
            'filter'     => [
                'type' => ColumnInterface::FILTER_INPUT
            ]
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery(Manager $manager)
    {
        $this->query = $manager->table('layout_theme');
        $this->query->groupBy('layout_theme.id');
    }
}