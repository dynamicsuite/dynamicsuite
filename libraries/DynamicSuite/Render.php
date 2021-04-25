<?php
/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 * @noinspection PhpUnused PhpNoReturnAttributeCanBeAddedInspection
 */

namespace DynamicSuite;
use DynamicSuite\Util\Template;

/**
 * Class View.
 *
 * @package DynamicSuite
 */
final class Render
{

    /**
     * Meta description to use on the document.
     *
     * @var string|null
     */
    public static ?string $meta_description = null;

    /**
     * The main document to render.
     *
     * @var Template
     */
    public static Template $document_template;

    /**
     * Content to serve on "about" request.
     *
     * @var Template
     */
    public static Template $about_template;

    /**
     * Content to serve on 404 error.
     *
     * @var Template
     */
    public static Template $error_404_template;

    /**
     * Content to serve on 500 error.
     *
     * @var Template
     */
    public static Template $error_500_template;

    /**
     * Data to be rendered as part of the window on the client.
     *
     * @var array
     */
    public static array $window_data = [
        'has_session' => false,
        'hide_overlay' => true,
        'default_view' => null,
        'overlay_nav_footer_text' => null,
        'overlay_nav_footer_view' => null,
        'overlay_nav_tree' => null,
        'overlay_title' => null,
        'overlay_actions' => null,
        'misc' => null
    ];

    /**
     * Initialize the render.
     *
     * @return void
     */
    public static function init(): void
    {

        // Caching hash
        $hash = 'ds' . crc32(__FILE__);

        // Set meta description to the DS default (if one is not set already)
        if (!self::$meta_description) {
            self::$meta_description = DynamicSuite::$cfg->meta_description;
        }

        // Get from cache or load
        if (DS_CACHING && apcu_exists($hash) && $cache = apcu_fetch($hash)) {
            self::$document_template = $cache['document'];
            self::$about_template = $cache['about'];
            self::$error_404_template = $cache['error_404'];
            self::$error_500_template = $cache['error_500'];
        } else {

            // Load templates
            foreach (['document_template', 'about_template', 'error_404_template', 'error_500_template'] as $template) {
                $path = DS_ROOT_DIR . DynamicSuite::$cfg->$template;
                if (!is_readable($path)) {
                    error_log("Template not readable: $path");
                } else {
                    self::$$template = new Template(file_get_contents($path));
                }
            }

            // Update the DS version for the error templates and about template
            self::$about_template->replace([
                '{{version}}' => DS_VERSION,
                '{{favicon}}' => DynamicSuite::$cfg->favicon . '?v=' . DS_VERSION
            ]);
            self::$error_404_template->replace([
                '{{version}}' => DS_VERSION
            ]);
            self::$error_500_template->replace([
                '{{version}}' => DS_VERSION
            ]);

            // Set global CSS
            $css_global = [
                DynamicSuite::$cfg->css_fontawesome . '?v' . DS_VERSION,
                DynamicSuite::$cfg->css_dynamicsuite . '?v' . DS_VERSION
            ];
            foreach (Packages::$css as $path) {
                if (!in_array($path, $css_global)) {
                    $css_global[] = $path;
                }
            }
            $css_template = '';
            foreach ($css_global as $path) {
                $css_template .=
                    "<link rel=\"stylesheet\" href=\"$path\">";
            }

            // Set global JS
            $js_global = [
                DynamicSuite::$cfg->js_vue . '?v' . DS_VERSION,
                DynamicSuite::$cfg->js_dynamicsuite . '?v' . DS_VERSION
            ];
            foreach (Packages::$js as $path) {
                if (!in_array($path, $js_global)) {
                    $js_global[] = $path;
                }
            }
            $js_template = '';
            foreach ($js_global as $path) {
                $js_template .= "<script src=\"$path\"></script>";
            }

            // Update the template
            self::$document_template->replace([
                '{{language}}' => DynamicSuite::$cfg->language,
                '{{charset}}' => DynamicSuite::$cfg->charset,
                '{{favicon}}' => DynamicSuite::$cfg->favicon . '?v=' . DS_VERSION,
                '<!--{{css_global}}-->' => $css_template,
                '<!--{{js_global}}-->' => $js_template
            ]);

            // Clean up the template
            $replace = [
                '/\>[^\S ]+/s' => '>',
                '/[^\S ]+\</s' => '<',
                '/(\s)+/s' => '\\1',
                '/<!--suppress(.|\s)*?-->/' => '',
                '~>\s+<~' => '><'
            ];
            self::$document_template->contents = preg_replace(
                array_keys($replace),
                array_values($replace),
                self::$document_template->contents
            );

            // Cache if enabled
            if (DS_CACHING) {
                $store = apcu_store($hash, [
                    'document' => self::$document_template,
                    'about' => self::$about_template,
                    'error_404' => self::$error_404_template,
                    'error_500' => self::$error_500_template
                ]);
                if (!$store) {
                    error_log('Error saving "Render" in cache, check server config');
                }
            }

        }
    }

    /**
     * Generate the navigation tree.
     *
     * @return array
     */
    public static function generateNavTree(): array
    {

        // Navigation tree to return
        $tree = [];

        // Only views can generate a navigation tree
        if (!DS_VIEW) {
            return $tree;
        }

        // Vue key
        $key = 0;

        // Iterate on loaded views
        foreach (Packages::$views as $view_id => $view) {

            // View is not navigable
            if (!$view->navigable) {
                continue;
            }

            // Session does not have the proper permissions for the view
            if (!$view->public && !Session::checkPermissions($view->permissions)) {
                continue;
            }

            // Add to existing nav group if assigned to nav group
            if ($view->nav_group && isset($tree[$view->nav_group])) {
                $tree[$view->nav_group]['views'][$view_id] = [
                    'path' => $view_id,
                    'icon' => $view->nav_icon,
                    'name' => $view->nav_name,
                    'active' => false,
                    'key' => 'nav_link_' . $key++
                ];
            }

            // Add to new nav group if assigned to nav group
            elseif ($view->nav_group && isset(Packages::$nav_groups[$view->nav_group])) {
                if (
                    !Packages::$nav_groups[$view->nav_group]->public &&
                    !Session::checkPermissions(Packages::$nav_groups[$view->nav_group]->permissions)
                ) {
                    continue;
                }
                $tree[$view->nav_group] = [
                    'icon' => Packages::$nav_groups[$view->nav_group]->icon,
                    'name' => Packages::$nav_groups[$view->nav_group]->name,
                    'views' => [],
                    'active' => false,
                    'selected' => false,
                    'nav_group' => $view->nav_group,
                    'key' => 'nav_link_' . $key++
                ];
                $tree[$view->nav_group]['views'][$view_id] = [
                    'path' => $view_id,
                    'icon' => $view->nav_icon,
                    'name' => $view->nav_name,
                    'active' => false,
                    'key' => 'nav_link_' . $key++
                ];
            }

            // Add as a root view
            elseif (!$view->nav_group) {
                $tree[$view_id] = [
                    'path' => $view_id,
                    'icon' => $view->nav_icon,
                    'name' => $view->nav_name,
                    'active' => false,
                    'selected' => false,
                    'nav_group' => null,
                    'key' => 'nav_link_' . $key++
                ];
            }

            // Orphaned view
            else {
                error_log("View [$view->package_id:$view_id] belongs to an unknown nav group '$view->nav_group'");
            }

        }

        // Format tree
        $tree = array_values($tree);
        foreach ($tree as $key => $value) {
            if (isset($value['views'])) {
                $tree[$key]['views'] = array_values($value['views']);
            }
        }

        // Return the tree
        return $tree;

    }

    /**
     * Render the about page.
     *
     * @return void
     */
    public static function about(): void
    {
        self::$document_template->replace([
            '{{meta_description}}' => DynamicSuite::$cfg->meta_description,
            '{{title}}' => DynamicSuite::$cfg->about_title,
            '<!--{{window_data}}-->' => '',
            '<!--{{css_variable}}-->' => '',
            '<!--{{js_variable}}-->' => '',
            '{{body}}' => self::$about_template->contents
        ]);
        echo self::$document_template->contents;
        exit;
    }

    /**
     * Render the 404 error page.
     *
     * @return void
     */
    public static function error404(): void
    {
        self::$document_template->replace([
            '{{meta_description}}' => '404',
            '{{title}}' => DynamicSuite::$cfg->error_404_title,
            '<!--{{window_data}}-->' => '',
            '<!--{{css_variable}}-->' => '',
            '<!--{{js_variable}}-->' => '',
            '{{body}}' => self::$error_404_template->contents
        ]);
        echo self::$document_template->contents;
        if (DynamicSuite::$cfg->error_404_logging) {
            error_log('404: ' . URL::$as_string);
        }
        exit;
    }

    /**
     * Render the 500 error page.
     *
     * @return void
     */
    public static function error500(): void
    {
        self::$document_template->replace([
            '{{meta_description}}' => '500',
            '{{title}}' => DynamicSuite::$cfg->error_500_title,
            '<!--{{window_data}}-->' => '',
            '<!--{{css_variable}}-->' => '',
            '<!--{{js_variable}}-->' => '',
            '{{body}}' => self::$error_500_template->contents
        ]);
        echo self::$document_template->contents;
        exit;
    }

}