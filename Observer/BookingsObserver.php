<?php

namespace Droppa\DroppaShipping\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductRepository;
use Droppa\DroppaShipping\Model\Parcel;
use Droppa\DroppaShipping\Model\Curl;
use Psr\Log\LoggerInterface;

class BookingsObserver extends Template implements ObserverInterface
{
    public $booking_endpoint = 'https://www.droppa.co.za/droppa/services/plugins/book';
    // public $booking_endpoint = 'https://droppergroup.co.za/droppa/services/plugins/book';
    protected $logger;
    protected $storeManager;
    protected $scopeConfig;
    protected $customer;
    protected $objectManager;
    protected $productRepository;

    public function __construct(
        LoggerInterface $logger,
        Session $customer,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        ProductRepository $productRepository
    ) {
        $this->logger = $logger;
        $this->customer = $customer;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
    }

    public function bookingPluginAttributes($_length, $_breadth, $_height, $_mass): array
    {
        $parcels = new Parcel($_mass, $_breadth, $_height, $_length);

        return [
            "parcel_length" => $parcels->getLength(),
            "parcel_breadth" => $parcels->getWidth(),
            "parcel_height" => $parcels->getHeight(),
            "parcel_mass" => $parcels->getItemMass()
        ];
    }

    public function execute(Observer $observer)
    {
        $order              = $observer->getEvent()->getOrder();
        $objectManager      = ObjectManager::getInstance();
        $customerSession    = $objectManager->get('\Magento\Customer\Model\Session');
        $customerName       = $customerSession->getCustomer()->getName();
        $customerEmail      = $customerSession->getCustomer()->getEmail();
        $customerCompany    = $order->getBillingAddress()->getCompany();

        $custPhone = ($order->getShippingAddress()->getTelephone() ?
            $order->getShippingAddress()->getTelephone() :
            $order->getBillingAddress()->getTelephone());
        $dropOffPinCode = ($order->getShippingAddress()->getPostcode() ?
            $order->getShippingAddress()->getPostcode() :
            $order->getBillingAddress()->getPostcode());
        $dropOffCity = ($order->getShippingAddress()->getCity() ?
            $order->getShippingAddress()->getCity() :
            $order->getBillingAddress()->getCity());
        $dropOffRegion = ($order->getShippingAddress()->getRegion() ?
            $order->getShippingAddress()->getRegion() :
            $order->getBillingAddress()->getRegion());
        $dropOffAddress = ($order->getShippingAddress()->getStreet() ?
            $order->getShippingAddress()->getStreet() :
            $order->getBillingAddress()->getStreet());

        $adminStoreName = $this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_WEBSITES);
        $adRegion = $this->scopeConfig->getValue('general/store_information/region_id', ScopeInterface::SCOPE_WEBSITES);
        $adminPCode = $this->scopeConfig->getValue('general/store_information/postcode', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreCity = $this->scopeConfig->getValue('general/store_information/city', ScopeInterface::SCOPE_WEBSITES);
        $adStr1 = $this->scopeConfig->getValue('general/store_information/street_line1', ScopeInterface::SCOPE_WEBSITES);
        $adStr2 = $this->scopeConfig->getValue('general/store_information/street_line2', ScopeInterface::SCOPE_WEBSITES);

        $adminAPI = $this->scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES);
        $adminServ = $this->scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES);
        $base_shipping_amount = '';
        $_bookingDimensions = array();

        foreach ($order as $item) {
            $base_shipping_amount = $item['base_shipping_amount'];
            $productWeight = $item['weight'];
            $_bookingDimensions[] = $this->bookingPluginAttributes(0, 0, 0, $productWeight);
        }

        $getNewSurburb = new Curl($adminAPI, $adminServ);
        $fromSuburbResponse = $getNewSurburb->curlEndpoint("https://www.droppa.co.za/droppa/services/parties/suburb/{$adminPCode}", '', 'GET');
        $ToSuburbResponse = $getNewSurburb->curlEndpoint("https://www.droppa.co.za/droppa/services/parties/suburb/{$dropOffPinCode}", '', 'GET');

        $pickUpPostalCodeSuburb = json_decode($fromSuburbResponse, true);
        $dropOffPostalCodeSuburb = json_decode($ToSuburbResponse, true);

        $_quote_body = [
            "serviceId" => $adminServ,
            "platform" => "Magento",
            "pickUpPCode" => $adminPCode,
            "dropOffPCode" => $dropOffPinCode,
            "fromSuburb" => $pickUpPostalCodeSuburb['suburb'],
            "toSuburb" => $dropOffPostalCodeSuburb['suburb'],
            "province" => $adRegion,
            "destinationProvince" => $dropOffRegion,
            "pickUpAddress" => $adStr1 . ', ' . $adminStoreCity . ', ' . $adminPCode . ', ' . $adRegion,
            "dropOffAddress" => implode(',', $dropOffAddress) . ', ' . $dropOffPinCode . ', ' . $dropOffRegion,
            "pickUpCompanyName" => $adminStoreName,
            "dropOffCompanyName" => $customerCompany,
            "pickUpUnitNo" => $adStr2,
            "dropOffUnitNo" => '',
            "customerName" => $customerName,
            "customerPhone" => $custPhone,
            "customerEmail" => $customerEmail,
            "instructions" => 'Magento Default Instructions',
            "price" => $base_shipping_amount,
            "parcelDimensions" => $_bookingDimensions
        ];

        if (!$adminAPI && !$adminServ) {
            return false;
        } else {
            $useCurlObject = new Curl($adminAPI, $adminServ);

            $response = $useCurlObject->curlEndpoint($this->booking_endpoint, $_quote_body, 'POST');
            $responseResults = json_decode($response, true);

            $resources = $objectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            $connection = $resources->getConnection();

            if ((float) $responseResults['price'] >= '05.00') {
                $installCustomTable = $resources->getTableName('droppa_booking_object');
                $BookingId = "INSERT INTO " . $installCustomTable . "(booking_id) VALUES ('" . $responseResults['oid'] . "')";
                return $connection->query($BookingId);
            }
        }
    }
}