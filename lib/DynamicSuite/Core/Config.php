<?php
/*
 * Dynamic Suite
 * Copyright (C) 2020 Dynamic Suite Team
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

/** @noinspection PhpUnused */

namespace DynamicSuite\Core;
use PDO;

/**
 * Class Config.
 *
 * @package DynamicSuite\Core
 * @property bool $debug_mode
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
 * @property string $action_links_icon
 * @property string $db_dsn
 * @property string $db_user
 * @property string $db_pass
 * @property array $db_options
 * @property string $memcached_host
 * @property int $memcached_port
 */
final class Config extends GlobalConfig
{

    /**
     * Debug Mode.
     *
     * Note: This may dump sensitive data sent through queries.
     *
     * @var bool
     */
    protected bool $debug_mode = false;

    /**
     * Packages to load.
     *
     * @var array
     */
    protected array $packages = [];

    /**
     * Application charset.
     *
     * @var string
     */
    protected string $charset = 'utf-8';

    /**
     * Application language.
     *
     * @var string
     */
    protected string $language = 'en_US';

    /**
     * The default view the user is redirected to.
     *
     * @var string
     */
    protected string $default_view = '/dynamicsuite/about';

    /**
     * The location where the navigation header links to.
     *
     * @var string
     */
    protected string $nav_header_view = '/dynamicsuite/about';

    /**
     * The view that unauthenticated users are redirected to.
     *
     * @var string
     */
    protected string $login_view = '/login';

    /**
     * The default title to use on all structured views.
     *
     * @var string
     */
    protected string $default_title = 'Dynamic Suite';

    /**
     * Application document template.
     *
     * @var string
     */
    protected string $document_template = 'client/templates/document.html';

    /**
     * CSS stylesheet template.
     *
     * @var string
     */
    protected string $stylesheet_template = 'client/templates/stylesheet.html';

    /**
     * JS script template
     *
     * @var string
     */
    protected string $script_template = 'client/templates/script.html';

    /**
     * Application navigation template/view.
     *
     * @var string
     */
    protected string $nav_template = 'client/templates/nav.html';

    /**
     * Navigation group template.
     *
     * @var string
     */
    protected string $nav_group_template = 'client/templates/nav_group.html';

    /**
     * Single view navigation template.
     *
     * @var string
     */
    protected string $nav_single_template = 'client/templates/nav_single.html';

    /**
     * Navigation group sublink template.
     *
     * @var string
     */
    protected string $nav_sublink_template = 'client/templates/nav_sublink.html';

    /**
     * About view template
     *
     * @var string
     */
    protected string $about_template = 'client/templates/about.html';

    /**
     * 404 error template.
     *
     * @var string
     */
    protected string $error_404_template = 'client/templates/errors/404.html';

    /**
     * If 404 errors are logged to the log file.
     *
     * @var bool
     */
    protected bool $error_404_log = false;

    /**
     * 500 error template.
     *
     * @var string
     */
    protected string $error_500_template = 'client/templates/errors/500.html';

    /**
     * Path to font awesome library.
     *
     * @var string
     */
    protected string $css_fontawesome = '/dynamicsuite/client/css/fontawesome.min.css';

    /**
     * Path to style library.
     *
     * @var string
     */
    protected string $css_style = '/dynamicsuite/client/css/ds-style.min.css';

    /**
     * Path to theme library.
     *
     * @var string
     */
    protected string $css_theme = '/dynamicsuite/client/css/ds-theme.min.css';

    /**
     * Path to dynamicsuite client library.
     *
     * @var string
     */
    protected string $js_dynamicsuite = '/dynamicsuite/client/js/dynamicsuite.min.js';

    /**
     * Header for the nav area.
     *
     * @var string
     */
    protected string $nav_header_text = 'Dynamic Suite';

    /**
     * The path used for user login/logout.
     *
     * @var string
     */
    protected string $nav_login_path = '/login';

    /**
     * The icon class to use for the user action area.
     *
     * @var string
     */
    protected string $action_links_icon = 'fa-user';

    /**
     * Database data source name.
     *
     * @var string
     */
    protected string $db_dsn = 'mysql:unix_socket=/tmp/mysql.sock;dbname=dynamicsuite';

    /**
     * Database username.
     *
     * @var string
     */
    protected string $db_user = '';

    /**
     * Database password.
     *
     * @var string
     */
    protected string $db_pass = '';

    /**
     * Database additional PDO options.
     *
     * @var array
     */
    protected array $db_options = [
        PDO::ATTR_TIMEOUT => 1,
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    /**
     * The host that the memcached server is running on.
     *
     * @var string
     */
    protected string $memcached_host = '127.0.0.1';

    /**
     * The port that the memcached server is listening on.
     *
     * @var int
     */
    protected int $memcached_port = 11211;

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