# MySpot

**[中文版读我 / Readme in Chinese](README.zh.md)**

| [Why](#why) | [Features](#features) | [Requirements](#requirements) | [Demo Project](#demo-project) | [Installation](#installation) | [Usage](#usage) | [Tests](#tests) | [License](#license) | [About Me](#about-me) |


A PHP persistence framework based on PDO with sql map support.

Think this as a PHP lightweight alternative implementation of MyBatis.

It should be helpful when you write business database access code in PHP project.

## Why?

PHP is my first choice when I decide to build something fast.

But data access code bothers me every time, so I decided to open this project.

Inspired by a simple data access library which is a part of a framework named "IRON", a proprietary library be used by a company named Youzan I worked for once. It's a great company, salute to the engineers in there.

(Sure, no code in this project comes from that "IRON" framework.)

## Features

* A visual utility to generate the code of Data Access Object class file and configuration file, check out [iCopyPaste](https://github.com/vimac/iCopyPaste)
* A simple syntax support for simple condition sub statement
* A simple syntax support for SELECT..IN query which is poor in PDO
* Lightweight, all configuration stored in PHP native array

## Requirements

* PHP 7.3+
* PHP PDO extension with proper database driver, MySQL or Sqlite recommend
* PHP JSON extension

## Demo Project

See MySpot action in a project:

[MySpot-Demo-Application](https://github.com/vimac/myspot-demo-app)

## Installation

```bash
composer require vimac/myspot
```

## Usage

#### GUI Utility

<img src="https://github.com/vimac/iCopyPaste/raw/master/snapshot.png" alt="iCopyPaste Snapshot"/>

You can use [iCopyPaste](https://github.com/vimac/iCopyPaste) to generate variants of basic queries, configuration template, DAO code template.

Downloads: [iCopyPaste Release Page](https://github.com/vimac/iCopyPaste/releases)

#### Initialize

```php
<?php
use MySpot\SqlMapConfig;
use MySpot\SqlMap;

// Initialize PDO first, deal with your database connection
$pdo = new PDO('sqlite::memory:'); 

// Initialize SqlMapConfig
// Specific your SQL map configuration dir
// Like `__DIR__ . '/../config/myspot'`
$sqlMapConfig = new SqlMapConfig('__PATH_TO_YOUR_CONFIGURATION_DIR__', $pdo);

// Optional, you can setup your default map style, _under_score_ to lowerCamelCase is recommended
$sqlMapConfig->setDefaultResultMapStyle(\MySpot\SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);

// Initialize SqlMap
// If you are using a framework which support Dependency Injection, it is recommend that you put this in SqlMap your container
// You can checkout the demo project and see how it works
$sqlMap = new SqlMap($sqlMapConfig);
```

#### Configuration

Configuration file should be similar to this:

```php
<?php
// Example file name: ${projectDir}/config/myspot/test/post.php
// Note: This file should be put in the directory which the initialize code specific
Use MySpot\SqlMapStatement;
return [
    'select' => [
        'sql' => 'SELECT * FROM `test`.`post`',
        'resultType' => \MyProject\DataObject\Test\PostDO::class, // resultType only available when it's a select query
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE // The statement specific map style
    ],
    'selectById' => [
        'sql' => 'SELECT * FROM `test`.`post` WHERE `id` = :id LIMIT 1',
        'resultType' => \MyProject\DataObject\Test\PostDO::class,
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ],
    'selectByIds' => [
        'sql' => 'SELECT * FROM `test`.`post` WHERE `id` in :id:',
        'resultType' => \MyProject\DataObject\Test\PostDO::class,
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ],
    'selectCountByUid' => [
        'sql' => 'SELECT COUNT(*) FROM `test`.`post` WHERE `uid` = :uid'
    ],
    'selectIdUidTitleSummary' => [
        'sql' => 'SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` :orderByCreatedAt?{ORDER BY `created_at` ASC}',
        'resultType' => \MyProject\DataObject\Test\PostDO::class,
        'resultMapStyle' => SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE
    ],
    'insert' => [
        'sql' => 'INSERT INTO `test`.`post` #INSERT#'
    ],
    'insertUidTitleSummaryCreatedAt' => [
        'sql' => 'INSERT INTO `test`.`post`(`uid`, `title`, `summary`, `created_at`) VALUES (:uid, :title, :summary, :createdAt)'
    ],
    'updateUidTitleSummaryById' => [
        'sql' => 'UPDATE `test`.`post` SET `uid` = :newUid, `title` = :newTitle, `summary` = :newSummary WHERE `id` = :id'
    ],
    'deleteByUid' => [
        'sql' => 'DELETE FROM `test`.`post` WHERE `uid` IN :uid: LIMIT 1'
    ]
];
```


##### Special Syntax

MySpot implements a syntax parser to compile its special syntax to real PDO prepared statement

##### SELECT...IN Statement

`:variable:`

The `variable` will be consider as a array and compile to `(:variableItem0, :variableItem1, :variableItem2, ...)`

##### Conditional Sub Statement

`:variable?{ substatement }`

The `substatment` part will only available when `variable` equals `TRUE`

#### SELECT

##### Normal SELECT query
```php
<?php
// ...

/** 
 * Method 'select' parameters: statementId, [statementParameters]
 * e.g
 * statementId: db.user.selectById
 */
$sqlMapResult = $sqlMap->select('configParentDir.configParentFile.statementId', [
    // 'parameterName' => ['parameterValue', parameterType]
    // parameterType could be omit, which will be default value: MySpot\SqlMapConst::PARAM_STR
    'id' => [1, \MySpot\SqlMapConst::PARAM_INT]
]);

// e.g:
// SQL Template: SELECT * FROM `test`.`post` WHERE `id` = :id
$id = 1;
$sqlMapResult = $sqlMap->select('test.post.selectById', [
    'id' => [$id, \MySpot\SqlMapConst::PARAM_INT]
]);
```

##### SELECT..IN query

```php
<?php
// In most case, it's no difference than normal SELECT query, except its parameter is an array
// e.g:
// SQL Template: SELECT * FROM `test`.`post` WHERE `id` in :id:
$ids = [1, 2, 3];
$sqlMapResult = $sqlMap->select('test.post.selectByIds', [
    'id' => [$ids, \MySpot\SqlMapConst::PARAM_INT]
]);
```

##### UPDATE

```php
<?php
// SQL template: UPDATE `test`.`post` SET `uid` = :newUid, `title` = :newTitle, `summary` = :newSummary WHERE `id` = :id
$sqlMapResult = $sqlMap->update('test.post.updateUidTitleSummaryCreatedAtById', [
    'newUid' => [$newUid, \MySpot\SqlMapConst::PARAM_INT], 
    'newTitle' => [$newTitle, \MySpot\SqlMapConst::PARAM_STR], 
    'newSummary' => [$newSummary, \MySpot\SqlMapConst::PARAM_STR], 
    'newCreatedAt' => [$newCreatedAt, \MySpot\SqlMapConst::PARAM_STR], 
    'id' => [$id, \MySpot\SqlMapConst::PARAM_INT]
]);

```

##### INSERT

```php
<?php
// Insert with auto fields name
// SQL template: INSERT INTO `test`.`post` #INSERT#
$data = [
    'uid' => [$uid, \MySpot\SqlMapConst::PARAM_INT],
    'title' => [$title],
    'summary' => [$summary],
    'created_at' => [$createdAt] // Notice the key name should be original field name in the table
];
$sqlMapResult = $sqlMap->insert('test.post.insert', [], $data);

// Insert with named parameter
// SQL Template: INSERT INTO `test`.`post`(`uid`, `title`, `summary`, `created_at`) VALUES (:uid, :title, :summary, :createdAt)
$sqlMapResult = $sqlMap->insert('test.post.insertUidTitleSummaryCreatedAt', [
    'uid' => [$uid, \MySpot\SqlMapConst::PARAM_INT], 
    'title' => [$title, \MySpot\SqlMapConst::PARAM_STR], 
    'summary' => [$summary, \MySpot\SqlMapConst::PARAM_STR], 
    'createdAt' => [$createdAt, \MySpot\SqlMapConst::PARAM_STR]
]);
```

##### DELETE

```php
<?php
// SQL Template: DELETE FROM `test`.`post` WHERE `uid` IN :uid: LIMIT 1
$sqlMapResult = $sqlMap->delete('test.post.deleteByUid', [
    'uid' => [$uid, \MySpot\SqlMapConst::PARAM_INT]
]);
```


##### Conditional Sub Statement
```php
<?php
// SQL Template: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` :orderByCreatedAt?{ORDER BY`created_at` DESC}

// Equals: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` 
$sqlMapResult = $sqlMap->select('test.post.selectIdUidTitleSummary', [
    'orderByCreatedAt' => [false, \MySpot\SqlMapConst::PARAM_BOOL]
]);

// Equals: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` ORDER BY `created_at` DESC
$sqlMapResult = $sqlMap->select('test.post.selectIdUidTitleSummary', [
    'orderByCreatedAt' => [true, \MySpot\SqlMapConst::PARAM_BOOL]
]);
```


##### Fetch result
```php
<?php
// All of the result will be combined into a class instance of `MySpot\SqlMapResult`

// Fetch an array of object or array of array depends on your configuration
$sqlMapResult->fetchAll();

// Fetch first object or array depends on your configuration
$sqlMapResult->fetch();

// Traverse all of the fetched data
while ($result = $sqlMapResult->fetch()) {
    // Do something
}

// Fetch the first column of first row, it's useful for SELECT COUNT query
$sqlMapResult->fetchColumn();

// Fetch the specific column of first row
$sqlMapResult->fetchColumn(3);

// Fetch the last insert Id when you inserted new row
$sqlMapResult->getLastInsertId();

// Fetch the affected lines when you updated or deleted something
$sqlMapResult->getAffectedLines();

// Fetch the execute result in boolean value
$sqlMapResult->getExecutedResult();

// Get the bound PDOStatement
$sqlMapResult->getStatement();
```

## Tests

```bash
# Simple run the command in project root dir
composer test
```


## License

[MIT License](LICENSE)

## About Me

[vifix.cn](http://vifix.cn)
