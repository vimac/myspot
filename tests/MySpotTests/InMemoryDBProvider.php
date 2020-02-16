<?php


namespace MySpotTests;


use PDO;

trait InMemoryDBProvider
{

    public function inMemoryPDOProvider(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(__DIR__ . '/../assets/example.ddl.sql'));

        $date = date('Y-m-d H:i:s');

        $families = ["赵", "钱", "孙", "李", "周", "吴", "郑", "王"];

        for ($i = 0; $i < count($families); $i++) {
            $gender = $i % 2 ? 1 : 2;
            $pdo->exec(
                sprintf(
                    'INSERT INTO `user` (`name`, `gender`, `created_at`, `deleted_at`) VALUES ("%s", %s, "%s", "0000-00-00 00:00:00")'
                    , $families[$i], $gender, $date
                )
            );
        }

        return $pdo;
    }

}
