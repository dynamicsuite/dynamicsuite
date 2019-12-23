<?php
/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

namespace DynamicSuite;
use PDO;

/**
 * Class Config.
 *
 * @package DynamicSuite
 * @property array $packages
 * @property string $charset
 * @property string $language
 * @property string $default_view
 * @property string $nav_header_view
 * @property string $login_view
 * @property string $default_title
 * @property string $document_template
 * @property string $stylesheet_template
 * @property string $script_template
 * @property string $nav_template
 * @property string $nav_group_template
 * @property string $nav_single_template
 * @property string $nav_sublink_template
 * @property string $about_template
 * @property string $error_404_template
 * @property bool $error_404_log
 * @property string $error_500_template
 * @property string $css_fontawesome
 * @property string $css_style
 * @property string $css_theme
 * @property string $js_dynamicsuite
 * @property string $nav_header_text
 * @property string $nav_login_path
 * @property string $db_dsn
 * @property string $db_user
 * @property string $db_pass
 * @property array $db_options
 * @property string $backup_dir
 * @property string $backup_days_keep
 * @property string $pkg_db_dir
 * @property string $pkg_build_dir
 * @property array $pkg_repos
 * @property array $pkg_blacklist
 */
class Config extends DSConfig
{

    /**
     * Packages to load.
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Application charset.
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Application language.
     *
     * @var string
     */
    protected $language = 'en_US';

    /**
     * The default view the user is redirected to.
     *
     * @var string
     */
    protected $default_view = '/dynamicsuite/about';

    /**
     * The location where the navigation header links to.
     *
     * @var string
     */
    protected $nav_header_view = '/dynamicsuite/about';

    /**
     * The view that unauthenticated users are redirected to.
     *
     * @var string
     */
    protected $login_view = '/login';

    /**
     * The default title to use on all structured views.
     *
     * @var string
     */
    protected $default_title = 'Dynamic Suite';

    /**
     * Application document template.
     *
     * @var string
     */
    protected $document_template = 'client/templates/document.html';

    /**
     * CSS stylesheet template.
     *
     * @var string
     */
    protected $stylesheet_template = 'client/templates/stylesheet.html';

    /**
     * JS script template
     *
     * @var string
     */
    protected $script_template = 'client/templates/script.html';

    /**
     * Application navigation template/view.
     *
     * @var string
     */
    protected $nav_template = 'client/templates/nav.html';

    /**
     * Navigation group template.
     *
     * @var string
     */
    protected $nav_group_template = 'client/templates/nav_group.html';

    /**
     * Single view navigation template.
     *
     * @var string
     */
    protected $nav_single_template = 'client/templates/nav_single.html';

    /**
     * Navigation group sublink template.
     *
     * @var string
     */
    protected $nav_sublink_template = 'client/templates/nav_sublink.html';

    /**
     * About view template
     *
     * @var string
     */
    protected $about_template = 'client/templates/about.html';

    /**
     * 404 error template.
     *
     * @var string
     */
    protected $error_404_template = 'client/templates/errors/404.html';

    /**
     * If 404 errors are logged to the log file.
     *
     * @var bool
     */
    protected $error_404_log = false;

    /**
     * 500 error template.
     *
     * @var string
     */
    protected $error_500_template = 'client/templates/errors/500.html';

    /**
     * Path to font awesome library.
     *
     * @var string
     */
    protected $css_fontawesome = '/dynamicsuite/client/css/fontawesome.min.css';

    /**
     * Path to style library.
     *
     * @var string
     */
    protected $css_style = '/dynamicsuite/client/css/ds-style.min.css';

    /**
     * Path to theme library.
     *
     * @var string
     */
    protected $css_theme = '/dynamicsuite/client/css/ds-theme.min.css';

    /**
     * Path to dynamicsuite client library.
     *
     * @var string
     */
    protected $js_dynamicsuite = '/dynamicsuite/client/js/dynamicsuite.min.js';

    /**
     * Header for the nav area.
     *
     * @var string
     */
    protected $nav_header_text = 'Dynamic Suite';

    /**
     * The path used for user login/logout.
     *
     * @var string
     */
    protected $nav_login_path = '/login';

    /**
     * Database data source name.
     *
     * @var string
     */
    protected $db_dsn = 'mysql:unix_socket=/tmp/mysql.sock;dbname=dynamicsuite';

    /**
     * Database username.
     *
     * @var string
     */
    protected $db_user = '';

    /**
     * Database password.
     *
     * @var string
     */
    protected $db_pass = '';

    /**
     * Database additional PDO options.
     *
     * @var array
     */
    protected $db_options = [
        PDO::ATTR_TIMEOUT => 1,
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    /**
     * Backup directory for CLI backups.
     *
     * @var string
     */
    protected $backup_dir = '/var/backups/dynamicsuite';

    /**
     * Number of days to keep a backup before marking it for deletion.
     *
     * @var int
     */
    protected $backup_days_keep = 7;

    /**
     * Package meta directory.
     *
     * @var string
     */
    protected $pkg_db_dir = '.dspkg';

    /**
     * Package build directory.
     *
     * @var string
     */
    protected $pkg_build_dir = '.dspkg/build';

    /**
     * Package repositories.
     *
     * @var string
     */
    protected $pkg_repos = [
        'https://public-repo.dynamicsuite.io' => null
    ];

    /**
     * An array of blacklisted packages.
     *
     * @var array
     */
    protected $pkg_blacklist = [];

    /**
     * Config constructor.
     *
     * @param string $package_id
     */
    public function __construct(string $package_id)
    {
        parent::__construct($package_id);
    }

}