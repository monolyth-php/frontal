<?php

namespace Welcome;

use Dormant\Adapter;
use Ornament\Model as Base;
use Ornament\Query;
use Disclosure\Injector;

/**
 * An example model using Ornament and Dormant.
 */
class Model
{
    use Base;
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
        // Assuming you've registered your PDO database resource under
        // `adapter` in the `Disclosure\Container`:
        $this->inject(function ($adapter) {});
        $this->addAdapter(new Adapter($this->adapter));
    }
}

