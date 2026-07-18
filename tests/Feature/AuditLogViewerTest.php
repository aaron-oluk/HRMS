<?php

test('an auditor can view the audit log', function () {
    [, $auditor] = tenantWithRole('Auditor');

    $this->actingAs($auditor)->get(route('audit-logs.index'))->assertOk();
});

test('an employee without audit.view permission cannot view the audit log', function () {
    [, $employee] = tenantWithRole('Employee');

    $this->actingAs($employee)->get(route('audit-logs.index'))->assertForbidden();
});
