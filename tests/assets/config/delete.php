<?php

return [

    'delete_all' => [
        'sql' => 'DELETE FROM `user`',
    ],
    'delete_by_id' => [
        'sql' => 'DELETE FROM `user` WHERE `id` = :id',
    ],
    'delete_with_condition_in' => [
        'sql' => 'DELETE FROM `user` :id? {WHERE `id` IN :id:}',
    ]

];