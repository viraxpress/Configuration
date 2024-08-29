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

namespace ViraXpress\Configuration\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;

class Activate extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'ViraXpress_Configuration::activate';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var ReinitableConfigInterface
     */
    protected $config;

    /**
     * Application config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $appConfig;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Activate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $configWriter
     * @param JsonFactory $resultJsonFactory
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        Config $resourceConfig,
        ReinitableConfigInterface $config,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        JsonFactory $resultJsonFactory,
        Curl $curl
    ) {
        $this->resourceConfig    = $resourceConfig;
        $this->appConfig         = $config;
        $this->storeManager      = $storeManager;
        $this->configWriter      = $configWriter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->curl              = $curl;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();
        $stores = $this->storeManager->getStores();
        $baseUrls = [];
        $apiUrl = "http://103.242.236.166:8001/api/method/viraxpress.api.sales_order_tracking";
        $authKey = "token 78db714cd793755:ecb055a8db1cda4";
        $headers = [
            "Authorization" => $authKey,
            "Content-Type" => "application/json"
        ];

        foreach ($stores as $store) {
            $storeid = $store->getId();
            $baseurl = $store->getBaseUrl();
            preg_match('/^(?:https?:\/\/)?(?:www\.)?([^\/]+)/', $baseurl, $matches);
            $hostname = isset($matches[1]) ? $matches[1] : '';
            $host = preg_replace('/^www\./', '', $hostname);
            $baseUrls[] = $host;
        }

        $domains = array_unique($baseUrls);

        $uniqueBaseUrls = array_unique($baseUrls);

        $result = $this->activateUser($apiUrl, $headers, $params, $domains);
        if ($result['success']) {
            $result['active'] = true;
            $configRow = [];

            $configRow['active'] = 1;
            $configRow['register_name'] = $params['name'];
            $configRow['register_email'] = $params['email'];
            if (isset($result['key']) && $result['key']) {
                $configRow['activation_token'] = $result['key'];
                $activationToken = $result['key'];
            }
            if (is_array($configRow)) {
                foreach ($configRow as $config => $configValue) {
                    $configPath = 'viraxpress_config/module/'.$config;

                    $this->configWriter->save($configPath, $configValue, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
                }
            }
            $resultJson->setData([
                'isSuccess' => true,
                'active' => true,
                'key' => $activationToken,
                'content' => 'ViraXpress is successfully activated.'
            ]);

            $this->appConfig->reinit();
        } else {
            $resultJson->setData([
                'isSuccess' => false,
                'active' => false,
                'content' => $result['message']
            ]);
        }
        return $resultJson;
    }

    /**
     * Activates a user via API request.
     *
     * @param string $apiUrl The URL of the API endpoint for activating the user.
     * @param array $headers The headers to be included in the API request.
     * @param array $params An array containing the parameters required for activating the user.
     * @param array $domains An array of domains associated with the user.
     * @return array The response from the API request.
     */
    public function activateUser($apiUrl, $headers, $params, $domains)
    {
        $domains = array_values($domains);
        $fields = [
            "ip" => $params['domain'],
            "customer_email" => $params['email'],
            "domain" => $domains
        ];
        try {
            $this->curl->setHeaders($headers);
            $this->curl->post($apiUrl, json_encode($fields));
            $result = $this->curl->getBody();
            if (!empty($result)) {
                if ($this->isJson($result)) {
                    $decodedResult = json_decode($result);
                    if ($decodedResult->message->status == 'Success') {
                        $outputResult = ['success' => true];
                        $outputResult['key'] = $decodedResult->message->token;
                    } else {
                        $outputResult = ['success' => false];
                        $outputResult['message'] = 'Invalid Credentials';
                    }
                }
            } else {
                $outputResult = ['success' => false];
                $outputResult = ['message' => 'Invalid Credentials'];
            }
        } catch (\Exception $e) {
            $outputResult = ['success' => false];
            $outputResult['message'] = $e->getMessage();
        }
        return $outputResult;
    }

    /**
     * Function isJson to check if a string is jSon
     *
     * @param string $string string
     *
     * @return bool
     */
    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
