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

namespace ViraXpress\Configuration\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use ViraXpress\Configuration\Model\Config\Source\GlobalEffects;

class Effects extends Select
{
    /**
     * @var GlobalEffects
     */
     protected $globalEffects;

    /**
     * Constructor
     *
     * @param Context $context
     * @param GlobalEffects $globalEffects
     * @param array $data
     */
    public function __construct(
        Context $context,
        GlobalEffects $globalEffects,
        array $data = []
    ) {
        $this->globalEffects = $globalEffects;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value): Effects
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param Value $value
     * @return $this
     */
    public function setInputId($value): Effects
    {
        return $this->setId($value);
    }

    /**
     * Render the HTML content for the element.
     *
     * Sets options for social media icons and returns the parent's HTML content.
     *
     * @return string
     */
    public function _toHtml()
    {
        $options = [];
        $options = $this->globalEffects->toOptionArray();
        $this->setOptions($options);
        return parent::_toHtml();
    }
}
