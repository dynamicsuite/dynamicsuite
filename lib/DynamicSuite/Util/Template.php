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

namespace DynamicSuite\Util;

/**
 * Class Template.
 *
 * @package DynamicSuite\Util
 * @property string $contents
 */
class Template
{

    /**
     * Contents of the template.
     *
     * @var string
     */
    public string $contents;

    /**
     * Template constructor.
     *
     * @param string $contents
     * @return void
     */
    public function __construct(string $contents = '')
    {
        $this->contents = $contents;
    }

    /**
     * Prepend content to the template.
     *
     * @param string $content
     * @return Template
     */
    public function prepend(string $content): Template
    {
        $this->contents = $content . $this->contents;
        return $this;
    }

    /**
     * Append content to the template.
     *
     * @param string $content
     * @return Template
     */
    public function append(string $content): Template
    {
        $this->contents = $this->contents . $content;
        return $this;
    }

    /**
     * Search and replace a string(s) in template.
     *
     * @param mixed $replace
     * @return Template
     */
    public function replace(array $replace): Template
    {
        $this->contents = str_replace(array_keys($replace), array_values($replace), $this->contents);
        return $this;
    }

}