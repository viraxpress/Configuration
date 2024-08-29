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
use Magento\Store\Api\StoreRepositoryInterface;

class ConfigPlugin
{

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

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
     * @param StoreRepositoryInterface $storeRepository
     * @param DesignInterface $design
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
        StoreRepositoryInterface $storeRepository,
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
        $this->storeRepository = $storeRepository;
        $this->nodeVersionFactory = $nodeVersionFactory;
        $this->shell = $shell;
    }

    /**
     * Plugin to modify the behavior of the save method in Magento\Config\Model\Config class.
     *
     * @param \Magento\Config\Model\Config $subject The original config model instance.
     * @param \Closure $proceed The original method implementation.
     * @return mixed
     */
    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        $params = $subject->getData('groups');
        $themePath = $this->dataHelper->checkThemePath();
        /* Get Current Store ID */
        $storeId = $this->storeManager->getStore()->getId();
        if (isset($params['colors'])) {
            $primaryConfigColor = $this->scopeConfig->getValue('viraxpress_config/colors/primary_color', ScopeInterface::SCOPE_STORE);
            $primaryColor = $params['colors']['fields']['primary_color']['value'];
            if ($primaryColor != $primaryConfigColor) {
                $nodePath = $this->scopeConfig->getValue('viraxpress_config/general/server_npm_node_path');
                if (empty($themePath)) {
                    $storeCode = $subject->getData('scope');
                    $storeId = (int)$this->storeRepository->get($storeCode)->getId();
                    $themePath = $this->dataHelper->checkThemePathByStoreId($storeId);
                }
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
                    $result = $this->shell->execute($npmCommand, [], ['PATH' => $newEnvPath]);
                }
            }
        }
        return $proceed();
    }

    /**
     * Get current environment PATH
     *
     * @return string
     */
    private function getCurrentEnvPath(): string
    {
        return getenv('PATH') ?: '';
    }
}
