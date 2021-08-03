<?php

namespace Droppa\DroppaShipping\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Droppa\DroppaShipping\Model\Curl;
use Psr\Log\LoggerInterface;

class PaymentsObserver implements ObserverInterface
{
    public $resources;
    public $bookingObjectID;
    protected $logger;
    protected $scopeConfig;
    // protected $PROD_CONFIRM_PAYMENT_SERVICE = 'https://droppergroup.co.za/droppa/services/plugins/confirm/';
    protected string $PROD_CONFIRM_PAYMENT_SERVICE = 'https://www.droppa.co.za/droppa/services/plugins/confirm/';

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $adminAPI = $this->scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES);
        $adminServ = $this->scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES);

        if (!$adminAPI && !$adminServ) {
            return false;
        } else {
            $useCurlObject = new Curl($adminAPI, $adminServ);

            $objectManager     = ObjectManager::getInstance();
            $this->resources   = $objectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection        = $this->resources->getConnection();

            $installCustomTable = $this->resources->getTableName('droppa_booking_object');
            $LastSavedOID       = "SELECT booking_id FROM $installCustomTable ORDER BY id DESC LIMIT 1";
            $_collect           = $connection->fetchAll($LastSavedOID);
            $connection->query($LastSavedOID);

            foreach ($_collect as $valueBookingId) {
                $this->bookingObjectID = $valueBookingId['booking_id'];
            }

            return $useCurlObject->curlEndpoint($this->PROD_CONFIRM_PAYMENT_SERVICE . $this->bookingObjectID, '', 'POST');
        }
    }
}