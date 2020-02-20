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
