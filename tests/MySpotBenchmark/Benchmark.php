<?php


namespace MySpotBenchmark;

require_once __DIR__ . '/../../vendor/autoload.php';

use MySpot\SqlMap;
use MySpot\SqlMapConfig;
use MySpotTests\InMemoryDBProvider;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Class Benchmark
 *
 * No doubt it's much slower than native PDO, use it at your own risk
 */
class Benchmark extends TestCase
{

    const LEVEL = 10000;

    use InMemoryDBProvider;

    /**
     * @test
     * @dataProvider multipleTimes
     * @group bench
     */
    public function bench()
    {
        $pdo = $this->inMemoryPDOProvider();
        $pdo->exec("DELETE FROM `user`");

        $time = microtime(true);
        $this->insertDirect($pdo, self::LEVEL);
        printf("\nInsert directly %s rows: %ss \n", self::LEVEL, microtime(true) - $time);
        $count = $this->countAndClear($pdo);
        $this->assertEquals(self::LEVEL, (int)$count);

        $time = microtime(true);
        $this->insertByMySpot($pdo, self::LEVEL);
        printf("\nInsert by SQLMap %s rows: %ss \n", self::LEVEL, microtime(true) - $time);
        $count = $this->countAndClear($pdo);
        $this->assertEquals(self::LEVEL, (int)$count);
    }

    /**
     * @test
     * @group profile
     */
    public function profile()
    {
        $pdo = $this->inMemoryPDOProvider();
        $this->insertByMySpot($pdo);
        $this->assertTrue(true);
    }

    private function insertDirect(PDO $pdo, $level = 1)
    {
        $date = date('Y-m-d H:i:s');
        for ($i = 0; $i < $level; $i++) {
            $stmt = $pdo->prepare(
                sprintf(
                    "INSERT INTO `user` (`name`, `gender`, `created_at`) VALUES ('%s', %s, '%s')",
                    "TEST" . $i,
                    1,
                    $date
                )
            );
            $stmt->execute();
        }
    }

    private function insertByMySpot(PDO $pdo, $level = 1)
    {
        $path = __DIR__ . '/../assets';
        $date = date('Y-m-d H:i:s');
        $sqlMapConfig = new SqlMapConfig($path, $pdo, null, false);
        $sqlMap = new SqlMap($sqlMapConfig);
        for ($i = 0; $i < $level; $i++) {
            $sqlMap->insert('config.insert.insert2', [
                    'name' => ['TEST' . $i],
                    'gender' => [1],
                    'createdAt' => [$date]
                ]
            );
        }
    }

    public function multipleTimes()
    {
        return [[], [], []];
    }

    /**
     * @param PDO $pdo
     * @return int
     */
    private function countAndClear(PDO $pdo)
    {
        $stmt = $pdo->prepare("select count(*) from `user`");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $pdo->exec("DELETE FROM `user`");
        return (int)$count;
    }


}
