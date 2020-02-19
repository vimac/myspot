<?php


namespace MySpotBenchmark;


use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpotTests\InMemoryDBProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class Benchmark
 */
class Benchmark extends TestCase
{

    const LEVEL = 10000;

    use InMemoryDBProvider;

    public function test()
    {
        $pdo = $this->inMemoryPDOProvider();
        echo "No doubt it's much slower than native PDO, use it at your own risk\n";
        for ($round = 0; $round < 3; $round++) {
            echo "Round $round:\n";
            $time = microtime(true);
            $date = date('Y-m-d H:i:s');
            for ($i = 0; $i < self::LEVEL; $i++) {
                $pdo->exec(
                    sprintf(
                        "INSERT INTO `user` (`name`, `gender`, `created_at`) VALUES ('%s', %s, '%s')",
                        "TEST" . $i,
                        1,
                        $date
                    )
                );
            }

            echo 'Insert by direct PDO ' . self::LEVEL . ' rows: ' . (microtime(true) - $time) . 's';
            echo "\n";

            $path = __DIR__ . '/../assets';
            $time = microtime(true);
            $date = date('Y-m-d H:i:s');
            $sqlMapConfig = new SqlMapConfig($path, $pdo, null, false);
            $sqlMap = new SqlMap($sqlMapConfig);
            for ($i = 0; $i < self::LEVEL; $i++) {
                $sqlMap->insert('config.insert.insert2', [
                        'name' => ['TEST' . $i],
                        'gender' => [1],
                        'createdAt' => [$date]
                    ]
                );
            }

            echo 'Insert by SQLMap ' . self::LEVEL . ' rows: ' . (microtime(true) - $time) . 's';
            echo "\n";
        }

        $this->assertTrue(true);
    }


}
