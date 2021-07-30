<?php

namespace Droppa\DroppaShipping\Model\Carrier;

use Droppa\DroppaShipping\Model\Quotes;
use Droppa\DroppaShipping\Model\Curl;
use Exception;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

if (!class_exists('DroppaShipping')) {

    class DroppaShipping extends AbstractCarrier implements CarrierInterface
    {
        protected $_code = 'droppashipping';
        protected $rateResultFactory;
        protected $rateMethodFactory;
        protected $rateErrorFactory;
        protected $_scopeConfig;
        protected $logger;
        protected $cart;
        public string $_quote_endpoint = 'https://droppergroup.co.za/droppa/services/plugins/quotes';
        // public string $_quote_endpoint = 'https://www.droppa.co.za/droppa/services/plugins/quotes';
        public array $_quote_body = [];
        public float $_total_amount;

        public function __construct(
            ScopeConfigInterface $scopeConfig,
            ErrorFactory $rateErrorFactory,
            LoggerInterface $logger,
            ResultFactory $rateResultFactory,
            MethodFactory $rateMethodFactory,
            Cart $cart,
            array $data = array()
        ) {
            $this->rateResultFactory = $rateResultFactory;
            $this->rateMethodFactory = $rateMethodFactory;
            $this->rateErrorFactory = $rateErrorFactory;
            $this->cart = $cart;
            $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

            parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        }

        public function getAllowedMethods(): array
        {
            return [$this->_code => $this->getConfigData('name')];
        }

        private function getShippingPrice(): float
        {
            return $this->getFinalPriceWithHandlingFee($this->getConfigData('price'));
        }

        public function collectRates(RateRequest $request)
        {
            if (!$this->getConfigFlag('active')) {
                return false;
            }

            $result = $this->rateResultFactory->create();

            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $_fullDimensionContents = [
                'parcel_length' => (int)$request->getPackageDepth(),
                'parcel_breadth' => (int)$request->getPackageWidth(),
                'parcel_height' => (int)$request->getPackageHeight(),
                'parcel_mass' => (int)$request->getPackageWeight()
            ];

            $this->_quote_body = $this->quotesPluginAttributes(
                $request->getOrigPostcode(),
                $request->getDestPostcode(),
                $request->getPackageWeight(),
                $_fullDimensionContents
            );

            $isModuleActive = $this->_scopeConfig->getValue('carriers/droppashipping/active', ScopeInterface::SCOPE_WEBSITES);
            $checkAPIKey = $this->_scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES);
            $checkServiceKey = $this->_scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES);

            if ($isModuleActive === 0 || $isModuleActive == "0" && $checkAPIKey == 'undefined' && $checkServiceKey == 'undefined') {
                $this->logger->info("Deactived Module = {$isModuleActive}, API Key = {$checkServiceKey}, Service Key = {$checkAPIKey}");
                return false;
            }

            if ($isModuleActive === "1" || $isModuleActive === 1) {
                $useCurlObject = new Curl($checkAPIKey, $checkServiceKey);

                $response = $useCurlObject->curlEndpoint($this->_quote_endpoint, $this->_quote_body, 'POST');
                $object = '';
                // && $this->cart->getQuote()->getId()
                if (isset($response)) {
                    $object = json_decode($response, true);

                    $this->_total_amount = ($this->getShippingPrice() ? $this->getShippingPrice() : $object['amount']);

                    $method->setPrice($this->_total_amount);
                    $method->setCost($this->_total_amount);

                    try {
                        return $result->append($method);
                    } catch (Exception $e) {
                        $this->logger->error($e->getMessage(), $e->getCode());
                    }
                }
            }
        }

        public function quotesPluginAttributes(
            $_pickUpPCode,
            $_dropOffPCode,
            $_product_total_mass,
            $_parcelDimensionsArrayHolder
        ): array {
            (array)$_get_product_attributes = new Quotes($_pickUpPCode, $_dropOffPCode, $_product_total_mass);

            return [
                "pickUpCode" => $_get_product_attributes->getPickUpCode(),
                "dropOffCode" => $_get_product_attributes->getDropOffCode(),
                "mass" => $_get_product_attributes->getWeight(),
                "parcelDimensions" => [$_parcelDimensionsArrayHolder]
            ];
        }
    }
}