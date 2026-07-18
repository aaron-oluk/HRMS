<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Context;

/**
 * Writes access-event rows (as distinct from field-change rows written by the
 * Auditable trait) to the shared audit_logs table: login attempts, failed
 * authorization, sensitive-field views, and role assignments.
 */
class AccessAudit
{
    public static function loginSucceeded(User $user): void
    {
        static::write(
            tenantId: $user->tenant_id,
            actorId: $user->id,
            auditableType: User::class,
            auditableId: $user->id,
            action: 'login_succeeded',
        );
    }

    public static function loginFailed(?User $user, string $email): void
    {
        static::write(
            tenantId: $user?->tenant_id,
            actorId: $user?->id,
            auditableType: User::class,
            auditableId: $user?->id,
            action: 'login_failed',
            field: 'email',
            newValue: $email,
        );
    }

    public static function loggedOut(User $user): void
    {
        static::write(
            tenantId: $user->tenant_id,
            actorId: $user->id,
            auditableType: User::class,
            auditableId: $user->id,
            action: 'logged_out',
        );
    }

    public static function accessDenied(?User $user, string $message): void
    {
        $resource = request() ? request()->method().' '.request()->path() : null;

        static::write(
            tenantId: $user?->tenant_id,
            actorId: $user?->id,
            auditableType: User::class,
            auditableId: $user?->id,
            action: 'access_denied',
            field: 'resource',
            oldValue: $resource,
            newValue: $message,
        );
    }

    /**
     * @param  list<string>  $fields  the sensitive field tiers actually visible to $viewer
     */
    public static function sensitiveFieldViewed(Employee $employee, User $viewer, array $fields): void
    {
        foreach ($fields as $field) {
            static::write(
                tenantId: $employee->tenant_id,
                actorId: $viewer->id,
                auditableType: Employee::class,
                auditableId: $employee->id,
                action: 'sensitive_field_viewed',
                field: $field,
            );
        }
    }

    public static function roleAssigned(User $actor, User $target, string $role): void
    {
        static::write(
            tenantId: $target->tenant_id,
            actorId: $actor->id,
            auditableType: User::class,
            auditableId: $target->id,
            action: 'role_assigned',
            field: 'role',
            newValue: $role,
        );
    }

    protected static function write(
        ?int $tenantId,
        ?int $actorId,
        string $auditableType,
        ?int $auditableId,
        string $action,
        ?string $field = null,
        ?string $oldValue = null,
        ?string $newValue = null,
    ): void {
        AuditLog::create([
            'tenant_id' => $tenantId,
            'actor_id' => $actorId,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'action' => $action,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'request_id' => Context::get('request_id'),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
