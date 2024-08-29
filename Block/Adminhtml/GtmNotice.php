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

class GtmNotice extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Render the HTML output for the form element.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element The form element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $html = '';
        if ($element->getId() == 'tm_gtm_config_gtm_tracking_gtm_notice') {
            $html = '<div class="e-commerce-notice-container">
                        <span class="e-commerce-notice-msg" >For implementing any custom or below eCommerce trackings, please </span><a href="http://192.168.1.69:8001/#contact" style="font-weight:bold;">contact us.</a>
                        <a href="http://192.168.1.69:8001/#contact">
                            <img class="e-commerce-notice-logo" src="' . $baseUrl . 'media/logo/stores/1/viraxpress-logo.png" title="Brand logo of the ViraXpress" alt="Brand logo of the ViraXpress on Footer" width="100" height="25">
                        </a>
                        <ul class="ecommerce-listings">
                          <li class="ecommerce-items">add_to_cart</li>
                          <li class="ecommerce-items">view_cart</li>
                          <li class="ecommerce-items">begin_checkout</li>
                          <li class="ecommerce-items">purchase.. etc</li>
                        </ul>
                    </div>';
        }
        if ($element->getId() == 'tm_gtm_config_fb_pixel_tracking_fb_notice') {
            $html = '<div class="e-commerce-notice-container">
                        <span class="e-commerce-notice-msg" >For implementing any custom or below eCommerce trackings, please </span><a href="http://192.168.1.69:8001/#contact" style="font-weight:bold;">contact us.</a>
                        <a href="http://192.168.1.69:8001/#contact">
                            <img class="e-commerce-notice-logo" src="' . $baseUrl . 'media/logo/stores/1/viraxpress-logo.png" title="Brand logo of the ViraXpress" alt="Brand logo of the ViraXpress on Footer" width="100" height="25">
                        </a>
                        <ul class="ecommerce-listings">
                          <li class="ecommerce-items">AddToCart</li>
                          <li class="ecommerce-items">AddPaymentInfo</li>
                          <li class="ecommerce-items">InitiateCheckout</li>
                          <li class="ecommerce-items">Purchase.. etc</li>
                        </ul>
                    </div>';
        }
        return $html;
    }
}
