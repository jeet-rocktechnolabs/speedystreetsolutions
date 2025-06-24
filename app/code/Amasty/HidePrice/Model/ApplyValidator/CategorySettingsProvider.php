<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price (Call for Price) for Magento 2
 */

namespace Amasty\HidePrice\Model\ApplyValidator;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;

class CategorySettingsProvider
{
    public const CATEGORY_ATTR_MODE = 'am_hide_price_mode_cat';

    public const CATEGORY_ATTR_GROUP = 'am_hide_price_customer_gr_cat';

    public const KEY_MODE = 'mode';

    public const KEY_GROUPS = 'customerGroups';

    private const TWO_HOURS = 7200;

    /**
     * @var array|null
     */
    private $localStorage = null;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        CollectionFactory $collectionFactory,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * @return array{'mode': bool, 'customerGroups': int[]}|null
     */
    public function get(int $categoryId): ?array
    {
        $this->resolveLoad();

        return $this->localStorage[$categoryId] ?? null;
    }

    /**
     * Resolve the load process.
     */
    private function resolveLoad(): void
    {
        if (isset($this->localStorage)) {
            return;
        }

        $cache = $this->loadCache();
        if ($cache !== null) {
            $this->localStorage = $cache;

            return;
        }

        $data = $this->load();
        $this->saveCache($data);
        $this->localStorage = $data;
    }

    /**
     * @return array{int: array{'mode': bool, 'customerGroups': int[]}}|null
     */
    private function loadCache(): ?array
    {
        $data = $this->cache->load($this->getCacheId());
        if ($data === false) {
            return null;
        }

        return $this->serializer->unserialize($data);
    }

    /**
     * @return array{int: array{'mode': bool, 'customerGroups': int[]}}
     */
    private function load(): array
    {
        /* get categories only with not empty attributes customer_gr_cat and mode_cat */
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(self::CATEGORY_ATTR_MODE)
            ->addAttributeToSelect(self::CATEGORY_ATTR_GROUP)
            ->addAttributeToFilter(self::CATEGORY_ATTR_MODE, ['notnull' => true])
            ->addAttributeToFilter(self::CATEGORY_ATTR_GROUP, ['notnull' => true]);
        $data = $collection->getData();

        if (!$data) {
            return [];
        }

        $result = [];
        foreach ($data as $category) {
            $categoryId = (int)$category['entity_id'];
            $result[$categoryId] = [
                self::KEY_MODE => (bool)$category[self::CATEGORY_ATTR_MODE],
                self::KEY_GROUPS => $this->convertStringToArray($category[self::CATEGORY_ATTR_GROUP])
            ];
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function convertStringToArray(?string $string): array
    {
        $string = str_replace(' ', '', (string)$string);

        return $string ? array_map('intval', explode(',', $string)) : [];
    }

    private function saveCache(array $data): void
    {
        $this->cache->save(
            $this->serializer->serialize($data),
            $this->getCacheId(),
            [\Magento\Catalog\Model\Category::CACHE_TAG],
            self::TWO_HOURS
        );
    }

    public function getCacheId(): string
    {
        return 'AM_HIDE_PRICE_CATEGORY_SETTINGS';
    }

    public function clearStorage(): void
    {
        $this->localStorage = null;
    }
}
