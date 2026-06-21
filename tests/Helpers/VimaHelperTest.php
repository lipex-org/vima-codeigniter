<?php

namespace Vima\CodeIgniter\Tests\Helpers;

use Vima\CodeIgniter\Tests\VimaTestCase;
use Config\Services;
use Vima\CodeIgniter\Tests\Fixtures\User;

class VimaHelperTest extends VimaTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('vima');
    }

    public function testVimaContextPersistence()
    {
        $resource = (object) ['id' => 1];
        vima_context($resource);

        $this->assertSame($resource, vima_context());
    }

    public function testCanThrowsExceptionWhenNoUserResolved()
    {
        // Ensure no auth service is returning a user
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Vima could not resolve the current user. Please ensure a user is logged in or define \'currentUser\' in your Vima configuration.');

        can('permission');
    }

    public function testCanUsesConfigUserResolver()
    {
        $mockUser = new \Vima\CodeIgniter\Tests\Fixtures\User(99);

        $config = config('Vima');
        $config->user['current'] = function () use ($mockUser) {
            return $mockUser;
        };

        // This should now correctly call vima()->can() with a valid User object.
        // It will return false because the user has no permissions, but it won't throw 
        // a "must be an instance of object" or role resolution error if handled correctly.

        $this->assertFalse(can('some.perm'));
    }
}
