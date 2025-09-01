<?php
namespace Cresco\Extensions\Plugin;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class SimpleProductUpdatePlugin
{
    private $productRepository;
    private $date;
    private $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->date = $date;
        $this->logger = $logger;
    }

    /**
     * Around saveProducts to correctly detect added/removed children.
     */
    public function aroundSaveProducts(
        Configurable $subject,
        callable $proceed,
        $product,
        $newChildIds = null
    ) {
        $parentId = (int)$product->getId();
        $this->logger->debug("[Cresco_Extensions] aroundSaveProducts called for parent ID: {$parentId}");

        $beforeIds = $this->flatten($subject->getChildrenIds($parentId));
        $this->logger->debug("[Cresco_Extensions] Before child IDs: " . implode(',', $beforeIds));

        $result = $proceed($product, $newChildIds);

        $afterIds = $this->flatten($subject->getChildrenIds($parentId));
        $this->logger->debug("[Cresco_Extensions] After child IDs: " . implode(',', $afterIds));

        $added = array_diff($afterIds, $beforeIds);
        $removed = array_diff($beforeIds, $afterIds);

        $this->logger->debug("[Cresco_Extensions] Added IDs: " . implode(',', $added));
        $this->logger->debug("[Cresco_Extensions] Removed IDs: " . implode(',', $removed));

        $now = $this->date->gmtDate();

        foreach ($added as $childId) {
            $this->touch($childId, $now, 'added');
        }
        foreach ($removed as $childId) {
            $this->touch($childId, $now, 'removed');
        }

        return $result;
    }

    private function flatten($ids): array
    {
        $flat = [];
        array_walk_recursive($ids, function ($v) use (&$flat) {
            $flat[] = (int)$v;
        });
        return array_values(array_unique($flat));
    }

    private function touch(int $childId, string $now, string $action): void
    {
        try {
            $childProduct = $this->productRepository->getById($childId);
            $childProduct->setUpdatedAt($now);
            $this->productRepository->save($childProduct);
            $this->logger->debug("[Cresco_Extensions] Updated updated_at for {$action} simple product ID: {$childId}");
        } catch (\Exception $e) {
            $this->logger->error("[Cresco_Extensions] Failed to update simple product {$childId}: " . $e->getMessage());
        }
    }
}
