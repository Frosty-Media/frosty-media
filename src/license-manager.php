<?php

namespace FrostyMedia\Includes;

/**
 * Class LicenseManager
 *
 * @package FrostyMedia\Includes
 */
abstract class LicenseManager implements WpHooksInterface {

    /** @var string $submenu_page */
    public $submenu_page;

    /** @var string $action */
    protected $action;

    /** @var string $api_url */
    protected $api_url;

    /** @var string $title */
    protected $title;

    /**
     * LicenseManager constructor.
     */
    public function __construct() {
        $this->add_hooks();
    }
}
