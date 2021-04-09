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
 * @property string $nav_header_text
 * @property string $nav_header_view
 * @property string $header_action_icon
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
 * @property string $db_dsn
 * @property string $db_user
 * @property string $db_pass
 * @property array $db_options
 */
final class Config extends GlobalConfig
{

    /**
     * Config constructor.
     *
     * @return void
     */
    public function __construct(
        string $package_id,
        protected bool $debug_mode = false,
        protected array $packages = [],
        protected string $charset = 'utf-8',
        protected string $language = 'en-US',
        protected string $default_view = '/dynamicsuite/about',
        protected string $nav_header_text = 'Dynamic Suite',
        protected string $nav_header_view = '/dynamicsuite/about',
        protected string $header_action_icon = 'fa-user',
        protected string $document_template = 'client/templates/document.html',
        protected string $stylesheet_template = 'client/templates/stylesheet.html',
        protected string $script_template = 'client/templates/script.html',
        protected string $nav_template = 'client/templates/nav.html',
        protected string $nav_group_template = 'client/templates/nav_group.html',
        protected string $nav_single_template = 'client/templates/nav_single.html',
        protected string $nav_sublink_template = 'client/templates/nav_sublink.html',
        protected string $about_template = 'client/templates/about.html',
        protected string $error_404_template = 'client/templates/errors/404.html',
        protected string $error_500_template = 'client/templates/errors/500.html',
        protected string $css_fontawesome = '/dynamicsuite/client/css/fontawesome.min.css',
        protected string $css_style = '/dynamicsuite/client/css/ds-style.min.css',
        protected string $css_theme = '/dynamicsuite/client/css/ds-theme.min.css',
        protected string $js_dynamicsuite = '/dynamicsuite/client/js/dynamicsuite.min.js',
        protected string $db_dsn = 'mysql:unix_socket=/tmp/mysql.sock;dbname=dynamicsuite;charset=utf8mb4',
        protected string $db_user = '',
        protected string $db_pass = '',
        protected array $db_options = [
            PDO::ATTR_TIMEOUT => 1,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ) {
        parent::__construct($package_id);
    }

}