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

use Magento\Framework\View\Element\Html\Select;

class Link extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value): Link
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param Value $value
     * @return $this
     */
    public function setInputId($value): Link
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
        $options = [
            ['value' => 'fa fa-facebook', 'label' => __('Facebook')],
            ['value' => 'fa fa-twitter', 'label' => __('Twitter')],
            ['value' => 'fa fa-whatsapp', 'label' => __('WhatsApp')],
            ['value' => 'fa fa-google', 'label' => __('Google')],
            ['value' => 'fa fa-linkedin', 'label' => __('LinkedIn')],
            ['value' => 'fa fa-youtube', 'label' => __('YouTube')],
            ['value' => 'fa fa-instagram', 'label' => __('Instagram')],
            ['value' => 'fa fa-pinterest', 'label' => __('Pinterest')],
            ['value' => 'fa fa-snapchat-ghost', 'label' => __('Snapchat')],
            ['value' => 'fa fa-skype', 'label' => __('Skype')],
            ['value' => 'fa fa-yahoo', 'label' => __('Yahoo')],
            ['value' => 'fa fa-soundcloud', 'label' => __('SoundCloud')],
            ['value' => 'fa fa-dribbble', 'label' => __('Dribbble')],
            ['value' => 'fa fa-vimeo', 'label' => __('Vimeo')],
            ['value' => 'fa fa-tumblr', 'label' => __('Tumblr')],
            ['value' => 'fa fa-vine', 'label' => __('Vine')],
            ['value' => 'fa fa-foursquare', 'label' => __('Foursquare')],
            ['value' => 'fa fa-stumbleupon', 'label' => __('Stumbleupon')],
            ['value' => 'fa fa-flickr', 'label' => __('Flickr')],
            ['value' => 'fa fa-reddit', 'label' => __('Reddit')],
            ['value' => 'fa fa-rss', 'label' => __('RSS')],
        ];
        $this->setOptions($options);
        return parent::_toHtml();
    }
}
