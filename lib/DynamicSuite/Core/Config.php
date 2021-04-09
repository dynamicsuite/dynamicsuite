<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite\Core
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused
 */

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
 * @property string $document_template
 * @property string $stylesheet_template
 * @property string $script_template
 * @property string $nav_template
 * @property string $nav_group_template
 * @property string $nav_single_template
 * @property string $nav_sublink_template
 * @property string $about_template
 * @property string $error_404_template
 * @property string $error_500_template
 * @property string $css_fontawesome
 * @property string $css_style
 * @property string $css_theme
 * @property string $js_dynamicsuite
 * @property string $nav_header_text
 * @property string $action_area_icon
 * @property string $db_dsn
 * @property string $db_user
 * @property string $db_pass
 * @property array $db_options
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
    protected string $language = 'en-US';

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
     * The icon class to use for the action area.
     *
     * @var string
     */
    protected string $action_area_icon = 'fa-user';

    /**
     * Database data source name.
     *
     * @var string
     */
    protected string $db_dsn = 'mysql:unix_socket=/tmp/mysql.sock;dbname=dynamicsuite;charset=utf8mb4';

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
     * Config constructor.
     *
     * @param string $package_id
     */
    public function __construct(string $package_id)
    {
        parent::__construct($package_id);
    }

}