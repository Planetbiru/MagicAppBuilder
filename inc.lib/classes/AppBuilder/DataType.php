<?php

namespace AppBuilder;

/**
 * Class DataType
 *
 * Defines commonly used database data types as constants.
 * These constants can be used to standardize data type definitions 
 * across the application, improving consistency and reducing errors.
 *
 * @package AppBuilder
 */
class DataType
{
    /** @var string Integer with a size of 11. */
    public const INT_11 = 'int(11)';
    
    /** @var string Integer with a size of 4. */
    public const INT_4 = 'int(4)';
    
    /** @var string Tiny integer with a size of 1, typically used for boolean values. */
    public const TINYINT_1 = 'tinyint(1)';
    
    /** @var string Variable character string with a maximum length of 40. */
    public const VARCHAR_40 = 'varchar(40)';
    
    /** @var string Variable character string with a maximum length of 50. */
    public const VARCHAR_50 = 'varchar(50)';
    
    /** @var string Timestamp for date and time data. */
    public const TIMESTAMP = 'timestamp';
}
