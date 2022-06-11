<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Table\DataSource\Nopkey\_generated;

use Atlas\Table\Table;

/**
 * @method NopkeyRow|null fetchRow($primaryVal)
 * @method NopkeyRow[] fetchRows(array $primaryVals)
 * @method NopkeyTableSelect select(array $whereEquals = [])
 * @method NopkeyRow newRow(array $cols = [])
 * @method NopkeyRow newSelectedRow(array $cols)
 */
abstract class NopkeyTable_ extends Table
{
    const DRIVER = 'sqlite';

    const NAME = 'nopkeys';

    const COLUMNS = [
        'name' => [
            'name' => 'name',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
    ];

    const COLUMN_NAMES = [
        'name',
        'email',
    ];

    const COLUMN_DEFAULTS = [
        'name' => null,
        'email' => null,
    ];

    const PRIMARY_KEY = [
    ];

    const AUTOINC_COLUMN = null;

    const AUTOINC_SEQUENCE = null;
}
