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

namespace DynamicSuite;

/**
 * Class View.
 *
 * @package DynamicSuite
 * @property PackageView $package
 * @property Template $document
 * @property Template $stylesheet
 * @property Template $script
 * @property Template $error404
 * @property Template $error500
 * @property Template $about
 * @property Template $nav
 * @property Template $nav_group
 * @property Template $nav_single
 * @property Template $nav_sublink
 * @property string $core_css
 * @property string $core_js
 */
class View extends InstanceMember
{

    /**
     * Package view metadata.
     *
     * @var PackageView
     */
    protected $package;

    /**
     * Document template.
     *
     * @var Template
     */
    protected $document;

    /**
     * CSS stylesheet template.
     *
     * @var Template
     */
    protected $stylesheet;

    /**
     * JS script template.
     *
     * @var Template
     */
    protected $script;

    /**
     * 404 error page template.
     *
     * @var Template
     */
    protected $error404;

    /**
     * 500 error page template.
     *
     * @var Template
     */
    protected $error500;

    /**
     * About Dynamic Suite template.
     *
     * @var Template
     */
    protected $about;

    /**
     * Navigation template.
     *
     * @var Template
     */
    protected $nav;

    /**
     * Navigation group template.
     *
     * @var Template
     */
    protected $nav_group;

    /**
     * Navigation single view template.
     *
     * @var Template
     */
    protected $nav_single;

    /**
     * Navigation sublink template.
     *
     * @var Template
     */
    protected $nav_sublink;

    /**
     * HTML block of core CSS resources.
     *
     * @var string
     */
    protected $core_css;

    /**
     * HTML block of core JS resources.
     *
     * @var string
     */
    protected $core_js;

    /**
     * View constructor.
     *
     * @param Instance $ds
     * @return void
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
        $this->initTemplates();
        $this->initCoreResources();
    }

    /**
     * Initialize and load all templates.
     *
     * @return View
     */
    public function initTemplates(): View
    {
        if (!is_readable($this->ds->cfg->document_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->document_template}", E_USER_ERROR);
        } else {
            $this->document = new Template(File::contents($this->ds->cfg->document_template));
        }
        if (!is_readable($this->ds->cfg->stylesheet_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->stylesheet_template}", E_USER_ERROR);
        } else {
            $this->stylesheet = new Template(File::contents($this->ds->cfg->stylesheet_template));
        }
        if (!is_readable($this->ds->cfg->script_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->script_template}", E_USER_ERROR);
        } else {
            $this->script = new Template(File::contents($this->ds->cfg->script_template));
        }
        if (!is_readable($this->ds->cfg->error_404_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->error_404_template}", E_USER_ERROR);
        } else {
            $this->error404 = new Template(File::contents($this->ds->cfg->error_404_template));
        }
        if (!is_readable($this->ds->cfg->error_500_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->error_500_template}", E_USER_ERROR);
        } else {
            $this->error500 = new Template(File::contents($this->ds->cfg->error_500_template));
        }
        if (!is_readable($this->ds->cfg->about_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->about_template}", E_USER_ERROR);
        } else {
            $this->about = new Template(File::contents($this->ds->cfg->about_template));
        }
        if (!is_readable($this->ds->cfg->nav_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->nav_template}", E_USER_ERROR);
        } else {
            $this->nav = new Template(File::contents($this->ds->cfg->nav_template));
        }
        if (!is_readable($this->ds->cfg->nav_group_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->nav_group_template}", E_USER_ERROR);
        } else {
            $this->nav_group = new Template(File::contents($this->ds->cfg->nav_group_template));
        }
        if (!is_readable($this->ds->cfg->nav_single_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->nav_single_template}", E_USER_ERROR);
        } else {
            $this->nav_single = new Template(File::contents($this->ds->cfg->nav_single_template));
        }
        if (!is_readable($this->ds->cfg->nav_sublink_template)) {
            trigger_error("Template not readable: {$this->ds->cfg->nav_sublink_template}", E_USER_ERROR);
        } else {
            $this->nav_sublink = new Template(File::contents($this->ds->cfg->nav_sublink_template));
        }
        $this->document->replace(['{{charset}}' => $this->ds->cfg->charset]);
        $this->document->replace(['{{language}}' => $this->ds->cfg->language]);
        return $this;
    }

    /**
     * Initialize core resources.
     *
     * @return void
     */
    public function initCoreResources()
    {
        $css = array_unique(array_merge([
            $this->ds->cfg->css_fontawesome,
            $this->ds->cfg->css_style,
            $this->ds->cfg->css_theme
        ], $this->ds->packages->resources->css));
        $this->core_css = '';
        foreach ($css as $href) {
            $stylesheet = clone $this->stylesheet;
            $this->core_css .= $stylesheet->replace(['{{href}}' => $href,])->getContents();
        }
        $js = array_unique(array_merge([
            $this->ds->cfg->js_dynamicsuite
        ], $this->ds->packages->resources->js));
        $this->core_js = '';
        foreach ($js as $src) {
            $script = clone $this->script;
            $this->core_js .= $script->replace(['{{src}}' => $src])->getContents();
        }
    }

    /**
     * Set the package view based on the URI.
     *
     * @param string $uri
     * @return bool
     */
    public function setPackageView($uri = null): bool
    {
        $uri = $uri ?? $this->ds->request->uri_string;
        foreach ($this->ds->packages->views as $view_path => $package_view) {
            if ($this->ds->request->uriIs($view_path, $uri)) {
                $this->package = $package_view;
                return true;
            }
        }
        $pos = strrpos($uri, '/');
        if ($pos === false) return false;
        return $this->setPackageView(substr($uri, 0, $pos));
    }

    /**
     * Update the view to show the 404 error page.
     *
     * @return void
     */
    public function error404()
    {
        $this->document->replace([
            '{{title}}' => 'Page Not Found',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = $this->error404;
        $body->replace(['{{version}}' => DS_VERSION]);
        $this->document->replace(['{{body}}' => $body->getContents()]);
        http_response_code(404);
    }

    /**
     * Update the view to show the 500 error page.
     *
     * @return void
     */
    public function error500()
    {
        $this->document->replace([
            '{{title}}' => 'Internal Server Error',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = $this->error500;
        $body->replace(['{{version}}' => DS_VERSION]);
        $this->document->replace(['{{body}}' => $body->getContents()]);
        http_response_code(500);
    }

    /**
     * Update the view to show the about page.
     *
     * @return void
     */
    public function about()
    {
        $this->document->replace([
            '{{title}}' => 'About Dynamic Suite',
            '{{css}}' => '',
            '{{js}}' => ''
        ]);
        $body = $this->about;
        $body->replace(['{{version}}' => DS_VERSION]);
        $this->document->replace(['{{body}}' => $body->getContents()]);
    }

    /**
     * Set the document to a navigable document.
     *
     * @return View
     */
    public function setNavigable(): View
    {
        $this->nav->replace([
            '{{nav-header-view}}' => $this->ds->cfg->nav_header_view,
            '{{nav-header-text}}' => $this->ds->cfg->nav_header_text,
            '{{nav-header-path}}' => $this->ds->cfg->default_view,
            '{{login-path}}' => $this->ds->cfg->nav_login_path,
            '{{nav-footer-version}}' => DS_VERSION,
            '{{view-header}}' => $this->ds->view->package->title,
            '{{hide-logout}}' => $this->ds->view->package->hide_logout_button
                ? ' class="ds-hide"'
                : '',
            '{{nav-links}}' => $this->generateNavLinks(),
        ]);
        $this->document->replace(['{{body}}' => $this->nav->getContents()]);
        return $this;
    }

    /**
     * Set the resources for the view (CSS, JS).
     *
     * @return View
     */
    public function setViewResources(): View
    {
        $this->document->replace(['{{title}}' => ($this->package->title ?? $this->ds->cfg->default_title)]);
        $css = '';
        foreach ($this->package->resources->css as $href) {
            $stylesheet = clone $this->stylesheet;
            $css .= $stylesheet->replace(['{{href}}' => $href])->getContents();
        }
        $this->document->replace(['{{css}}' => $this->core_css . $css]);
        $js = '';
        foreach ($this->package->resources->js as $src) {
            $script = clone $this->script;
            $js .= $script->replace(['{{src}}' => $src])->getContents();
        }
        $this->document->replace(['{{js}}' => $this->core_js . $js]);
        return $this;
    }

    /**
     * Generate navigation links (nav tree).
     *
     * @return string
     */
    public function generateNavLinks(): string
    {
        $nav_links = '';
        /** @var $super PackageNavEntry */
        foreach ($this->ds->packages->nav_tree as $super) {
            if (!$super->public && !$this->ds->session->checkPermissions($super->permissions)) continue;
            if ($super->hasChildren()) {
                $superlink = clone $this->nav_group;
                $superlink->replace([
                    '{{icon}}' => $super->icon,
                    '{{name}}' => $super->name
                ]);
                $sublinks = '';
                $active = false;
                /** @var $sub PackageNavEntry */
                foreach ($super->children as $sub) {
                    if (!$sub->public && !$this->ds->session->checkPermissions($sub->permissions)) continue;
                    if ($this->package->uri_path === $sub->uri_path) {
                        $active_class = ' ds-nav-active';
                        $active = true;
                    } else {
                        $active_class = '';
                    }
                    $sublink = clone $this->nav_sublink;
                    $sublink->replace([
                        '{{active}}' => $active_class,
                        '{{path}}' => $sub->uri_path,
                        '{{icon}}' => $sub->icon,
                        '{{name}}' => $sub->name
                    ]);
                    $sublinks .= $sublink->getContents();
                }
                $superlink->replace([
                    '{{active}}' => $active ? ' ds-nav-active' : '',
                    '{{chevron}}' => $active ? 'fa-chevron-down' : 'fa-chevron-right',
                    '{{sublinks-active}}' => $active ? 'ds-show' : 'ds-hide',
                    '{{sublinks}}' => $sublinks
                ]);
            } else {
                $superlink = clone $this->nav_single;
                $superlink->replace([
                    '{{active}}' => $this->package->uri_path === $super->uri_path ? ' ds-nav-active' : '',
                    '{{path}}' => $super->uri_path,
                    '{{icon}}' => $super->icon,
                    '{{name}}' => $super->name
                ]);
            }
            $nav_links .= $superlink->getContents();
        }
        return $nav_links;
    }

}