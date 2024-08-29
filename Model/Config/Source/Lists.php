<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_Configuration
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

declare(strict_types=1);

namespace ViraXpress\Configuration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

class Lists implements OptionSourceInterface
{
    /**
     * @var ThemeCollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @param ThemeCollectionFactory $themeCollectionFactory
     */
    public function __construct(
        ThemeCollectionFactory $themeCollectionFactory
    ) {
        $this->themeCollectionFactory = $themeCollectionFactory;
    }

    /**
     * Retrieve options array.
     *
     * @return array The options array.
     */
    public function toOptionArray(): array
    {
        /** @var ThemeCollection $themeCollection */
        $themeCollection = $this->themeCollectionFactory->create();
        $themeCollection->addAreaFilter();
        $themes = [];
        foreach ($themeCollection->getItems() as $theme) {
            $themes[] = [
                'value' => "{$theme->getArea()}/{$theme->getThemePath()}",
                'label' => $theme->getThemeTitle(),
            ];
        }
        return $themes;
    }
}
