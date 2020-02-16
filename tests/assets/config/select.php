<?php

use MySpot\SqlMapStatement;

return [
    'select_by_id' => [
        'sql' => 'SELECT * FROM `user` WHERE `id` = :id',
    ],
    'select_by_gender_optional' => [
        'sql' => 'SELECT * FROM `user` :gender?{WHERE `gender` = :gender}',
    ],
    'select_in_id' => [
        'sql' => 'SELECT * FROM `user` :id?{WHERE `id` IN :id:}',
    ],
    'select_by_gender_with_pagination' => [
        'sql' => 'SELECT * FROM `user` WHERE `gender` = :gender :pagination?{LIMIT :offset, :rows}',
    ],
    'select_in_id_with_pagination' => [
        'sql' => 'SELECT * FROM `user` :id?{WHERE `id` IN :id:} :pagination?{LIMIT :offset, :rows}',
    ],
    'select_count' => [
        'sql' => 'SELECT count(*) FROM `user`',
    ],
    'select_count_with_condition' => [
        'sql' => 'SELECT count(*) FROM `user` :pagination? ',
    ],
    'select_in_id_as_named_class' => [
        'sql' => 'SELECT * FROM `user` :id?{WHERE `id` IN :id:}',
        'resultType' => \MySpotTests\TestDataObject\TestUserDO::class,
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ],
    'select_in_id_as_anonymous_class' => [
        'sql' => 'SELECT * FROM `user` :id?{WHERE `id` IN :id:}',
        'resultType' => 'object',
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ],
    'select_in_id_as_array' => [
        'sql' => 'SELECT * FROM `user` :id?{WHERE `id` IN :id:}',
        'resultType' => 'array',
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ]

];
