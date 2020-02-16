<?php

return [

    'insert' => [
        'sql' => 'INSERT INTO `user` #INSERT#',
    ],
    'insert2' => [
        'sql' => 'INSERT INTO `user` (`name`, `gender`, `created_at`) VALUES (:name, :gender, :createdAt)',
    ],

];
