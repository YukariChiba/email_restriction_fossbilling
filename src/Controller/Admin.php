<?php

declare(strict_types=1);
/**
 * Copyright 2026 Yukari Chiba
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright Yukari Chiba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Emailrestriction\Controller;

class Admin implements \FOSSBilling\InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function register(\Box_App &$app): void
    {
    }

    public function fetchNavigation(): array
    {
        return [
            'subpages' => [
                [
                    'location' => 'extensions',
                    'label' => __trans('Email Restriction'),
                    'index' => 2000,
                    'uri' => $this->di['url']->adminLink('extension/settings/emailrestriction'),
                    'class' => '',
                ],
            ],
        ];
    }
}
