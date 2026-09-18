<?php

declare(strict_types=1);
/**
 * Copyright 2026 Yukari Chiba
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright Yukari Chiba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Emailrestriction\Api;

class Admin extends \FOSSBilling\Api\AbstractApi
{
    public function get_config($data): array
    {
        $this->checkPermissions('emailrestriction', 'view');

        $config = $this->getDi()['mod_config']('Emailrestriction');

        return [
            'whitelist' => $config['whitelist'] ?? '',
            'blacklist' => $config['blacklist'] ?? '',
        ];
    }

    public function save_config($data): bool
    {
        $this->checkPermissions('emailrestriction', 'manage');

        $config = [
            'ext' => 'mod_emailrestriction',
            'whitelist' => (string) ($data['whitelist'] ?? ''),
            'blacklist' => (string) ($data['blacklist'] ?? ''),
        ];

        return $this->getDi()['mod_service']('extension')->setConfig($config);
    }
}
