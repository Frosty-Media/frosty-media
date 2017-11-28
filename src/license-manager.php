<?php

namespace FrostyMedia\Includes;

/**
 * Class LicenseManager
 *
 * @package FrostyMedia\Includes
 */
abstract class LicenseManager implements WpHooksInterface {

    const OBJECT_NAME = null;

    /** @var string $title */
    protected $title;

    /** @var string $action */
    protected $action;

    /** @var string $api_url */
    protected $api_url;

    /**
     * LicenseManager constructor.
     */
    public function __construct() {
        $this->add_hooks();
    }
}
