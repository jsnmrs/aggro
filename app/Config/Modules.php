<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

class Modules extends BaseModules
{
    /**
     * --------------------------------------------------------------------------
     * Enable Auto-Discovery?
     * --------------------------------------------------------------------------
     *
     * If true, then auto-discovery will happen across all elements listed in
     * $aliases below. If false, no auto-discovery will happen at all,
     * giving a slight performance boost.
     *
     * @var bool
     */
    public $enabled = true;

    /**
     * --------------------------------------------------------------------------
     * Enable Auto-Discovery Within Composer Packages?
     * --------------------------------------------------------------------------
     *
     * If true, then auto-discovery will happen across all namespaces loaded
     * by Composer, as well as the namespaces configured locally.
     *
     * @var bool
     */
    public $discoverInComposer = true;

    /**
     * --------------------------------------------------------------------------
     * Composer Packages
     * --------------------------------------------------------------------------
     *
     * This array allows you to specify which packages are allowed to be
     * discovered via the Composer autoloader, and which are excluded.
     *
     * The 'only' array, if provided, defines the only packages that will be
     * discovered. The 'exclude' array will ignore the specified packages.
     *
     * @var array{only?: list<string>, exclude?: list<string>}
     */
    public $composerPackages = [];

    /**
     * --------------------------------------------------------------------------
     * Auto-Discovery Rules
     * --------------------------------------------------------------------------
     *
     * Aliases list of all discovery classes that will be active and used during
     * the current application request.
     *
     * If it is not listed, only the base application elements will be used.
     *
     * @var list<string>
     */
    public $aliases = [
        'events',
        'filters',
        'registrars',
        'routes',
        'services',
    ];
}
