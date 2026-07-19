<x-layouts.marketing>
    <section class="mx-auto max-w-6xl px-4 py-20 text-center sm:px-6 sm:py-28">
        <span class="inline-flex items-center gap-x-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
            <i class="bx bx-layer text-sm"></i>
            Multi-tenant HR platform
        </span>

        <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
            One system for attendance, leave, payroll, and compliance
        </h1>

        <p class="mx-auto mt-5 max-w-2xl text-lg text-slate-500">
            {{ config('app.name') }} brings your organization's people data, approvals, and statutory obligations into a single, secure, multi-tenant platform — with role-based access built in from day one.
        </p>

        <div class="mt-8 flex items-center justify-center gap-x-4">
            <a href="{{ route('login') }}">
                <x-button icon="bx-log-in">Log in</x-button>
            </a>
            <a href="#features">
                <x-button variant="secondary">See features</x-button>
            </a>
        </div>
    </section>

    <section id="features" class="border-t border-slate-100 bg-slate-50">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Features</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Everything HR needs, in one place</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'bx-sitemap', 'title' => 'Employee Records & Organization', 'description' => 'Model your entities, branches, departments, positions, and grades — with a full employee record for every hire.'],
                    ['icon' => 'bx-time-five', 'title' => 'Attendance & Time Tracking', 'description' => 'Clock in and out, track worked hours by day, and route overtime requests through the right approver.'],
                    ['icon' => 'bx-calendar-check', 'title' => 'Leave Management', 'description' => 'Configurable leave types, running balances, and an approvals inbox scoped to each manager\'s team.'],
                    ['icon' => 'bxs-bank', 'title' => 'Statutory Payroll Compliance', 'description' => 'A pluggable statutory engine so payroll rules and compliance requirements stay accurate wherever your organization operates.'],
                    ['icon' => 'bx-shield-quarter', 'title' => 'Role-Based Access Control', 'description' => 'Nine built-in roles with field-level sensitivity rules, scoped per tenant — every user sees exactly what their role allows.'],
                    ['icon' => 'bx-history', 'title' => 'Audit Trail & Compliance', 'description' => 'Every sensitive action — logins, approvals, role changes, record views — is logged and reviewable by your auditors.'],
                ] as $feature)
                    <x-card>
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                            <i class="bx {{ $feature['icon'] }} text-xl text-emerald-600"></i>
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-500">{{ $feature['description'] }}</p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    <section id="how-it-works" class="border-t border-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-emerald-600">How it works</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Built around how your teams actually work</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ([
                    ['icon' => 'bx-cog', 'title' => 'HR admins & managers', 'description' => 'Set up your organization, manage employee records, and approve leave and overtime requests from a single inbox.'],
                    ['icon' => 'bx-user', 'title' => 'Employees', 'description' => 'Clock in and out, request leave, and view your own profile and pay accounts through self-service.'],
                    ['icon' => 'bx-search-alt', 'title' => 'Auditors & executives', 'description' => 'Get read-only visibility into records and the access log, without any risk of changing operational data.'],
                ] as $step)
                    <div class="text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-900">
                            <i class="bx {{ $step['icon'] }} text-xl text-white"></i>
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-sm text-slate-500">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-slate-100 bg-slate-900">
        <div class="mx-auto max-w-6xl px-4 py-16 text-center sm:px-6">
            <p class="text-2xl font-bold tracking-tight text-white">Ready to bring your HR online?</p>
            <p class="mx-auto mt-2 max-w-xl text-sm text-slate-400">
                New companies are onboarded by a platform administrator. If your organization already has an account, log in below.
            </p>
            <div class="mt-6">
                <a href="{{ route('login') }}">
                    <x-button icon="bx-log-in">Log in</x-button>
                </a>
            </div>
        </div>
    </section>
</x-layouts.marketing>
