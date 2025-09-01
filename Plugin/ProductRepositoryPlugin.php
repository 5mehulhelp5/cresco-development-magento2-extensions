<?php
namespace Cresco\Extensions\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Api\SearchResultsInterface;
use Psr\Log\LoggerInterface;

class ProductRepositoryPlugin
{
    private $configurableResource;
    private $logger;

    public function __construct(
        Configurable $configurableResource,
        LoggerInterface $logger
    ) {
        $this->configurableResource = $configurableResource;
        $this->logger = $logger;
    }

    /**
     * Add extension attributes to single product
     */
    public function afterGet(ProductRepositoryInterface $subject, ProductInterface $product)
    {
        try {
            $this->logger->debug('Cresco ProductRepositoryPlugin::afterGet - Processing product ID: ' . $product->getId());
            $this->addConfigurableProductIds($product);
            return $product;
        } catch (\Exception $e) {
            $this->logger->error('Cresco ProductRepositoryPlugin::afterGet - Error: ' . $e->getMessage());
            return $product;
        }
    }

    /**
     * Add extension attributes to product list
     */
    public function afterGetList(ProductRepositoryInterface $subject, SearchResultsInterface $searchResults)
    {
        try {
            $this->logger->debug('Cresco ProductRepositoryPlugin::afterGetList - Processing product list with ' . count($searchResults->getItems()) . ' items');
            foreach ($searchResults->getItems() as $product) {
                $this->addConfigurableProductIds($product);
            }
            return $searchResults;
        } catch (\Exception $e) {
            $this->logger->error('Cresco ProductRepositoryPlugin::afterGetList - Error: ' . $e->getMessage());
            return $searchResults;
        }
    }

    /**
     * Add configurable product IDs to simple products
     */
    private function addConfigurableProductIds(ProductInterface $product)
    {
        if ($product->getTypeId() === 'simple') {
            try {
                $configurableProductIds = $this->configurableResource->getParentIdsByChild($product->getId());
                $extensionAttributes = $product->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('\Magento\Catalog\Api\Data\ProductExtensionFactory')
                        ->create();
                }
                $extensionAttributes->setConfigurableProductIds($configurableProductIds ?: []);
                $product->setExtensionAttributes($extensionAttributes);
            } catch (\Exception $e) {
                $this->logger->error('Cresco - Error adding configurable_product_ids to product ' . $product->getId() . ': ' . $e->getMessage());
            }
        }
    }
}