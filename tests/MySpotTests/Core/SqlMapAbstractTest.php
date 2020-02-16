<?php


namespace MySpotTests\Core;


use MySpot\SqlMapConfig;
use MySpotTests\BaseTestCase;
use MySpotTests\InMemoryDBProvider;

abstract class SqlMapAbstractTest extends BaseTestCase
{
    use InMemoryDBProvider;

    public function dataProvider()
    {
        $path = __DIR__ . '/../../assets';

        $pdo = $this->inMemoryPDOProvider();

        $sqlMapConfig = new SqlMapConfig($path, $pdo);

        return [[
            $sqlMapConfig
        ]];
    }
}