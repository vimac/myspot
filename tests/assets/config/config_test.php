<?php

use MySpot\SqlMapStatement;

return [
    'empty_exception_test' => [],
    'select_by_id' => [
        'sql' => 'SELECT * FROM `user` WHERE `id` = :id',
    ],
];