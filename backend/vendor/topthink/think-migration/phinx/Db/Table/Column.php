<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Phinx\Db\Table;

use Phinx\Config\FeatureFlags;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\PostgresAdapter;
use RuntimeException;

/**
 * This object is based loosely on: https://api.rubyonrails.org/classes/ActiveRecord/ConnectionAdapters/Table.html.
 */
class Column
{
    public const BIGINTEGER = AdapterInterface::PHINX_TYPE_BIG_INTEGER;
    public const SMALLINTEGER = AdapterInterface::PHINX_TYPE_SMALL_INTEGER;
    public const TINYINTEGER = AdapterInterface::PHINX_TYPE_TINY_INTEGER;
    public const BINARY = AdapterInterface::PHINX_TYPE_BINARY;
    public const BOOLEAN = AdapterInterface::PHINX_TYPE_BOOLEAN;
    public const CHAR = AdapterInterface::PHINX_TYPE_CHAR;
    public const DATE = AdapterInterface::PHINX_TYPE_DATE;
    public const DATETIME = AdapterInterface::PHINX_TYPE_DATETIME;
    public const DECIMAL = AdapterInterface::PHINX_TYPE_DECIMAL;
    public const FLOAT = AdapterInterface::PHINX_TYPE_FLOAT;
    public const INTEGER = AdapterInterface::PHINX_TYPE_INTEGER;
    public const STRING = AdapterInterface::PHINX_TYPE_STRING;
    public const TEXT = AdapterInterface::PHINX_TYPE_TEXT;
    public const TIME = AdapterInterface::PHINX_TYPE_TIME;
    public const TIMESTAMP = AdapterInterface::PHINX_TYPE_TIMESTAMP;
    public const UUID = AdapterInterface::PHINX_TYPE_UUID;
    public const BINARYUUID = AdapterInterface::PHINX_TYPE_BINARYUUID;
    /** MySQL-only column type */
    public const MEDIUMINTEGER = AdapterInterface::PHINX_TYPE_MEDIUM_INTEGER;
    /** MySQL-only column type */
    public const ENUM = AdapterInterface::PHINX_TYPE_ENUM;
    /** MySQL-only column type */
    public const SET = AdapterInterface::PHINX_TYPE_STRING;
    /** MySQL-only column type */
    public const BLOB = AdapterInterface::PHINX_TYPE_BLOB;
    /** MySQL-only column type */
    public const YEAR = AdapterInterface::PHINX_TYPE_YEAR;
    /** MySQL/Postgres-only column type */
    public const JSON = AdapterInterface::PHINX_TYPE_JSON;
    /** Postgres-only column type */
    public const JSONB = AdapterInterface::PHINX_TYPE_JSONB;
    /** Postgres-only column type */
    public const CIDR = AdapterInterface::PHINX_TYPE_CIDR;
    /** Postgres-only column type */
    public const INET = AdapterInterface::PHINX_TYPE_INET;
    /** Postgres-only column type */
    public const MACADDR = AdapterInterface::PHINX_TYPE_MACADDR;
    /** Postgres-only column type */
    public const INTERVAL = AdapterInterface::PHINX_TYPE_INTERVAL;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|\Phinx\Util\Literal
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var bool
     */
    protected $null = true;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var bool
     */
    protected $identity = false;

    /**
     * Postgres-only column option for identity (always|default)
     *
     * @var ?string
     */
    protected $generated = PostgresAdapter::GENERATED_ALWAYS;

    /**
     * @var int|null
     */
    protected $seed;

    /**
     * @var int|null
     */
    protected $increment;

    /**
     * @var int|null
     */
    protected $scale;

    /**
     * @var string|null
     */
    protected $after;

    /**
     * @var string|null
     */
    protected $update;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var bool
     */
    protected $signed = true;

    /**
     * @var bool
     */
    protected $timezone = false;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var string|null
     */
    protected $collation;

    /**
     * @var string|null
     */
    protected $encoding;

    /**
     * @var int|null
     */
    protected $srid;

    /**
     * @var array|null
     */
    protected $values;

    /**
     * Column constructor
     */
    public function __construct()
    {
        $this->null = FeatureFlags::$columnNullDefault;
    }

    /**
     * Sets the column name.
     *
     * @param string $name Name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the column name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the column type.
     *
     * @param string|\Phinx\Util\Literal $type Column type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the column type.
     *
     * @return string|\Phinx\Util\Literal
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the column limit.
     *
     * @param int|null $limit Limit
     * @return $this
     */
    public function setLimit(?int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Gets the column limit.
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets whether the column allows nulls.
     *
     * @param bool $null Null
     * @return $this
     */
    public function setNull(bool $null)
    {
        $this->null = (bool)$null;

        return $this;
    }

    /**
     * Gets whether the column allows nulls.
     *
     * @return bool
     */
    public function getNull(): bool
    {
        return $this->null;
    }

    /**
     * Does the column allow nulls?
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->getNull();
    }

    /**
     * Sets the default column value.
     *
     * @param mixed $default Default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Gets the default column value.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Sets generated option for identity columns. Ignored otherwise.
     *
     * @param string|null $generated Generated option
     * @return $this
     */
    public function setGenerated(?string $generated)
    {
        $this->generated = $generated;

        return $this;
    }

    /**
     * Gets generated option for identity columns. Null otherwise
     *
     * @return string|null
     */
    public function getGenerated(): ?string
    {
        return $this->generated;
    }

    /**
     * Sets whether or not the column is an identity column.
     *
     * @param bool $identity Identity
     * @return $this
     */
    public function setIdentity(bool $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Gets whether or not the column is an identity column.
     *
     * @return bool
     */
    public function getIdentity(): bool
    {
        return $this->identity;
    }

    /**
     * Is the column an identity column?
     *
     * @return bool
     */
    public function isIdentity(): bool
    {
        return $this->getIdentity();
    }

    /**
     * Sets the name of the column to add this column after.
     *
     * @param string $after After
     * @return $this
     */
    public function setAfter(string $after)
    {
        $this->after = $after;

        return $this;
    }

    /**
     * Returns the name of the column to add this column after.
     *
     * @return string|null
     */
    public function getAfter(): ?string
    {
        return $this->after;
    }

    /**
     * Sets the 'ON UPDATE' mysql column function.
     *
     * @param string $update On Update function
     * @return $this
     */
    public function setUpdate(string $update)
    {
        $this->update = $update;

        return $this;
    }

    /**
     * Returns the value of the ON UPDATE column function.
     *
     * @return string|null
     */
    public function getUpdate(): ?string
    {
        return $this->update;
    }

    /**
     * Sets the number precision for decimal or float column.
     *
     * For example `DECIMAL(5,2)`, 5 is the precision and 2 is the scale,
     * and the column could store value from -999.99 to 999.99.
     *
     * @param int|null $precision Number precision
     * @return $this
     */
    public function setPrecision(?int $precision)
    {
        $this->setLimit($precision);

        return $this;
    }

    /**
     * Gets the number precision for decimal or float column.
     *
     * For example `DECIMAL(5,2)`, 5 is the precision and 2 is the scale,
     * and the column could store value from -999.99 to 999.99.
     *
     * @return int|null
     */
    public function getPrecision(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets the column identity increment.
     *
     * @param int $increment Number increment
     * @return $this
     */
    public function setIncrement(int $increment)
    {
        $this->increment = $increment;

        return $this;
    }

    /**
     * Gets the column identity increment.
     *
     * @return int|null
     */
    public function getIncrement(): ?int
    {
        return $this->increment;
    }

    /**
     * Sets the column identity seed.
     *
     * @param int $seed Number seed
     * @return $this
     */
    public function setSeed(int $seed)
    {
        $this->seed = $seed;

        return $this;
    }

    /**
     * Gets the column identity seed.
     *
     * @return int
     */
    public function getSeed(): ?int
    {
        return $this->seed;
    }

    /**
     * Sets the number scale for decimal or float column.
     *
     * For example `DECIMAL(5,2)`, 5 is the precision and 2 is the scale,
     * and the column could store value from -999.99 to 999.99.
     *
     * @param int|null $scale Number scale
     * @return $this
     */
    public function setScale(?int $scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Gets the number scale for decimal or float column.
     *
     * For example `DECIMAL(5,2)`, 5 is the precision and 2 is the scale,
     * and the column could store value from -999.99 to 999.99.
     *
     * @return int
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     * Sets the number precision and scale for decimal or float column.
     *
     * For example `DECIMAL(5,2)`, 5 is the precision and 2 is the scale,
     * and the column could store value from -999.99 to 999.99.
     *
     * @param int $precision Number precision
     * @param int $scale Number scale
     * @return $this
     */
    public function setPrecisionAndScale(int $precision, int $scale)
    {
        $this->setLimit($precision);
        $this->scale = $scale;

        return $this;
    }

    /**
     * Sets the column comment.
     *
     * @param string|null $comment Comment
     * @return $this
     */
    public function setComment(?string $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Gets the column comment.
     *
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Sets whether field should be signed.
     *
     * @param bool $signed Signed
     * @return $this
     */
    public function setSigned(bool $signed)
    {
        $this->signed = (bool)$signed;

        return $this;
    }

    /**
     * Gets whether field should be signed.
     *
     * @return bool
     */
    public function getSigned(): bool
    {
        return $this->signed;
    }

    /**
     * Should the column be signed?
     *
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->getSigned();
    }

    /**
     * Sets whether the field should have a timezone identifier.
     * Used for date/time columns only!
     *
     * @param bool $timezone Timezone
     * @return $this
     */
    public function setTimezone(bool $timezone)
    {
        $this->timezone = (bool)$timezone;

        return $this;
    }

    /**
     * Gets whether field has a timezone identifier.
     *
     * @return bool
     */
    public function getTimezone(): bool
    {
        return $this->timezone;
    }

    /**
     * Should the column have a timezone?
     *
     * @return bool
     */
    public function isTimezone(): bool
    {
        return $this->getTimezone();
    }

    /**
     * Sets field properties.
     *
     * @param array $properties Properties
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Gets field properties
     *
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Sets field values.
     *
     * @param string[]|string $values Value(s)
     * @return $this
     */
    public function setValues($values)
    {
        if (!is_array($values)) {
            $values = preg_split('/,\s*/', $values) ?: [];
        }
        $this->values = $values;

        return $this;
    }

    /**
     * Gets field values
     *
     * @return array|null
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Sets the column collation.
     *
     * @param string $collation Collation
     * @return $this
     */
    public function setCollation(string $collation)
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * Gets the column collation.
     *
     * @return string|null
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    /**
     * Sets the column character set.
     *
     * @param string $encoding Encoding
     * @return $this
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Gets the column character set.
     *
     * @return string|null
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
    }

    /**
     * Sets the column SRID.
     *
     * @param int $srid SRID
     * @return $this
     */
    public function setSrid(int $srid)
    {
        $this->srid = $srid;

        return $this;
    }

    /**
     * Gets the column SRID.
     *
     * @return int|null
     */
    public function getSrid(): ?int
    {
        return $this->srid;
    }

    /**
     * Gets all allowed options. Each option must have a corresponding `setFoo` method.
     *
     * @return array
     */
    protected function getValidOptions(): array
    {
        return [
            'limit',
            'default',
            'null',
            'identity',
            'scale',
            'after',
            'update',
            'comment',
            'signed',
            'timezone',
            'properties',
            'values',
            'collation',
            'encoding',
            'srid',
            'seed',
            'increment',
            'generated',
        ];
    }

    /**
     * Gets all aliased options. Each alias must reference a valid option.
     *
     * @return array
     */
    protected function getAliasedOptions(): array
    {
        return [
            'length' => 'limit',
            'precision' => 'limit',
        ];
    }

    /**
     * Utility method that maps an array of column options to this objects methods.
     *
     * @param array<string, mixed> $options Options
     * @throws \RuntimeException
     * @return $this
     */
    public function setOptions(array $options)
    {
        $validOptions = $this->getValidOptions();
        $aliasOptions = $this->getAliasedOptions();

        if (isset($options['identity']) && $options['identity'] && !isset($options['null'])) {
            $options['null'] = false;
        }

        foreach ($options as $option => $value) {
            if (isset($aliasOptions[$option])) {
                // proxy alias -> option
                $option = $aliasOptions[$option];
            }

            if (!in_array($option, $validOptions, true)) {
                throw new RuntimeException(sprintf('"%s" is not a valid column option.', $option));
            }

            $method = 'set' . ucfirst($option);
            $this->$method($value);
        }

        return $this;
    }
}
