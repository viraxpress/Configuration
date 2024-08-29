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

namespace ViraXpress\Configuration\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class CustomizableEffects extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('link', ['label' => __('Effects'), 'renderer' => $this->_getLinkRenderer(), 'class' => 'required-entry']);
        $this->addColumn('path', ['label' => __('Add Class (or) Id'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Retrieve the link renderer block instance.
     *
     * @return \ViraXpress\Configuration\Block\Adminhtml\Form\Field\Link
     */
    protected function _getLinkRenderer()
    {
        $renderer = $this->getLayout()->createBlock(
            \ViraXpress\Configuration\Block\Adminhtml\Form\Field\Effects::class,
            '',
            ['data' => ['is_render_to_js_template' => false]]
        );
        $renderer->setClass('required-entry');
        return $renderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }
}
