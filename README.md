# MySpot

[中文版读我 / Readme in Chinese](README.zh.md)

A PHP persistence framework based on PDO with sql map support.

Think this as a PHP lightweight alternative implementation of MyBatis.

It should be helpful when you write business database access code in PHP project.

## Why?

PHP is my first choice when I decide to build something fast.

But data access code bothers me every time, so I decided to open this project.

Inspired by a more simple data access library which is a part of a framework named "IRON", a proprietary library be used by a company named Youzan I worked for once. It's a great company, salute to the engineers in there.

(Sure, no code in this project comes from that "IRON" framework.)

## Features

* A visual tools to generate the code of Data Access Object class file and configuration file, visit [iCopyPaste](https://github.com/vimac/iCopyPaste)
* A simple syntax support for simple condition sub statement
* A simple syntax support for SELECT..IN query which is poor in PDO
* Lightweight, all configuration stored in PHP native array

## Requirements

* PHP 7.3+
* PHP PDO extension with proper database driver, MySQL or Sqlite recommend
* PHP JSON extension

## Installation

```bash
composer require vimac/myspot
```

## Usage

#### GUI Utility

<img src="https://github.com/vimac/iCopyPaste/raw/master/snapshot.png" alt="iCopyPaste Snapshot"/>

Check out [iCopyPaste](https://github.com/vimac/iCopyPaste)

Downloads: [iCopyPaste Release Page](https://github.com/vimac/iCopyPaste/releases)

#### Configuration

#### Special syntax


#### Initialize

```php
<?php
use MySpot\SqlMapConfig;
use MySpot\SqlMap;

// Initialize PDO first, deal with your database connection
$pdo = new PDO('sqlite::memory:'); 

// Initialize SqlMapConfig
$sqlMapConfig = new SqlMapConfig('__PATH_TO_YOUR_CONFIGURATION_DIR__', $pdo);

// Initialize SqlMap
// If you are using a framework which support Dependency Injection, it is recommend that you put this in SqlMap your container
$sqlMap = new SqlMap($sqlMapConfig);
```


#### SELECT

##### Normal SELECT query
```php
<?php
// Initialize code

/** 
 * Method 'select' parameters: statementId, [statementParameters]
 * e.g
 * statementId: db.user.select_by_id
 */
$sqlMapResult = $sqlMap->select('configParentDir.configParentFile.statementId', [
    // 'parameterName' => ['parameterValue', parameterType]
    // parameterType could be omit, which will be default value: MySpot\SqlMapConst::PARAM_STR
    'id' => [1, MySpot\SqlMapConst::PARAM_INT]
]);

$sqlMapResult->fetch();

```

##### SELECT..IN query


## Tests

PHPUnit 8+

```bash

# Run this when in project root dir

composer run-scripts test
```




