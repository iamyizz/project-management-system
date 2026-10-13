<?php

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createProjectManagementUser(string $role = 'user', bool $isActive = true): User
{
    return User::create([
        'nama' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'role' => $role,
        'is_active' => $isActive,
        'email_verified_at' => now(),
    ]);
}

function createProjectFor(User $pic, string $status = 'planning', array $attributes = []): Project
{
    $endUser = EndUser::create(['nama' => fake()->company()]);

    return Project::create(array_merge([
        'end_user_id' => $endUser->id,
        'pic_id' => $pic->id,
        'project_name' => fake()->sentence(3),
        'project_value' => 100000,
        'internal_cost' => 60000,
        'margin' => 40000,
        'percentage' => 40,
        'status' => $status,
    ], $attributes));
}

test('user can update their project to a database-supported status only', function () {
    $pic = createProjectManagementUser();
    $project = createProjectFor($pic, 'progress');

    $this->actingAs($pic)
        ->patch(route('user.projects.update', $project), ['status' => 'on_hold'])
        ->assertSessionHasErrors('status');

    $this->assertSame('progress', $project->fresh()->status);

    $this->patch(route('user.projects.update', $project), ['status' => 'done'])
        ->assertSessionHasNoErrors();

    $this->patch(route('user.projects.update', $project), ['status' => 'progress'])
        ->assertForbidden();
});

test('project PIC cannot update a project assigned to another user', function () {
    $pic = createProjectManagementUser();
    $otherPic = createProjectManagementUser();
    $project = createProjectFor($otherPic);

    $this->actingAs($pic)
        ->patch(route('user.projects.update', $project), ['status' => 'progress'])
        ->assertForbidden();
});

test('project list filters search across assigned project and end user', function () {
    $pic = createProjectManagementUser();
    $matching = createProjectFor($pic, 'planning', ['project_name' => 'Portal Internal Acme']);
    createProjectFor($pic, 'planning', ['project_name' => 'Website Public Beta']);

    $this->actingAs($pic)
        ->get(route('user.projects.index', ['q' => 'Acme']))
        ->assertOk()
        ->assertSee('Portal Internal Acme')
        ->assertDontSee('Website Public Beta');
});

test('end user with projects cannot be deleted', function () {
    $admin = createProjectManagementUser('admin');
    $project = createProjectFor(createProjectManagementUser());

    $this->actingAs($admin)
        ->delete(route('admin.end-users.destroy', $project->end_user_id))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('projects', ['id' => $project->id]);
});

test('end user email input must be a valid email address', function () {
    $admin = createProjectManagementUser('admin');

    $this->actingAs($admin)
        ->post(route('admin.end-users.store'), [
            'nama' => 'Customer Validasi',
            'email' => 'bukan-email',
        ])
        ->assertSessionHasErrors('email');
});

test('admin report applies project status filter and summarizes finance', function () {
    $admin = createProjectManagementUser('admin');
    $pic = createProjectManagementUser();
    createProjectFor($pic, 'progress');
    createProjectFor($pic, 'done', ['project_value' => 250000, 'internal_cost' => 100000, 'margin' => 150000]);

    $this->actingAs($admin)
        ->get(route('admin.reports.index', ['status' => 'progress']))
        ->assertOk()
        ->assertSee('Rp 100.000')
        ->assertDontSee('Rp 250.000');
});

test('inactive account is denied access to the application', function () {
    $user = createProjectManagementUser('user', false);

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertForbidden();

    $this->assertGuest();
});

test('last active administrator cannot delete their own account', function () {
    $admin = createProjectManagementUser('admin');

    $this->actingAs($admin)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertSessionHasErrorsIn('userDeletion', 'password');

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin', 'is_active' => true]);
});
