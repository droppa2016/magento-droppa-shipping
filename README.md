# Magento 2 Droppa Shipping App

```
Donate link: https://droppa.co.za/magento-shipping-plugin
Tags: Shipping, Courier
Tested up to: 4.0.1
Stable tag: 1.0.7
Requires PHP: 7.0
```

## Description

> The Droppa Shipping App allows you to ship goods everywhere across South Africa.
* This Shipping method uses Express Courier to deliver your parcels within 24 to 72 hours.
* Our Express service is available nationwide.
* No collections/deliveries on weekends

## System Requirements

* PHP 7.2 is required to run the extension.
* PHP 5.6 - 7.0 can be supported on request.

## Before Installation

* All App users must be clients with Droppa Group and should have a Business account along with an [API key and Service ID]. Please contact Droppa Group administration to help set it up - itsupport@droppa.co.za
* Please make sure that App extension has been installed in your Store.
* NB: Please make sure that your store configuration is set to South Africa & Currency to ZAR as currently the Droppa Shipping App supports shipping for addresses & postal codes valid for South Africa.

## Installation

* Navigate to the Marketplace https://marketplace.magento.com/extensions/accounting-finance.html, search for Droppa Shipping and download the App.
* Open your hosting site on where your platform is hosted and open up App > Code folder and save the Droppa Shipping App in it.
* Open up your terminal pointing to the store project and run the following commands:

```
php ./bin/magento module:enable droppa_droppashipping
php ./bin/magento setup:upgrade
php ./bin/magento setup:di:compile
php ./bin/magento setup:static-content:deploy -f
php ./bin/magento cache:clean
php ./bin/magento cache:flush
```

* Navigate to your Magento Admin site > Stores > Configurations > Sales > Delivery Methods.
* Enable the Droppa Shipping App, place in your API and Service keys generated from the Dropp Group platform by our developers. itsupport@droppa.co.za

### Post-Installation

* Enable the Droppa Shipping app, place in your API and Service keys generated from the Dropp Group platform by our developers. itsupport@droppa.co.za by navigating to your Admin dashboard site > Stores > Configurations > Sales > Delivery Methods.
* Take Note the Droppa Shipping App works only for South Africa Shipping Addresses: Example of South African Addresses & their postal codes:
* Below are some of the Province Postal Codes but not limited to: Provinces -> Gauteng (2001), Mpumalanga (1201), North-West (2745), FreeState (9301), Western Cape (8001), Eastern Cape (5605), Kwazulu-Natal (3201), Limpopo (0699) & Northern Cape (8301)


## Frequently Asked Questions

1. Does the Droppa Shipping App requires an authorization access key?

- Yes. In most cases, whenever working with Apps, an Oauth key is required to allow data integration with the application. The API and Service Keys are designated to the retail store owner and are attached to the information within.

2. How long does it take to activate the App.

- As soon as the App is downloaded and activated on the Magento dashboard, users can communicate with the support team itsupport@droppa.co.za for instructions on activating the App.

3. Does the App work for all countries on shipping destinations?

- No. Droppa Shipping App currently supports shipping for addresses within South Africa including their postal codes. So the store owner needs to set Store country to South Africa & Currency to ZAR on the store configuration dashboard.

## Screenshots

1. Settings section for adding up API and Service keys.
![Settings section for adding up API and Service keys](https://user-images.githubusercontent.com/73278719/112615171-d2b85b00-8e2a-11eb-9edb-63cab4e29ee1.PNG)

2. The output at Checkout page.

![The output at Checkout page](https://user-images.githubusercontent.com/73278719/112615206-dea41d00-8e2a-11eb-8cde-55d04d4bd2a7.PNG)



## Support

* Should you encounter any issue with the App, please revert back to our support developer team itsupport@droppa.co.za

## Changelog

### 1.0.0

* This is the official release of this version.

### 1.0.1 And 1.0.2

* Updated our App to work with all payment gateways

### 1.0.3

* Modified our storage system to save the API and Service Key.

### 1.0.4 And 1.0.5

* Added a new rate calculator service that determines the quotation based on the product mass only.

### 1.0.6 And 1.0.7

* Re-evaluated the mass rate calculator and updated the service.