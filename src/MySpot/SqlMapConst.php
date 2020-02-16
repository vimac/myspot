<?php


namespace MySpot;


use PDO;

/**
 * Aliases for PDO constants
 */
class SqlMapConst
{
    /**
     * Represents a boolean data type.
     */
    const PARAM_BOOL = PDO::PARAM_BOOL;

    /**
     * Represents the SQL NULL data type.
     */
    const PARAM_NULL = PDO::PARAM_NULL;

    /**
     * Represents the SQL INTEGER data type.
     */
    const PARAM_INT = PDO::PARAM_INT;

    /**
     * Represents the SQL CHAR, VARCHAR, or other string data type.
     */
    const PARAM_STR = PDO::PARAM_STR;

    /**
     * Flag to denote a string uses the national character set. Available since PHP 7.2.0
     * PARAM_STR_NATL must be combined with PARAM_STR using bitwise-OR for parameter binding.
     */
    const PARAM_STR_NAIL = PDO::PARAM_STR_NATL;

    /**
     * Flag to denote a string uses the regular character set. Available since PHP 7.2.0
     * PARAM_STR_CHAR must be combined with PARAM_STR using bitwise-OR for parameter binding.
     */
    const PARAM_STR_CHAR = PDO::PARAM_STR_CHAR;

    /**
     * Represents the SQL large object data type.
     */
    const PARAM_LOB = PDO::PARAM_LOB;

    /**
     * Represents a record set type. Not currently supported by any drivers.
     */
    const PARAM_STMT = PDO::PARAM_STMT;

    /**
     * Specifies that the parameter is an INOUT parameter for a stored procedure.
     * You must bitwise-OR this value with an explicit PDO::PARAM_* data type.
     */
    const PARAM_INPUT_OUTPUT = PDO::PARAM_INPUT_OUTPUT;

}