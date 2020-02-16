<?php

return [

    'update_all' => [
        'sql' => 'UPDATE `user` SET #UPDATE#',
    ],
    'update_with_in' => [
        'sql' => 'UPDATE `user` SET #UPDATE# WHERE `id` IN :id:',
    ],
    'update_with_condition_in' => [
        'sql' => 'UPDATE `user` SET #UPDATE# :id? {WHERE `id` IN :id:}',
    ]

];