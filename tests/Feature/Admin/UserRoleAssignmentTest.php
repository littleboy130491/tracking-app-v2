<?php

/**
 * File: tests/Feature/Admin/UserRoleAssignmentTest.php
 * Responsibility: Verifies the role hierarchy on the admin user screens.
 * What it does:
 * - Covers the customer default for new users.
 * - Covers the rule that only a super admin may hand out (or take away) the
 *   admin / super admin roles. The roles field only offers assignable roles, and
 *   Filament rejects a submitted value that was not on offer, so a tampered
 *   request fails validation before anything is written. The backend rule in
 *   App\Support\Authorization\AssignableRoles is asserted directly as well.
 * How to use: `php artisan test --filter=UserRoleAssignmentTest`.
 * How to extend: add a test per new role or rule.
 */

namespace Tests\Feature\Admin;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\AssignableRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class UserRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $customer;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->superAdmin = User::query()->where('email', 'superadmin@example.com')->firstOrFail();
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->customer = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->adminRole = Role::query()->where('name', Role::ADMIN)->firstOrFail();
    }

    public function test_a_new_user_defaults_to_the_customer_role(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Person',
                'email' => 'new-person@example.com',
                'password' => 'password',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = User::query()->where('email', 'new-person@example.com')->firstOrFail();

        $this->assertTrue($created->hasRole(Role::CUSTOMER));
        $this->assertFalse($created->hasRole(Role::ADMIN));
    }

    public function test_a_super_admin_can_create_an_admin(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Second Admin',
                'email' => 'second-admin@example.com',
                'password' => 'password',
                'is_active' => true,
                'roles' => [$this->adminRole->getKey()],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(
            User::query()->where('email', 'second-admin@example.com')->firstOrFail()->hasRole(Role::ADMIN),
        );
    }

    public function test_an_admin_cannot_create_an_admin(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(CreateUser::class)->fillForm([
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'password',
            'is_active' => true,
            'roles' => [$this->adminRole->getKey()],
        ]);

        $component->call('create');

        $this->assertRolesWereRejected($component);
        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }

    public function test_an_admin_cannot_promote_an_existing_user_to_admin(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(EditUser::class, ['record' => $this->customer->getKey()])
            ->fillForm(['roles' => [$this->adminRole->getKey()]]);

        $component->call('save');

        $this->assertRolesWereRejected($component);
        $this->assertFalse($this->customer->refresh()->hasRole(Role::ADMIN));
    }

    public function test_an_admin_cannot_change_the_roles_of_a_privileged_account(): void
    {
        // The admin role is not on offer, so it can be neither added nor removed:
        // the account keeps its role instead of being silently downgraded.
        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $this->admin->getKey()])
            ->fillForm(['name' => 'Renamed Admin'])
            ->call('save');

        $this->assertSame('Renamed Admin', $this->admin->refresh()->name);
        $this->assertTrue($this->admin->hasRole(Role::ADMIN));
    }

    public function test_an_admin_can_still_manage_unprivileged_users(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $this->customer->getKey()])
            ->fillForm(['name' => 'Renamed Customer'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Renamed Customer', $this->customer->refresh()->name);
    }

    public function test_privileged_roles_are_only_offered_to_a_super_admin(): void
    {
        $operator = Role::query()->where('name', Role::OPERATOR)->firstOrFail();
        $customer = Role::query()->where('name', Role::CUSTOMER)->firstOrFail();

        $adminOptions = AssignableRoles::optionsFor($this->admin);
        $this->assertArrayNotHasKey($this->adminRole->getKey(), $adminOptions);
        $this->assertArrayHasKey($operator->getKey(), $adminOptions);
        $this->assertArrayHasKey($customer->getKey(), $adminOptions);

        $superAdminOptions = AssignableRoles::optionsFor($this->superAdmin);
        $this->assertArrayHasKey($this->adminRole->getKey(), $superAdminOptions);
    }

    public function test_only_the_super_admin_role_bypasses_every_gate(): void
    {
        $this->assertTrue($this->superAdmin->can('an-ability-that-does-not-exist'));
        $this->assertFalse($this->admin->can('an-ability-that-does-not-exist'));

        // The admin still works, through its explicitly granted permissions.
        $this->assertTrue($this->admin->can('ViewAny:User'));
        $this->assertTrue($this->admin->can('ViewAny:Company'));
    }

    public function test_only_admin_and_super_admin_can_delete_records(): void
    {
        $operator = User::query()->where('email', 'operator@example.com')->firstOrFail();

        // The whole delete lifecycle is admin territory, on every resource.
        foreach (['Delete', 'DeleteAny', 'ForceDelete', 'ForceDeleteAny', 'Restore', 'RestoreAny'] as $ability) {
            $this->assertFalse($operator->can("$ability:Company"), "operator should not hold $ability:Company");
            $this->assertFalse($operator->can("$ability:BillOfLading"), "operator should not hold $ability:BillOfLading");
        }

        $this->assertTrue($this->admin->can('Delete:Company'));
        $this->assertTrue($this->admin->can('ForceDelete:Company'));
        $this->assertTrue($this->superAdmin->can('Delete:Company'));

        // Operators still keep their write access on shipments.
        $this->assertTrue($operator->can('Update:BillOfLading'));
    }

    public function test_operators_cannot_access_companies_and_users(): void
    {
        $operator = User::query()->where('email', 'operator@example.com')->firstOrFail();
        $company = Company::query()->firstOrFail();

        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $ability) {
            $this->assertFalse($operator->can("$ability:Company"), "operator should not hold $ability:Company");
            $this->assertFalse($operator->can("$ability:User"), "operator should not hold $ability:User");
        }

        $this->actingAs($operator)
            ->get(CompanyResource::getUrl('index'))->assertForbidden();
        $this->actingAs($operator)
            ->get(CompanyResource::getUrl('create'))->assertForbidden();
        $this->actingAs($operator)
            ->get(CompanyResource::getUrl('edit', ['record' => $company]))->assertForbidden();
        $this->actingAs($operator)
            ->get(UserResource::getUrl('index'))->assertForbidden();
    }

    public function test_admins_can_toggle_a_companys_active_state(): void
    {
        $company = Company::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($this->admin);

        Livewire::test(EditCompany::class, ['record' => $company->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($company->refresh()->is_active);
    }

    /**
     * Filament validates a relationship select against the options it offered,
     * so the rejection can land on the field or on one of its entries.
     */
    private function assertRolesWereRejected(Testable $component): void
    {
        $keys = array_keys($component->errors()->toArray());

        $this->assertTrue(
            collect($keys)->contains(fn (string $key): bool => str_starts_with($key, 'data.roles')),
            'Expected the roles field to be rejected. Errors: '.implode(', ', $keys),
        );
    }
}
