<?php
namespace Cresco\Extensions\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class SimpleProductUpdatePlugin
{
    private $productRepository;
    private $date;
    private $logger;

    public function __construct(
        ProductRepository $productRepository,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->date = $date;
        $this->logger = $logger;
    }

    /**
     * After saving configurable product children, update updated_at for added or removed simples
     */
    public function afterSave(Configurable $subject, $result, $product, $associatedProductIds = [])
    {
        $this->logger->debug("[Cresco_Extensions] SimpleProductUpdatePlugin triggered for configurable product ID: " . $product->getId());
        $now = $this->date->gmtDate();

        try {
            $existingChildIds = $subject->getChildrenIds($product->getId())[0] ?? [];
            $removedIds = array_diff($existingChildIds, $associatedProductIds);
            $addedIds = array_diff($associatedProductIds, $existingChildIds);

            foreach ($removedIds as $childId) {
                $childProduct = $this->productRepository->getById($childId);
                $childProduct->setUpdatedAt($now);
                $this->productRepository->save($childProduct);
                $this->logger->debug("[Cresco_Extensions] Updated updated_at for removed simple product ID: $childId");
            }

            foreach ($addedIds as $childId) {
                $childProduct = $this->productRepository->getById($childId);
                $childProduct->setUpdatedAt($now);
                $this->productRepository->save($childProduct);
                $this->logger->debug("[Cresco_Extensions] Updated updated_at for added simple product ID: $childId");
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('[Cresco_Extensions] Error updating simple products: ' . $e->getMessage());
            return $result;
        }
    }
}