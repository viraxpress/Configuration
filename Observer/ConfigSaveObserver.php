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

namespace ViraXpress\Configuration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ViraXpress\Cms\Model\NodeVersionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ViraXpress\Configuration\Helper\Data;
use Magento\Framework\Shell;
use Magento\Theme\Model\ThemeFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\RequestInterface;

class ConfigSaveObserver implements ObserverInterface
{

    /**
     * frontend theme code
     */
    public const THEME_CODE = 'ViraXpress/frontend';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var NodeVersionFactory
     */
    protected $nodeVersionFactory;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param DirectoryList $directory
     * @param ThemeFactory $themeFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param NodeVersionFactory $nodeVersionFactory
     * @param ThemeProviderInterface $themeProvider
     * @param RequestInterface $request
     * @param Data $dataHelper
     * @param Shell $shell
     */
    public function __construct(
        DirectoryList $directory,
        ThemeFactory $themeFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        NodeVersionFactory $nodeVersionFactory,
        ThemeProviderInterface $themeProvider,
        RequestInterface $request,
        DesignInterface $design,
        Data $dataHelper,
        Shell $shell
    ) {
        $this->design = $design;
        $this->directory = $directory;
        $this->dataHelper = $dataHelper;
        $this->themeFactory = $themeFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->request = $request;
        $this->nodeVersionFactory = $nodeVersionFactory;
        $this->shell = $shell;
    }

    /**
     * Executes the observer when the configuration section is saved.
     *
     * @param Observer $observer The observer instance containing event data.
     * @return void
     */
    public function execute(Observer $observer)
    {
        $storeId = $observer->getEvent()->getStore();
        if (!$storeId) {
            $websiteId = $observer->getData('website');
            $website = $this->storeManager->getWebsite($websiteId);
            if ($website) {
                $storeIds = $website->getStoreIds();
                foreach ($storeIds as $key => $storeId) {
                    $this->executeByStore($storeId);
                }
            }
        } else {
            $this->executeByStore($storeId);
        }
    }

    /**
     * Executes the tailwind command.
     *
     * @param mixed $storeId.
     * @return void
     */
    private function executeByStore($storeId) {
        $themePath = $this->dataHelper->checkThemePathByStoreId($storeId);
        if (!$themePath) {
            $themePath = $this->dataHelper->checkThemePath();
        }

        if ($storeId) {
            $primaryColor = $this->scopeConfig->getValue('viraxpress_config/colors/primary_color', ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            $primaryColor = $this->scopeConfig->getValue('viraxpress_config/colors/primary_color', ScopeInterface::SCOPE_STORE);
        }

        $nodePath = $this->scopeConfig->getValue('viraxpress_config/general/server_npm_node_path');
        if (!empty($nodePath) && $themePath) {
            $newEnvPath = $this->getCurrentEnvPath() . ":$nodePath";
            $phpVariables = json_encode(
                [
                    "primary" => $primaryColor,
                    "secondary" => $primaryColor,
                    "root" => $this->directory->getRoot() . "/pub/vx/".$themePath."/web/tailwind/tailwind.config.js",
                    "npmrun" => "cd " . $this->directory->getRoot() . "/pub/vx/".$themePath."/web/tailwind && npm run prod"
                ]
            );
            $npmCommand = "node ".$this->directory->getRoot()."/pub/vx/".$themePath."/web/tailwind/store-config.js '{$phpVariables}'";
            putenv('PATH=' . getenv('PATH') . ':' . $nodePath);
            $result = $this->shell->execute($npmCommand, [], ['PATH' => $newEnvPath]);
        }
    }

    private function getCurrentEnvPath(): string
    {
        return getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin';
    }
}