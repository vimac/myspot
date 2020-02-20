# MySpot

**[Readme in English](README.md)**

![PHP from Packagist](https://img.shields.io/packagist/php-v/vimac/myspot?color=%238593BC&logo=php) [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE) [![Build Status](https://travis-ci.org/vimac/myspot.svg?branch=master)](https://travis-ci.org/vimac/myspot) [![Coverage Status](https://coveralls.io/repos/github/vimac/myspot/badge.svg?branch=master)](https://coveralls.io/github/vimac/myspot?branch=master)

| [开发动机](#开发动机) | [特性](#特性) | [环境需求](#环境需求) | [演示工程](#演示工程) | [安装](#安装) | [使用](#使用) | [测试](#测试) | [许可协议](#许可协议) | [关于我](#关于我) |


一个 PHP 持久化层框架, 基于 PDO, 支持 SQL Map.

可以把这个是想象成非常轻量级的 MyBatis 实现.

在 PHP 工程中, 它应该对你写一些业务方面的数据库访问代码会比较有帮助

## 开发动机

当我决定快速开启某些项目时, PHP 仍然是我的首选

但是数据库访问代码一直是我很头痛的一点, 所以决定开启这个项目

开始开发这个项目也是受到了曾经使用和参与过的一个名为 IRON 的私有框架的启发, 它有一个非常简单的数据库访问层, 这里借鉴了它的参数配置部分. IRON 是我曾经工作过的公司"有赞"的 PHP 团队早期开发并使用的 PHP 框架, 虽然有赞饱受 996 的争议, 我仍然想说那是一家伟大的公司, 向那里的工程师致敬.

(当然, 并没有代码来自于那个 IRON 框架)

## 特性

* 一个可视化工具, 可以用来生成 DAO 类文件和配置文件, 可以访问: [iCopyPaste](https://github.com/vimac/iCopyPaste)
* 一个简单语法支持条件子语句
* 一个简单语法支持 PDO 支持的并不好的 SELECT..IN 语句
* 轻量级, 所有的配置均使用 PHP 数组存储

## 环境需求

* PHP 7.3+
* PHP PDO 扩展, 以及对应的数据库驱动, 推荐 MySQL 或者 Sqlite
* PHP JSON 扩展

## 演示工程

你可以在这个项目里面看到 MySpot 的整体使用方式:

[MySpot-Demo-Application](https://github.com/vimac/myspot-demo-app)

## 安装

```bash
composer require vimac/myspot
```

## 使用

#### GUI 工具

[![iCopyPaste Snapshot](https://github.com/vimac/iCopyPaste/raw/master/snapshot.png)](https://github.com/vimac/iCopyPaste)

你可以使用 [iCopyPaste](https://github.com/vimac/iCopyPaste) 来生成基本的查询, 包括增删查改, 配置文件, 和 DAO 代码模板

下载: [iCopyPaste Release Page](https://github.com/vimac/iCopyPaste/releases)

#### 初始化

```php
<?php
use MySpot\SqlMapConfig;
use MySpot\SqlMap;

// 首先你要有一个可用的 PDO 对象, 并连接正常
$pdo = new PDO('...blablabla...'); 

// 初始化 SqlMapConfig 对象
// 指定你存放 SQLMap 配置文件的目录
// 例如 `__DIR__ . '/../config/myspot'`
$sqlMapConfig = new SqlMapConfig('__PATH_TO_YOUR_CONFIGURATION_DIR__', $pdo);

// 可选项, 你可以指定数据库表到 PHP 内数据类型的映射方式, 建议开启下划线到小写驼峰体
$sqlMapConfig->setDefaultResultMapStyle(\MySpot\SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE);

// 初始化 SqlMap 对象
// 如果你使用一个支持依赖注入的框架, 建议你把 SqlMap 放到你的容器中去
// 可以看一下演示工程中初始化的处理方式
$sqlMap = new SqlMap($sqlMapConfig);
```

#### 配置

配置文件应该看起来像是这样:

```php
<?php
// 示例文件名: ${projectDir}/config/myspot/test/post.php
// 注意: 这个文件需要被指定在初始化代码中指定的位置中
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

##### 特殊语法

MySpot 实现了一个简单的语法解析器来编译它的特殊语法到真实的 PDO 预处理 SQL

##### SELECT...IN 语句

`:variable:`

这里 `variable` 这个变量将会被视作是数组, 并被编译为: `(:variableItem0, :variableItem1, :variableItem2, ...)`

##### 条件子语句

`:variable?{ substatement }`

这里 `substatment` 的部分, 将会在变量 `variable` 为布尔值真时才有效

#### SELECT

##### 普通 SELECT 查询
```php
<?php
// ...

/** 
 * 'select' 方法的参数: statementId, [statementParameters]
 * 举例
 * statementId: db.user.selectById
 */
$sqlMapResult = $sqlMap->select('configParentDir.configParentFile.statementId', [
    // '参数名' => ['参数值', 参数类型]
    // 参数类型是可以省略的, 那种情况下将会被视作字符串处理: MySpot\SqlMapConst::PARAM_STR
    'id' => [1, \MySpot\SqlMapConst::PARAM_INT]
]);

// 举例
// SQL 模板: SELECT * FROM `test`.`post` WHERE `id` = :id
$id = 1;
$sqlMapResult = $sqlMap->select('test.post.selectById', [
    'id' => [$id, \MySpot\SqlMapConst::PARAM_INT]
]);
```

##### SELECT..IN 查询

```php
<?php
// 大多数情况下, 和普通的 SQL 查询没有区别, 除了它的参数是一个数组
// e.g:
// SQL 模板: SELECT * FROM `test`.`post` WHERE `id` in :id:
$ids = [1, 2, 3];
$sqlMapResult = $sqlMap->select('test.post.selectByIds', [
    'id' => [$ids, \MySpot\SqlMapConst::PARAM_INT]
]);
```

##### UPDATE

```php
<?php
// SQL 模板: UPDATE `test`.`post` SET `uid` = :newUid, `title` = :newTitle, `summary` = :newSummary WHERE `id` = :id
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
// 使用自动生成列名来插入
// SQL 模板: INSERT INTO `test`.`post` #INSERT#
$data = [
    'uid' => [$uid, \MySpot\SqlMapConst::PARAM_INT],
    'title' => [$title],
    'summary' => [$summary],
    'created_at' => [$createdAt] // Notice the key name should be original field name in the table
];
$sqlMapResult = $sqlMap->insert('test.post.insert', [], $data);

// 使用命名变量来插入
// SQL 模板: INSERT INTO `test`.`post`(`uid`, `title`, `summary`, `created_at`) VALUES (:uid, :title, :summary, :createdAt)
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
// SQL 模板: DELETE FROM `test`.`post` WHERE `uid` IN :uid: LIMIT 1
$sqlMapResult = $sqlMap->delete('test.post.deleteByUid', [
    'uid' => [$uid, \MySpot\SqlMapConst::PARAM_INT]
]);
```


##### 条件子语句
```php
<?php
// SQL 模板: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` :orderByCreatedAt?{ORDER BY`created_at` DESC}

// 等效于: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` 
$sqlMapResult = $sqlMap->select('test.post.selectIdUidTitleSummary', [
    'orderByCreatedAt' => [false, \MySpot\SqlMapConst::PARAM_BOOL]
]);

// 等效于: SELECT `id`, `uid`, `title`, `summary` FROM `test`.`post` ORDER BY `created_at` DESC
$sqlMapResult = $sqlMap->select('test.post.selectIdUidTitleSummary', [
    'orderByCreatedAt' => [true, \MySpot\SqlMapConst::PARAM_BOOL]
]);
```


##### 获取结果
```php
<?php
// 所有的结果都会被组合到 MySpot\SqlMapResult 类的实例返回

// 根据你的配置, 获取对象数组, 或者二维数组
$sqlMapResult->fetchAll();

// 根据你的配置, 获取对象或者单行数据转换而成的数组
$sqlMapResult->fetch();

// 遍历所有取回的数据
while ($result = $sqlMapResult->fetch()) {
    // Do something
}

// 获取第一行第一列的数据, 在 SELECT COUNT 时非常有用
$sqlMapResult->fetchColumn();

// 获取第一行特定列的数据
$sqlMapResult->fetchColumn(3);

// 获取插入新行后的最后插入的 ID
$sqlMapResult->getLastInsertId();

// 获取删除或者更新操作后的影响行数
$sqlMapResult->getAffectedLines();

// 以布尔值形式获得执行结果
$sqlMapResult->getExecutedResult();

// 获取绑定的 PDOStatement 对象
$sqlMapResult->getStatement();
```

## 测试

```bash
# 只要在工程根目录下面运行以下命令即可
composer test
```


## 许可协议

[MIT License](LICENSE)

## 关于我

[vifix.cn](http://vifix.cn)
