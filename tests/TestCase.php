<?php

/**
 * File: tests/TestCase.php
 * Responsibility: Base test case for the application.
 * What it does:
 * - Pins the environment variables the test suite depends on before the
 *   application boots, so a shell that exports .env values (or a missing
 *   variables_order) cannot shadow phpunit.xml and make tests run as "local"
 *   against the development database.
 * How to use: extend this class from every feature/unit test.
 * How to extend: add a variable here when a test must not read .env.
 */

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Environment variables that must always win over .env during tests.
     *
     * @var array<string, string>
     */
    protected array $forcedEnvironment = [
        'APP_ENV' => 'testing',
        'APP_DEBUG' => 'true',
        'DB_CONNECTION' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'DB_URL' => '',
        'SESSION_DRIVER' => 'array',
        'CACHE_STORE' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'MAIL_MAILER' => 'array',
        'BROADCAST_CONNECTION' => 'null',
        'BCRYPT_ROUNDS' => '4',
    ];

    public function createApplication()
    {
        foreach ($this->forcedEnvironment as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        return parent::createApplication();
    }
}
