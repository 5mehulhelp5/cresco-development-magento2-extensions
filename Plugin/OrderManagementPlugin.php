<?php
namespace Cresco\Extensions\Plugin;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ProductRepository;

class OrderManagementPlugin
{
    private $logger;
    private $date;
    private $productRepository;

    public function __construct(
        LoggerInterface $logger,
        DateTime $date,
        ProductRepository $productRepository
    ) {
        $this->logger = $logger;
        $this->date = $date;
        $this->productRepository = $productRepository;
    }

    /**
     * Update order and order items' products updated_at
     */
    public function afterSave(Order $subject, Order $result)
    {
        try {
            $now = $this->date->gmtDate();
            $this->logger->debug("[Cresco_Extensions] OrderManagementPlugin triggered for order ID: " . $subject->getId() . ", setting updated_at to $now");

            // Force the order updated_at as dirty and save
            $subject->setData('updated_at', $now);
            $subject->setOrigData('updated_at', null); // Mark field as changed
            $subject->getResource()->save($subject);
            $this->logger->debug("[Cresco_Extensions] Successfully updated order updated_at for order ID: " . $subject->getId());

            // Always update products in order items
            foreach ($subject->getAllItems() as $item) {
                try {
                    $productId = $item->getProductId();
                    if (!$productId) {
                        $this->logger->debug("[Cresco_Extensions] Order item ID " . $item->getItemId() . " has no product ID, skipping");
                        continue;
                    }

                    $product = $this->productRepository->getById($productId);
                    $product->setUpdatedAt($now);
                    $this->productRepository->save($product);
                    $this->logger->debug("[Cresco_Extensions] Updated product updated_at for product ID: $productId (order item ID " . $item->getItemId() . ")");
                } catch (\Exception $e) {
                    $this->logger->error("[Cresco_Extensions] Error updating product ID " . $item->getProductId() . ": " . $e->getMessage());
                }
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("[Cresco_Extensions] OrderManagementPlugin::afterSave - Error for order ID " . $subject->getId() . ": " . $e->getMessage());
            return $result;
        }
    }
}
