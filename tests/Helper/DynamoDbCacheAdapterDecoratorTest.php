<?php

namespace Rikudou\Tests\DynamoDbCacheBundle\Helper;

use Rikudou\Clock\Clock;
use Rikudou\DynamoDbCache\DynamoDbCache;
use Rikudou\DynamoDbCache\Encoder\SerializeItemEncoder;
use Rikudou\DynamoDbCacheBundle\Cache\DynamoDbCacheAdapter;
use Rikudou\DynamoDbCacheBundle\Converter\SymfonyCacheItemConverter;
use Rikudou\DynamoDbCacheBundle\Helper\DynamoDbCacheAdapterDecorator;
use Rikudou\DynamoDbCacheBundle\Provider\DynamoDbCacheProvider;
use Rikudou\Tests\DynamoDbCacheBundle\AbstractDynamoDbTest;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class DynamoDbCacheAdapterDecoratorTest extends AbstractDynamoDbTest
{
    private DynamoDbCacheAdapter $originalInstance;

    private AdapterInterface|CacheInterface $instance;

    protected function setUp(): void
    {
        $this->originalInstance = new DynamoDbCacheAdapter(
            new DynamoDbCacheProvider(
                new DynamoDbCache('test', $this->getFakeDynamoDbClient($this->itemPoolDefault)),
                new SymfonyCacheItemConverter(
                    new Clock(),
                    new SerializeItemEncoder()
                )
            )
        );
        $this->instance = new class ($this->originalInstance) implements AdapterInterface, CacheInterface {
            use DynamoDbCacheAdapterDecorator;
        };
    }

    public function testGetItem()
    {
        $result1 = $this->originalInstance->getItem('test123');
        $result2 = $this->instance->getItem('test123');
        self::assertEquals($result1, $result2);
        $result1 = $this->originalInstance->getItem('test456');
        $result2 = $this->instance->getItem('test456');
        self::assertEquals($result1, $result2);
        $result1 = $this->originalInstance->getItem('test789');
        $result2 = $this->instance->getItem('test789');
        self::assertEquals($result1, $result2);
        $result1 = $this->originalInstance->getItem('test852');
        $result2 = $this->instance->getItem('test852');
        self::assertEquals($result1, $result2);
    }

    public function testGetItems()
    {
        $result1 = $this->originalInstance->getItems([
            'test123',
            'test456',
            'test852',
        ]);
        $result2 = $this->instance->getItems([
            'test123',
            'test456',
            'test852',
        ]);

        self::assertSameSize($result1, $result2);
        self::assertEquals($result1, $result2);
    }

    public function testClear()
    {
        self::assertEquals($this->originalInstance->clear(), $this->instance->clear());
    }

    public function testHasItem()
    {
        self::assertEquals(
            $this->originalInstance->hasItem('test123'),
            $this->instance->hasItem('test123')
        );
        self::assertEquals(
            $this->originalInstance->hasItem('test456'),
            $this->instance->hasItem('test456')
        );
        self::assertEquals(
            $this->originalInstance->hasItem('test789'),
            $this->instance->hasItem('test789')
        );
        self::assertEquals(
            $this->originalInstance->hasItem('test852'),
            $this->instance->hasItem('test852')
        );
    }

    public function testDeleteItem()
    {
        self::assertEquals(
            $this->originalInstance->deleteItem('test123'),
            $this->instance->deleteItem('test123')
        );
        self::assertEquals(
            $this->originalInstance->deleteItem('test456'),
            $this->instance->deleteItem('test456')
        );
        self::assertEquals(
            $this->originalInstance->deleteItem('test852'),
            $this->instance->deleteItem('test852')
        );
    }

    public function testDeleteItems()
    {
        self::assertEquals(
            $this->originalInstance->deleteItems([
                'test123',
                'test456',
                'test789',
                'test852',
            ]),
            $this->instance->deleteItems([
                'test123',
                'test456',
                'test789',
                'test852',
            ])
        );
        self::assertEquals(
            $this->originalInstance->deleteItems([
                'test456',
            ]),
            $this->instance->deleteItems([
                'test456',
            ])
        );
        self::assertEquals(
            $this->originalInstance->deleteItems([
                'test852',
            ]),
            $this->instance->deleteItems([
                'test852',
            ])
        );
    }

    public function testSave()
    {
        self::assertCount(0, $this->itemPoolSaved);
        $item = $this->instance->getItem('test123');
        self::assertTrue($this->instance->save($item));
        self::assertCount(1, $this->itemPoolSaved);
    }

    public function testSaveDeferred()
    {
        self::assertCount(0, $this->itemPoolSaved);
        $item = $this->instance->getItem('test123');
        self::assertTrue($this->instance->saveDeferred($item));
        self::assertCount(0, $this->itemPoolSaved);
        $this->instance->commit();
        self::assertCount(1, $this->itemPoolSaved);
    }
}
