<?php

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectDeadlineReminder;
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

test('PIC must move a project through statuses in order and each change is recorded', function () {
    $pic = createProjectManagementUser();
    $project = createProjectFor($pic, 'planning');

    $this->actingAs($pic)
        ->patch(route('user.projects.update', $project), ['status' => 'done'])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseMissing('project_status_histories', [
        'project_id' => $project->id,
        'to_status' => 'done',
    ]);

    $this->patch(route('user.projects.update', $project), ['status' => 'progress'])
        ->assertSessionHasNoErrors();

    $this->patch(route('user.projects.update', $project), ['status' => 'done'])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('project_status_histories', [
        'project_id' => $project->id,
        'actor_id' => $pic->id,
        'from_status' => 'planning',
        'to_status' => 'progress',
    ]);
    $this->assertDatabaseHas('project_status_histories', [
        'project_id' => $project->id,
        'from_status' => 'progress',
        'to_status' => 'done',
    ]);
});

test('admin status changes are recorded and shown in project details', function () {
    $admin = createProjectManagementUser('admin');
    $project = createProjectFor(createProjectManagementUser(), 'planning');

    $this->actingAs($admin)
        ->put(route('admin.projects.update', $project), [
            'end_user_id' => $project->end_user_id,
            'pic_id' => $project->pic_id,
            'project_name' => $project->project_name,
            'project_value' => $project->project_value,
            'po_number' => $project->po_number,
            'quotation_number' => $project->quotation_number,
            'internal_cost' => $project->internal_cost,
            'status' => 'cancel',
            'deadline_date' => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('project_status_histories', [
        'project_id' => $project->id,
        'actor_id' => $admin->id,
        'from_status' => 'planning',
        'to_status' => 'cancel',
    ]);

    $this->get(route('admin.projects.index'))
        ->assertOk()
        ->assertSee('Riwayat Status');
});

test('deadline reminder command notifies PIC once for each deadline stage', function () {
    $pic = createProjectManagementUser();
    $project = createProjectFor($pic, 'progress', ['deadline_date' => today()->addDays(7)]);

    $this->artisan('projects:send-deadline-reminders')->assertSuccessful();
    $this->artisan('projects:send-deadline-reminders')->assertSuccessful();

    $this->assertDatabaseCount('notifications', 1);
    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $pic->id,
        'type' => ProjectDeadlineReminder::class,
    ]);
    $this->assertSame('upcoming', $pic->notifications()->firstOrFail()->data['stage']);
    $this->assertSame($project->id, $pic->notifications()->firstOrFail()->data['project_id']);
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
