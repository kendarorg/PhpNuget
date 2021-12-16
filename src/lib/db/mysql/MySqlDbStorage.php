<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\utils\Properties;

class MySqlDbStorage extends DbStorage
{
    /**
     * @var Properties
     */
    private $properties;

    /**
     * @param Properties $properties
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }
}