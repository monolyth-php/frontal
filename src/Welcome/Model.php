<?php

namespace Welcome;

use Dormant\Dabble;
use Ornament\Query;
use Disclosure\Injector;

/**
 * An example model using Ornament and Dormant.
 */
class Model
{
    use Dabble;
    use Query;
    use Injector;

    private $adapter;

    /**
     * {{{
     * Just example properties that presumably match fields in some
     * data source:
     */
    public $id;
    public $name;
    public $value;
    /** }}} */

    public function __construct()
    {
        $this->inject(function ($adapter) {});
        $this->addDabbleAdapter($this->adapter);
    }
}

