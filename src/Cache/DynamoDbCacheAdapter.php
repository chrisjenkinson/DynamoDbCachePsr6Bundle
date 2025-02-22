<?php

namespace Rikudou\DynamoDbCacheBundle\Cache;

use Psr\Cache\CacheItemInterface;
use Rikudou\DynamoDbCache\DynamoCacheItem;
use Rikudou\DynamoDbCache\DynamoDbCache;
use Rikudou\DynamoDbCache\Exception\InvalidArgumentException;
use Rikudou\DynamoDbCacheBundle\Converter\SymfonyCacheItemConverter;
use Rikudou\DynamoDbCacheBundle\Provider\DynamoDbCacheProvider;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\CacheTrait;

final class DynamoDbCacheAdapter implements AdapterInterface, CacheInterface
{
    use CacheTrait;

    private DynamoDbCache $cache;

    private SymfonyCacheItemConverter $converter;

    // @phpstan-ignore-next-line
    private string $namespace;

    public function __construct(
        DynamoDbCacheProvider $provider,
        string $namespace = '',
    ) {
        $this->cache = $provider->cache;
        $this->converter = $provider->converter;
        $this->namespace = $namespace;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     *
     * @return CacheItem
     */
    public function getItem(mixed $key): CacheItem
    {
        $item = $this->cache->getItem($key);
        assert($item instanceof DynamoCacheItem);

        return $this->converter->convertToCacheItem($item);
    }

    /**
     * @param array<string> $keys
     *
     * @throws InvalidArgumentException
     *
     * @return CacheItem[]
     */
    public function getItems(array $keys = []): iterable
    {
        return array_map(function (DynamoCacheItem $item) {
            return $this->converter->convertToCacheItem($item);
        }, [...$this->cache->getItems($keys)]);
    }

    /**
     * @param string $prefix
     *
     * @return bool
     */
    public function clear(string $prefix = ''): bool
    {
        return $this->cache->clear();
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function hasItem(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function deleteItem(string $key): bool
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * @param array<string> $keys
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function deleteItems(array $keys): bool
    {
        return $this->cache->deleteItems($keys);
    }

    /**
     * @param CacheItemInterface $item
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->cache->save($item);
    }

    /**
     * @param CacheItemInterface $item
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->cache->saveDeferred($item);
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->cache->commit();
    }
}
