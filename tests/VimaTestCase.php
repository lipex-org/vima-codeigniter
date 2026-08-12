<?php

namespace Vima\CodeIgniter\Tests;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use Vima\CodeIgniter\Support\VimaRegistrar;
use Vima\Core\Cache\Contracts\CacheInterface;
use function Vima\Core\resolve;

abstract class VimaTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'Vima\CodeIgniter';

    protected function setUp(): void
    {
        Factories::reset();
        parent::setUp();
        VimaRegistrar::init(true);

        // Clear cache and context
        if (function_exists('vima')) {
            resolve(CacheInterface::class)->clear();
        }
        if (class_exists(Services::class)) {
            try {
                Services::vima_context()->set(null);
            } catch (\Throwable $e) {
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public static function setupBeforeClass(): void
    {
        helper('vima');
        vima();
    }
}
