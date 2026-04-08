<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_access_user_role_management(): void
    {
        $owner = User::where('email', 'owner@hfs.local')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('admin.users-roles'))
            ->assertOk()
            ->assertSee('User & Role Management');
    }

    public function test_site_admin_is_forbidden_from_user_role_management(): void
    {
        $siteAdmin = User::where('email', 'siteadmin@hfs.local')->firstOrFail();

        $this->actingAs($siteAdmin)
            ->get(route('admin.users-roles'))
            ->assertForbidden();
    }
}
