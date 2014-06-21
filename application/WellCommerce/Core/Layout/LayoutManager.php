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
namespace WellCommerce\Core\Layout;

use WellCommerce\Core\Component\AbstractComponent;
use WellCommerce\Core\Layout\Box\LayoutBoxConfiguratorInterface;
use WellCommerce\Core\Layout\Page\LayoutPageInterface;

/**
 * Class LayoutManager
 *
 * @package WellCommerce\Core
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class LayoutManager extends AbstractComponent
{
    /**
     * @var array
     */
    private $layoutBoxConfigurators = [];

    /**
     * Adds new layout box configurator to stack
     *
     * @param                                $id
     * @param LayoutBoxConfiguratorInterface $configurator
     */
    public function addLayoutBoxConfigurator($alias, LayoutBoxConfiguratorInterface $configurator)
    {
        $this->layoutBoxConfigurators[$alias] = $configurator;
    }

    public function getLayoutBoxConfigurator($alias)
    {
        if (!isset($this->layoutBoxConfigurators[$alias])) {
            throw new \RuntimeException(sprintf('LayoutBoxConfigurator "%s" is not registered', $alias));
        }

        return $this->layoutBoxConfigurators[$alias];
    }

    /**
     * Returns all layout box configurators
     *
     * @return array
     */
    public function getLayoutBoxConfigurators()
    {
        return $this->layoutBoxConfigurators;
    }
}