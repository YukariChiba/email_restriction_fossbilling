<?php

declare(strict_types=1);
/**
 * Copyright 2026 Yukari Chiba
 * SPDX-License-Identifier: Apache-2.0.
 *
 * @copyright Yukari Chiba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

namespace Box\Mod\Emailrestriction;

use Box\Mod\Client\Entity\Client;
use FOSSBilling\InformationException;
use FOSSBilling\InjectionAwareInterface;

class Service implements InjectionAwareInterface
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

    public function getModulePermissions(): array
    {
        return [
            'view' => [
                'type' => 'bool',
                'display_name' => __trans('View email restriction settings'),
                'description' => __trans('Allows the staff member to view the email restriction configuration.'),
            ],
            'manage' => [
                'type' => 'bool',
                'display_name' => __trans('Manage email restriction settings'),
                'description' => __trans('Allows the staff member to change the whitelist and blacklist.'),
            ],
            'manage_settings' => [],
        ];
    }

    public static function onBeforeClientSignUp(\Box_Event $event): void
    {
        $email = $event->getParameters()['email'] ?? null;

        if (is_string($email) && $email !== '') {
            $event->getDi()['mod_service']('Emailrestriction')->assertEmailAllowed($email);
        }
    }

    public static function onBeforeClientProfileUpdate(\Box_Event $event): void
    {
        $di = $event->getDi();
        $params = $event->getParameters();
        $email = $params['email'] ?? null;

        if (!is_string($email) || $email === '') {
            return;
        }

        // Skip unchanged emails so unrelated profile edits are never blocked.
        $id = $params['id'] ?? null;
        if ($id !== null) {
            $client = $di['em']->getRepository(Client::class)->find($id);
            if ($client instanceof Client && strcasecmp((string) $client->getEmail(), $email) === 0) {
                return;
            }
        }

        $di['mod_service']('Emailrestriction')->assertEmailAllowed($email);
    }

    public function assertEmailAllowed(string $email): void
    {
        if (!$this->isAllowed($email)) {
            throw new InformationException('This email address is not allowed. Please use a different email address.', [], 403);
        }
    }

    // Whitelist must match first, then the blacklist must not match. An empty list is disabled.
    public function isAllowed(string $email): bool
    {
        $domain = $this->extractDomain($email);
        if ($domain === null) {
            return true;
        }

        $config = $this->getConfig();

        $whitelist = $this->parseDomainList($config['whitelist'] ?? '');
        if ($whitelist !== [] && !$this->domainMatchesList($domain, $whitelist)) {
            return false;
        }

        $blacklist = $this->parseDomainList($config['blacklist'] ?? '');
        if ($blacklist !== [] && $this->domainMatchesList($domain, $blacklist)) {
            return false;
        }

        return true;
    }

    // Null means the address could not be parsed; callers treat that as allowed.
    public function extractDomain(string $email): ?string
    {
        $email = trim($email);
        $atPos = strrpos($email, '@');
        if ($atPos === false) {
            return null;
        }

        $domain = trim(strtolower(substr($email, $atPos + 1)), " \t\n\r\0\x0B.");

        return $domain === '' ? null : $domain;
    }

    /**
     * @return string[]
     */
    public function parseDomainList(mixed $raw): array
    {
        if (!is_string($raw)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if ($lines === false) {
            return [];
        }

        $domains = [];
        foreach ($lines as $line) {
            $line = strtolower(trim($line));
            if ($line !== '' && !str_starts_with($line, '#')) {
                $domains[$line] = true;
            }
        }

        return array_keys($domains);
    }

    /**
     * @param string[] $patterns
     */
    public function domainMatchesList(string $domain, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_starts_with($pattern, '*.')) {
                $suffix = substr($pattern, 1);
                if ($suffix !== '.' && str_ends_with($domain, $suffix)) {
                    return true;
                }
            } elseif ($domain === $pattern) {
                return true;
            }
        }

        return false;
    }

    public function getConfig(): array
    {
        return $this->di['mod_config']('Emailrestriction');
    }
}
