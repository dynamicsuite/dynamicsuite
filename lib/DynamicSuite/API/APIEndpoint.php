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

namespace DynamicSuite\API;
use DynamicSuite\Base\InstanceMember;
use DynamicSuite\Core\Instance;
use DynamicSuite\Package\API;

/**
 * Class APIEndpoint.
 *
 * @package DynamicSuite\API
 * @property APIRequest|null $request
 * @property API|null $structure
 */
class APIEndpoint extends InstanceMember
{

    /**
     * Current API request.
     *
     * @var APIRequest
     */
    protected ?APIRequest $request = null;

    /**
     * Current API structure.
     *
     * @var API
     */
    protected ?API $structure = null;

    /**
     * API constructor.
     *
     * @param Instance $ds
     */
    public function __construct(Instance $ds)
    {
        parent::__construct($ds);
    }

    /**
     * Standard content type to use for API requests.
     *
     * @var string
     */
    public const CONTENT_TYPE = 'application/json';

    /**
     * Input stream to parse for API headers.
     *
     * @var string
     */
    public const INPUT_STREAM = 'php://input';

    /**
     * Build an external request based on the input stream.
     *
     * @return bool|APIRequest
     */
    public function buildExternalRequest()
    {
        $post = json_decode(file_get_contents(self::INPUT_STREAM), true);
        if (empty($post)) {
            trigger_error('External API request received with no input', E_USER_WARNING);
            return false;
        } elseif (!isset($post['package_id'])) {
            trigger_error('External API request received with no package ID', E_USER_WARNING);
            return false;
        } elseif (!isset($post['api_id'])) {
            trigger_error('External API request received with no API ID', E_USER_WARNING);
            return false;
        }
        return new APIRequest($post['package_id'], $post['api_id'], $post['data'] ?? null);
    }

    /**
     * Call an API request.
     *
     * @param APIRequest $request
     * @return APIResponse
     */
    public function call(APIRequest $request): APIResponse
    {
        $prefix = "[API] [$request->package_id:$request->api_id]";
        $this->request = $request;
        $this->structure = $this->ds->packages->apis[$this->request->package_id][$this->request->api_id] ?? null;
        $response = new APIResponse();
        if (!$this->structure) {
            trigger_error("$prefix API not found", E_USER_WARNING);
            return $response;
        }
        foreach ($this->structure->post as $key) {
            if (!array_key_exists($key, $request->data)) {
                trigger_error("$prefix Missing required post key: $key", E_USER_WARNING);
                return $response;
            }
        }
        if (!$this->structure->public && (!$this->ds->session->checkPermissions($this->structure->permissions))) {
            trigger_error("$prefix Authentication required", E_USER_WARNING);
            return $response;
        }
        if (!defined('DS_PKG_DIR')) {
            define('DS_PKG_DIR', DS_ROOT_DIR . "/packages/{$this->structure->package_id}");
        }
        (new class($this->structure) {
            private API $structure;
            public function __construct(API $structure) {
                $this->structure = $structure;
                spl_autoload_register(function ($class) {
                    $file = str_replace('\\', '/', $class) . '.php';
                    foreach ($this->structure->resources->autoload as $dir) {
                        if (file_exists("$dir/$file")) {
                            require_once "$dir/$file";
                            break;
                        }
                    }
                });
            }
        });
        $return = (function ($ds) {
            /** @var Instance $ds */
            foreach ($ds->api->structure->resources->init as $script) {
                include $script;
            }
            return (require_once $ds->api->structure->entry);
        })($this->ds);
        if ($return instanceof APIResponse) {
            return $return;
        } else {
            trigger_error("$prefix No output");
            return $response;
        }
    }

}