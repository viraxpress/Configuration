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

namespace ViraXpress\Configuration\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class CheckoutThemeSwitcherPlugin
{
    /**
     * @var Http|RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param DesignInterface $design
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        DesignInterface $design,
        ThemeProviderInterface $themeProvider
    ) {
        $this->design = $design;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Before execute function
     *
     * @param ActionInterface $subject
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $checkoutThemeSwitcher = $this->scopeConfig->getValue('viraxpress_config/checkout_theme_switcher/default_checkout', ScopeInterface::SCOPE_STORE);
        $excludedPaths = [
            '/multishipping/checkout/login/',
            '/multishipping/checkout/register/'
        ];
        $currentPath = $this->request->getRequestUri();
        if ($checkoutThemeSwitcher) {
            $themeSwitcher = explode(",", $checkoutThemeSwitcher);
            foreach ($themeSwitcher as $config) {
                if (!in_array($currentPath, $excludedPaths)) {
                    if (!empty($config) && $this->isValidRouteControllerAction($this->request, $config)) {
                        $this->design->setDesignTheme($this->getTheme());
                    }
                }
            }
        }
    }

    /**
     * Get ViraXpress theme.
     *
     * @return ThemeInterface The ViraXpress theme.
     */
    private function getTheme(): ThemeInterface
    {
        $themeFullPath = $this->scopeConfig->getValue('viraxpress_config/checkout_theme_switcher/theme_full_path', ScopeInterface::SCOPE_STORE);
        return $this->themeProvider->getThemeByFullPath($themeFullPath);
    }

    /**
     * Check the valid controller action.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $urlSegment
     * @return bool
     */
    private function isValidRouteControllerAction(Http $request, string $urlSegment): bool
    {
        $urlSegmentParts = explode('/', trim($urlSegment, '/'), 3);
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
}
