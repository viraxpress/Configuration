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

namespace ViraXpress\Configuration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\ThemeFactory;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class Data extends AbstractHelper
{

    /**
     * frontend theme code
     */
    public const THEME_CODE = 'ViraXpress/frontend';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param DesignInterface $design
     * @param StoreManagerInterface $storeManager
     * @param ThemeFactory $themeFactory
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        DesignInterface $design,
        StoreManagerInterface $storeManager,
        ThemeFactory $themeFactory,
        ThemeProviderInterface $themeProvider
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->design = $design;
        $this->storeManager = $storeManager;
        $this->themeFactory = $themeFactory;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Checks the fallback theme for current page.
     *
     * @return bool.
     */
    public function isViraXpressEnable()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $themeId = $this->design->getConfigurationDesignTheme('frontend', ['store' => $storeId]);
        $theme = $this->themeProvider->getThemeById($themeId);
        $parentThemeId = $theme->getParentId();

        if ($this->design->getDesignTheme()->getCode() != self::THEME_CODE) {
            if (!$parentThemeId) {
                return false;
            }
            $parentTheme = $this->themeFactory->create()->load($parentThemeId);
            if ($parentTheme->getThemePath() != self::THEME_CODE) {
                return false;
            }
        }

        $checkoutControllers = $this->scopeConfig->getValue('viraxpress_config/theme_fallback/default_checkout', ScopeInterface::SCOPE_STORE);
        $currentPath = $this->request->getRequestUri();
        $excludedPaths = [
            '/multishipping/checkout/login/',
            '/multishipping/checkout/register/'
        ];
        $isViraXpressTheme = true;
        if ($checkoutControllers) {
            $fallback = explode(",", $checkoutControllers);
            foreach ($fallback as $config) {
                if (!in_array($currentPath, $excludedPaths)) {
                    if (!empty($config) && $this->isValidRouteControllerAction($this->request, $config)) {
                        $isViraXpressTheme =  false;
                    }
                }
            }
        }
        return $isViraXpressTheme;
    }

    /**
     * Check valid controller action.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $urlSegment
     * @return bool
     */
    public function isValidRouteControllerAction(Http $request, string $urlSegment): bool
    {
        $urlSegmentParts = explode('/', trim($urlSegment, '/'), 3);
        $isFallback = false;
        switch (count($urlSegmentParts)) {
            case 1:
                return $this->isValidRoute($request, $urlSegmentParts[0]);
            case 2:
                return $this->isValidRoute($request, $urlSegmentParts[0]) &&
                       $this->isValidController($request, $urlSegmentParts[1]);
            case 3:
                return $this->isValidRoute($request, $urlSegmentParts[0]) &&
                       $this->isValidController($request, $urlSegmentParts[1]) &&
                       $this->isValidAction($request, $urlSegmentParts[2]);
            default:
                return $this->isValidSegment($request, $urlSegment);
        }
    }

    /**
     * Check valid route.
     *
     * @param Http $request
     * @param string $routeName
     * @return bool
     */
    private function isValidRoute(Http $request, string $routeName): bool
    {
        return $routeName === $request->getRouteName();
    }

    /**
     * Check valid controller.
     *
     * @param Http $request
     * @param string $controllerName
     * @return bool
     */
    private function isValidController(Http $request, string $controllerName): bool
    {
        return $controllerName === $request->getControllerName();
    }

    /**
     * Check valid action.
     *
     * @param Http $request
     * @param string $actionName
     * @return bool
     */
    private function isValidAction(Http $request, string $actionName): bool
    {
        return $actionName === $request->getActionName();
    }

    /**
     * Check valid segment.
     *
     * @param Http $request
     * @param string $urlSegment
     * @return bool
     */
    private function isValidSegment(Http $request, string $urlSegment): bool
    {
        $actualUrl = $request->getRequestUri();
        return strpos($actualUrl, $urlSegment) !== false;
    }

    /**
     * Check if the current theme or its parent matches the specified theme path.
     *
     * @return mixed
     */
    public function checkThemePath()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $themeId = $this->design->getConfigurationDesignTheme('frontend', ['store' => $storeId]);
        $theme = $this->themeProvider->getThemeById($themeId);
        $parentThemeId = $theme->getParentId();
        if ($this->design->getDesignTheme()->getCode() != self::THEME_CODE &&
            (!$parentThemeId || $this->themeFactory->create()->load($parentThemeId)->getThemePath() != self::THEME_CODE)) {
            return false;
        } else {
            return $theme->getThemePath();
        }
    }

    /**
     * Check if the current theme or its parent matches the specified store theme path.
     *
     * @param int $storeId
     * @return mixed
     */
    public function checkThemePathByStoreId($storeId)
    {
        $themeId = $this->design->getConfigurationDesignTheme('frontend', ['store' => $storeId]);
        $theme = $this->themeProvider->getThemeById($themeId);
        $parentThemeId = $theme->getParentId();
        if (!empty($this->design->getDesignTheme()->getCode()) && $this->design->getDesignTheme()->getCode() != self::THEME_CODE &&
            (!$parentThemeId || $this->themeFactory->create()->load($parentThemeId)->getThemePath() != self::THEME_CODE)) {
            return false;
        } else {
            return self::THEME_CODE;
        }
    }
}
