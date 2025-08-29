# Cresco Extensions for Magento 2

## Features

- Includes `configurable_product_ids` extension attribute to simple products when queried via REST API
- Updates the `updated_at` of simple products when they are added or removed from a configurable product
- Updates the `updated_at` of products within an order when an order is created or shipment made
- Updated the `updated_at` of orders when an order shipment is created or updated

## Purpose

To retrieve updates to products (especially stock changes) and orders via regular polling of products and orders using `updated_at` being `greater_than` the last polling time.
The inclusion of `configurable_product_ids` and updating of simple products when removed or added from configurable products allows for more efficient product grouping for integrators when polling used.

## Requirements

- Magento 2.2+
- PHP 7.4+ (depending on your Magento version)
- Composer (optional, for Composer installation)

## Installation

Choose either a manual or composer (vendor) installation below

### 1. Manual Installation (app/code)

```bash
cd <magento-root>/app/code

# create the Cresco folder if it doesn't exist
mkdir -p Cresco
cd Cresco

# clone the repo directly into 'Extensions'
git clone https://github.com/<username>/magento2-extensions.git Extensions

# verify folder structure
ls Extensions
# should show registration.php, etc/, Plugin/, Observer/, composer.json

# enable the module
bin/magento module:enable Cresco_Extensions
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### 2. Composer Installation (optional)
```bash
cd <magento-root>
composer require cresco/extensions
bin/magento module:enable Cresco_Extensions
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```