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

Install using the steps below. Composer installation TBD. 

### 1. Manual Installation (app/code)

```bash
cd <magento-root>/app/code

# create the Cresco folder if it doesn't exist
mkdir -p Cresco
cd Cresco

# download the repository's zip file
wget https://github.com/cresco-development/magento2-extensions/archive/refs/heads/master.zip
unzip master.zip -d .
rm master.zip
mv magento2-extensions-master Extensions
ls Extensions
# should show registration.php, etc/, Plugin/, Observer/, composer.json

cd <magento-root>

# Ensure permissions are correct, you may or may not need to run these
sudo chown -R <web-user>:<web-group> app/code/Cresco/Extensions
find app/code/Cresco/Extensions -type f -exec chmod 644 {} \;  # files
find app/code/Cresco/Extensions -type d -exec chmod 755 {} \;  # directories

# enable the module
bin/magento module:enable Cresco_Extensions
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```