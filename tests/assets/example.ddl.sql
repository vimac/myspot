CREATE TABLE `user` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT ,
    `name` VARCHAR(20) NOT NULL,
    `gender` tinyint(3) NOT NULL DEFAULT '0',
    `created_at` VARCHAR(20) NOT NULL,
    `deleted_at` VARCHAR(20) DEFAULT '0'
)
