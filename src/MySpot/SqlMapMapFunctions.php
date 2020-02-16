<?php


namespace MySpot;


class SqlMapMapFunctions
{

    private static $functions = [
        SqlMapStatement::MAP_STYLE_UNDERSCORE_TO_LOWER_CAMELCASE => [self::class, 'convertUnderScoreToLowerCase'],
        SqlMapStatement::MAP_STYLE_LOWER_CAMELCASE_TO_UNDERSCORE => [self::class, 'convertLowerCaseToUnderScore'],
    ];

    public static function getFunction($mapStyle): callable
    {
        return self::$functions[$mapStyle];
    }

    public static function convertUnderScoreToLowerCase(string $in): string
    {
        return preg_replace_callback('/_+(\S)/', function ($word) {
            return @strtoupper($word[1]);
        }, $in);
    }

    public static function convertLowerCaseToUnderScore(string $in): string
    {
        return preg_replace_callback('/[A-Z]/', function ($word) {
            return @'_' . strtolower($word[0]);
        }, $in);
    }

}