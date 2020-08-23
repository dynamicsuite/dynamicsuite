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
/** @noinspection PhpIncludeInspection */

namespace DynamicSuite\Core;
use DynamicSuite\Package\NavGroup;
use DynamicSuite\Package\Packages;
use DynamicSuite\Util\Template;

/**
 * Class View.
 *
 * @package DynamicSuite\View
 * @property \DynamicSuite\Package\View $structure
 * @property Template $document
 * @property Template $document_template
 * @property Template $stylesheet_template
 * @property Template $script_template
 * @property Template $error_404_template
 * @property Template $error_500_template
 * @property Template $about_template
 * @property Template $nav
 * @property Template $nav_template
 * @property Template $nav_group_template
 * @property Template $nav_single_template
 * @property Template $nav_sublink_template
 * @property string $core_css
 * @property string $core_js
 */
final class View
{

    /**
     * Package view structure.
     *
     * @var \DynamicSuite\Package\View
     */
    protected \DynamicSuite\Package\View $structure;

    /**
     * Viewable document.
     *
     * @var Template
     */
    protected Template $document;

    /**
     * Viewable document template.
     *
     * @var Template
     */
    protected Template $document_template;

    /**
     * CSS stylesheet template.
     *
     * @var Template
     */
    protected Template $stylesheet_template;

    /**
     * JS script template.
     *
     * @var Template
     */
    protected Template $script_template;

    /**
     * 404 error page template.
     *
     * @var Template
     */
    protected Template $error_404_template;

    /**
     * 500 error page template.
     *
     * @var Template
     */
    protected Template $error_500_template;

    /**
     * About Dynamic Suite template.
     *
     * @var Template
     */
    protected Template $about_template;

    /**
     * Viewable navigation template.
     *
     * @var Template
     */
    protected Template $nav;

    /**
     * Navigation template.
     *
     * @var Template
     */
    protected Template $nav_template;

    /**
     * Navigation group template.
     *
     * @var Template
     */
    protected Template $nav_group_template;

    /**
     * Navigation single view template.
     *
     * @var Template
     */
    protected Template $nav_single_template;

    /**
     * Navigation sublink template.
     *
     * @var Template
     */
    protected Template $nav_sublink_template;

    /**
     * HTML block of core CSS resources.
     *
     * @var string
     */
    protected string $core_css;

    /**
     * HTML block of core JS resources.
     *
     * @var string
     */
    protected string $core_js;

    /**
     * Parameter getter magic method.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return $this->$property;
    }

    /**
     * Reset the view.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->document = clone $this->document_template;
        $this->nav = clone $this->nav_template;
        $this->initCoreResources();
    }

    /**
     * Initialize and load all templates.
     *
     * @return void
     */
    public function initTemplates()
    {
        foreach ([
            'document_template',
            'stylesheet_template',
            'script_template',
            'error_404_template',
            'error_500_template',
            'about_template',
            'nav_template',
            'nav_group_template',
            'nav_single_template',
            'nav_sublink_template'
        ] as $template) {
            if (!is_readable(DS_ROOT_DIR . '/' . DynamicSuite::$cfg->$template)) {
                error_log('[View] Template not readable: ' . DynamicSuite::$cfg->$template, E_USER_ERROR);
            } else {
                $this->$template = new Template(file_get_contents(DS_ROOT_DIR . '/' . DynamicSuite::$cfg->$template));
            }
        }
        $this->document_template->replace([
            '{{charset}}' => DynamicSuite::$cfg->charset
        ]);
        $this->document_template->replace([
            '{{language}}' => DynamicSuite::$cfg->language
        ]);
    }

    /**
     * Initialize core resources.
     *
     * @return void
     */
    public function initCoreResources()
    {
        $css = array_unique(array_merge([
            DynamicSuite::$cfg->css_fontawesome,
            DynamicSuite::$cfg->css_style,
            DynamicSuite::$cfg->css_theme
        ], Packages::$global['css']));
        $this->core_css = '';
        foreach ($css as $href) {
            $stylesheet = clone $this->stylesheet_template;
            $this->core_css .= $stylesheet->replace([
                '{{href}}' => $href
            ])->contents;
        }
        $js = array_unique(array_merge([DynamicSuite::$cfg->js_dynamicsuite], Packages::$global['js']));
        $this->core_js = '';
        foreach ($js as $src) {
            $script = clone $this->script_template;
            $this->core_js .= $script->replace([
                '{{src}}' => $src
            ])->contents;
        }
    }

    /**
     * Set the package view based on the URL.
     *
     * @param string|null $url
     * @return bool
     */
    public function setPackageView(?string $url = null): bool
    {
        $url = $url ?? Request::$url_string;
        foreach (Packages::$views as $path => $view) {
            if (Request::urlIs($path, $url)) {
                $this->structure = $view;
                $this->structure->autoload = array_merge(
                    Packages::$loaded[$this->structure->package_id]->local['autoload'], $this->structure->autoload
                );
                $this->structure->init = array_merge(
                    Packages::$loaded[$this->structure->package_id]->local['init'], $this->structure->init
                );
                $this->structure->js = array_merge(
                    Packages::$loaded[$this->structure->package_id]->local['js'], $this->structure->js
                );
                $this->structure->css = array_merge(
                    Packages::$loaded[$this->structure->package_id]->local['css'], $this->structure->css
                );
                return true;
            }
        }
        $pos = strrpos($url, '/');
        if ($pos === false) {
            return false;
        }
        return $this->setPackageView(substr($url, 0, $pos));
    }

    /**
     * Update the view to show the 404 error page.
     *
     * @return void
     */
    public function error404()
    {
        $this->document = clone $this->document_template;
        $this->document->replace([
            '{{title}}' => 'Page Not Found',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = clone $this->error_404_template;
        $body->replace([
            '{{version}}' => DS_VERSION
        ]);
        $this->document->replace([
            '{{body}}' => $body->contents
        ]);
        http_response_code(404);
    }

    /**
     * Update the view to show the 500 error page.
     *
     * @return void
     */
    public function error500()
    {
        $this->document = clone $this->document_template;
        $this->document->replace([
            '{{title}}' => 'Internal Server Error',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = clone $this->error_500_template;
        $body->replace(['{{version}}' => DS_VERSION]);
        $this->document->replace([
            '{{body}}' => $body->contents
        ]);
        http_response_code(500);
    }

    /**
     * Update the view to show the about page.
     *
     * @return void
     */
    public function about()
    {
        $this->document = clone $this->document_template;
        $this->document->replace([
            '{{title}}' => 'About Dynamic Suite',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = clone $this->about_template;
        $body->replace(['{{version}}' => DS_VERSION]);
        $this->document->replace([
            '{{body}}' => $body->contents
        ]);
    }

    /**
     * Set the document to a navigable document.
     *
     * @return void
     */
    public function setNavigable(): void
    {
        $action_area = '';
        foreach (Packages::$action_groups as $group) {
            $action_links = '';
            foreach (Packages::$action_links as $text => $action) {
                if ($action->group !== $group) {
                    continue;
                }
                if (isset($action->permissions)) {
                    if (!Session::checkPermissions($action['permissions'])) {
                        continue;
                    }
                }
                if ($action->type === 'static') {
                    if ($action->ref) {
                        $value = $action->value .= '?ref=' . Request::$url_string;
                    } else {
                        $value = $action->value;
                    }
                    $action_links .= "<li><a href=\"$value\">$text</a></li>";
                } elseif ($action->type === 'dynamic') {
                    ob_start();
                    require $action->value;
                    $content = ob_get_clean();
                    $action_links .= "<li>$content</li>";
                }
            }
            if (mb_strlen($action_links)) {
                $action_area .= "<ul>$action_links</ul>";
            }
        }
        $this->nav->replace([
            '{{nav-header-text}}' => DynamicSuite::$cfg->nav_header_text,
            '{{nav-header-path}}' => DynamicSuite::$cfg->nav_header_view,
            '{{login-path}}' => DynamicSuite::$cfg->nav_login_path,
            '{{nav-footer-version}}' => DS_VERSION,
            '{{view-header}}' => $this->structure->title,
            '{{action-links-icon}}' => DynamicSuite::$cfg->action_links_icon,
            '{{hide-user-actions}}' => $this->structure->hide_user_actions
                ? ' class="ds-hide"'
                : '',
            '{{action-links}}' => $action_area,
            '{{hide-logout-link}}' => $this->structure->hide_logout_button
                ? ' class="ds-hide"'
                : '',
            '{{login-view}}' => DynamicSuite::$cfg->nav_login_path,
            '{{nav-links}}' => $this->generateNavLinks(),
        ]);
        $this->document->replace([
            '{{body}}' => $this->nav->contents
        ]);
    }

    /**
     * Set the resources for the view (CSS, JS).
     *
     * @return void
     */
    public function setViewResources(): void
    {
        $this->document->replace([
            '{{title}}' => $this->structure->title
        ]);
        $css = '';
        foreach ($this->structure->css as $href) {
            $stylesheet = clone $this->stylesheet_template;
            $css .= $stylesheet->replace([
                '{{href}}' => $href
            ])->contents;
        }
        $this->document->replace([
            '{{css}}' => $this->core_css . $css
        ]);
        $js = '';
        foreach ($this->structure->js as $src) {
            $script = clone $this->script_template;
            $js .= $script->replace([
                '{{src}}' => $src
            ])->contents;
        }
        $this->document->replace([
            '{{js}}' => $this->core_js . $js
        ]);
    }

    /**
     * Generate navigation links (nav tree).
     *
     * @return string
     */
    public function generateNavLinks(): string
    {
        $tree = [];
        foreach (Packages::$nav_groups as $group_id => $group) {
            if (!$group->public && !Session::checkPermissions($group->permissions)) {
                unset(Packages::$nav_groups[$group_id]);
            }
        }
        foreach (Packages::$views as $view_id => $view) {
            if (!$view->navigable) {
                continue;
            }
            if (!$view->public && !Session::checkPermissions($view->permissions)) {
                continue;
            }
            if ($view->nav_group && isset($tree["nav_group.$view->nav_group"])) {
                $tree["nav_group.$view->nav_group"]->views[] = $view;
            } elseif ($view->nav_group && isset(Packages::$nav_groups[$view->nav_group])) {
                $tree["nav_group.$view->nav_group"] = Packages::$nav_groups[$view->nav_group];
                $tree["nav_group.$view->nav_group"]->views[] = $view;
            } elseif(!$view->nav_group) {
                $tree["view.$view->package_id.$view_id"] = $view;
            } else {
                error_log("[Structure] Package \"$view->package_id\" view \"$view_id\" belongs to unknown nav group");
            }
        }
        $html = '';
        foreach ($tree as $branch) {
            if ($branch instanceof NavGroup && empty($branch->views)) {
                continue;
            } elseif ($branch instanceof NavGroup) {
                $superlink = clone $this->nav_group_template;
                $superlink->replace([
                    '{{icon}}' => $branch->icon,
                    '{{name}}' => $branch->name
                ]);
                $active = false;
                $sublinks = '';
                /** @var \DynamicSuite\Package\View $view */
                foreach ($branch->views as $view) {
                    if ($view->view_id === $this->structure->view_id) {
                        $active_class = ' ds-nav-active';
                        $active = true;
                    } else {
                        $active_class = '';
                    }
                    $sublink = clone $this->nav_sublink_template;
                    $sublink->replace([
                        '{{active}}' => $active_class,
                        '{{path}}' => $view->view_id,
                        '{{icon}}' => $view->nav_icon,
                        '{{name}}' => $view->nav_name
                    ]);
                    $sublinks .= $sublink->contents;
                }
                $superlink->replace([
                    '{{active}}' => $active ? ' ds-nav-active' : '',
                    '{{chevron}}' => $active ? 'fa-chevron-down' : 'fa-chevron-right',
                    '{{sublinks-active}}' => $active ? 'ds-show' : 'ds-hide',
                    '{{sublinks}}' => $sublinks
                ]);
            } else {
                $superlink = clone $this->nav_single_template;
                $superlink->replace([
                    '{{active}}' => $this->structure->view_id === $branch->view_id ? ' ds-nav-active' : '',
                    '{{path}}' => $branch->view_id,
                    '{{icon}}' => $branch->nav_icon,
                    '{{name}}' => $branch->nav_name
                ]);
            }
            $html .= $superlink->contents;
        }
        return $html;
    }

    /**
     * Set pre-rendered page data.
     *
     * @param array $data
     * @return void
     */
    public static function setPageData(array $data): void
    {
        echo '<script>let DS_PAGE_DATA = ' . json_encode($data) . ';</script>';
    }

}