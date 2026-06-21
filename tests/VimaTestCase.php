<?php

namespace Vima\CodeIgniter\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Vima\CodeIgniter\Database\Migrations\CreateVimaTables;

abstract class VimaTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace = 'Vima\CodeIgniter';

    protected function setUp(): void
    {
        \CodeIgniter\Config\Factories::reset();
        parent::setUp();
        \Vima\CodeIgniter\Support\VimaRegistrar::init(true);

        // Clear cache and context
        if (function_exists('vima')) {
            \Vima\Core\resolve(\Vima\Core\Cache\Contracts\CacheInterface::class)->clear();
        }
        if (class_exists(\Config\Services::class)) {
            try {
                \Config\Services::vima_context()->set(null);
            } catch (\Throwable $e) {}
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
