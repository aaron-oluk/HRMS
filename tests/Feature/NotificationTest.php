<?php

use App\Actions\Leave\SubmitLeaveRequest;
use App\Models\Entity;
use App\Models\LeaveType;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Notification;

test('approving a leave request notifies the employee', function () {
    Notification::fake();

    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = app(SubmitLeaveRequest::class)->handle($employee, [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->next('Monday')->toDateString(),
        'end_date' => now()->next('Monday')->addDays(2)->toDateString(),
        'reason' => 'Test',
    ]);

    $this->actingAs($admin)->post(route('leave.approve', $request));

    Notification::assertSentTo($employeeUser, GenericNotification::class);
});

test('a user can view and mark a notification as read', function () {
    [, $user] = tenantWithRole('HR Admin');

    $user->notify(new GenericNotification(
        title: 'Test notification',
        message: 'Something happened.',
        url: route('dashboard'),
    ));

    $notification = $user->notifications()->firstOrFail();
    expect($notification->read_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('notifications.read', $notification->id))
        ->assertRedirect(route('dashboard'));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('a user cannot mark another user\'s notification as read', function () {
    [, $userA] = tenantWithRole('HR Admin');
    [, $userB] = tenantWithRole('HR Admin');

    $userA->notify(new GenericNotification(title: 'Private', message: 'Not yours.'));
    $notification = $userA->notifications()->firstOrFail();

    $this->actingAs($userB)->get(route('notifications.read', $notification->id))->assertNotFound();
});
