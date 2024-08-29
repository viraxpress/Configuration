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

class GlobalEffects implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    private $options;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => '', 'label' => __('--- Please Select Effect ---')],
                ['value' => 'slide_up', 'label' => __('Slide Up')],
                ['value' => 'slide_down', 'label' => __('Slide Down')],
                ['value' => 'slide_left', 'label' => __('Slide Left')],
                ['value' => 'slide_right', 'label' => __('Slide Right')]
            ];
        }
        return $this->options;
    }
}
