@php
    $currentEmployment = $employee->currentEmployment;
    $allowances = $employee->compensationItems->where('category', 'allowance');
    $benefits = $employee->compensationItems->where('category', 'benefit');
    $baseSalary = (float) ($currentEmployment->basic_salary ?? 0);
    $totalMonthly = $baseSalary + $allowances->sum('amount') + $benefits->sum('amount');
    $currency = $currentEmployment->currency ?? $employee->entity->currency;
    $recentDocuments = $employee->documents->sortByDesc('created_at')->take(4);
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Leave balance</h3>
                <span class="text-xs text-slate-500">{{ now()->year }}</span>
            </div>

            @if (empty($leaveBalances['types']))
                <p class="text-sm text-slate-500">No leave types configured for this entity.</p>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <x-progress-ring :used="$leaveBalances['all']['used']" :total="$leaveBalances['all']['total']" label="All Leaves" unit="Days" />
                    @foreach ($leaveBalances['types'] as $type)
                        <x-progress-ring :used="$type['used']" :total="$type['total']" :label="$type['name']" unit="Days" />
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Performance overview</h3>
                @if (! empty($performanceTrend))
                    @php($latest = end($performanceTrend))
                    <span class="text-sm font-semibold text-emerald-600">{{ rtrim(rtrim(number_format($latest['score'], 1), '0'), '.') }}%</span>
                @endif
            </div>
            <x-trend-chart :data="$performanceTrend" />
        </x-card>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-card>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Hours logged</h3>
                    <span class="text-xs text-slate-500">This week</span>
                </div>
                <p class="mb-3 text-xl font-semibold text-slate-900">
                    {{ rtrim(rtrim(number_format(collect($hoursThisWeek)->sum('hours'), 1), '0'), '.') }}<span class="text-sm font-normal text-slate-400"> hrs</span>
                </p>
                <x-bar-chart :data="collect($hoursThisWeek)->map(fn ($d) => ['label' => $d['label'], 'value' => $d['hours']])->all()" />
            </x-card>

            <x-card>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Documents</h3>
                    <button type="button" @click="tab = 'documents'" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">View all</button>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($recentDocuments as $document)
                        <li class="flex items-center gap-x-3 py-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-emerald-50">
                                <i class="bx bx-file text-emerald-600"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $document->original_filename }}</p>
                                <p class="text-xs text-slate-400">{{ ucfirst(str_replace('_', ' ', $document->type)) }} &middot; {{ $document->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">No documents uploaded.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>

        @can('employees.view-notes')
            <x-card x-data="{ addingNote: false }">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Internal notes</h3>
                    @can('employees.manage-notes')
                        <button type="button" @click="addingNote = ! addingNote" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                            <span x-show="! addingNote">+ Add note</span>
                            <span x-show="addingNote" x-cloak>Cancel</span>
                        </button>
                    @endcan
                </div>

                @can('employees.manage-notes')
                    <form x-show="addingNote" x-cloak method="POST" action="{{ route('employees.notes.store', $employee) }}" class="mb-4 space-y-3 rounded-md border border-slate-100 p-3">
                        @csrf
                        <x-input name="title" placeholder="Note title" required />
                        <textarea name="body" rows="3" required placeholder="Details..." class="block w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition hover:border-slate-400 focus:border-emerald-500 focus:outline-none"></textarea>
                        <x-button type="submit" class="w-full">Save note</x-button>
                    </form>
                @endcan

                @if ($employee->notes->isEmpty())
                    <p class="text-sm text-slate-500">No internal notes yet.</p>
                @else
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($employee->notes as $note)
                            <div class="rounded-md border border-slate-100 p-3">
                                <div class="flex items-start justify-between gap-x-2">
                                    <p class="text-sm font-medium text-slate-900">{{ $note->title }}</p>
                                    @can('employees.manage-notes')
                                        <form method="POST" action="{{ route('employees.notes.destroy', [$employee, $note]) }}" onsubmit="return confirm('Delete this note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-base"></i></button>
                                        </form>
                                    @endcan
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $note->created_at->toFormattedDateString() }}</p>
                                <p class="mt-2 text-sm text-slate-600">{{ $note->body }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        @endcan
    </div>

    <div class="space-y-6">
        <x-card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ now()->format('F Y') }}</h3>
            <x-attendance-calendar :month="now()" :data="$attendanceCalendar" />
        </x-card>

        @can('employees.view-salary')
            <x-card x-data="{ addingItem: false }">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Payroll summary</h3>
                    @can('employees.manage-compensation')
                        <button type="button" @click="addingItem = ! addingItem" class="text-xs font-medium text-emerald-600 hover:text-emerald-500">
                            <span x-show="! addingItem">+ Add item</span>
                            <span x-show="addingItem" x-cloak>Cancel</span>
                        </button>
                    @endcan
                </div>

                @can('employees.manage-compensation')
                    <form x-show="addingItem" x-cloak method="POST" action="{{ route('employees.compensation-items.store', $employee) }}" class="mb-4 grid grid-cols-2 gap-2 rounded-md border border-slate-100 p-3">
                        @csrf
                        <div class="col-span-2">
                            <x-select name="category">
                                <option value="allowance">Allowance</option>
                                <option value="benefit">Benefit</option>
                            </x-select>
                        </div>
                        <x-input name="name" placeholder="Name" required class="col-span-2" />
                        <x-input name="amount" type="number" step="0.01" min="0" placeholder="Amount" required class="col-span-2" />
                        <x-button type="submit" class="col-span-2 w-full">Add</x-button>
                    </form>
                @endcan

                <dl class="text-sm">
                    <p class="text-xs font-semibold uppercase text-slate-400">Description</p>
                    <div class="flex justify-between py-1.5">
                        <dt class="text-slate-500">Base Salary</dt>
                        <dd class="font-medium text-slate-900">{{ number_format($baseSalary) }} {{ $currency }}</dd>
                    </div>

                    @if ($allowances->isNotEmpty())
                        <p class="pt-2 text-xs font-semibold uppercase text-slate-400">Allowances</p>
                        @foreach ($allowances as $item)
                            <div class="flex items-center justify-between py-1.5">
                                <dt class="text-slate-500">{{ $item->name }}</dt>
                                <div class="flex items-center gap-x-2">
                                    <dd class="font-medium text-slate-900">{{ number_format($item->amount) }}</dd>
                                    @can('employees.manage-compensation')
                                        <form method="POST" action="{{ route('employees.compensation-items.destroy', [$employee, $item]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-sm"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if ($benefits->isNotEmpty())
                        <p class="pt-2 text-xs font-semibold uppercase text-slate-400">Benefits</p>
                        @foreach ($benefits as $item)
                            <div class="flex items-center justify-between py-1.5">
                                <dt class="text-slate-500">{{ $item->name }}</dt>
                                <div class="flex items-center gap-x-2">
                                    <dd class="font-medium text-slate-900">{{ number_format($item->amount) }}</dd>
                                    @can('employees.manage-compensation')
                                        <form method="POST" action="{{ route('employees.compensation-items.destroy', [$employee, $item]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500"><i class="bx bx-x text-sm"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="mt-2 flex justify-between border-t border-slate-100 pt-2">
                        <dt class="font-semibold text-slate-900">Total Monthly Value</dt>
                        <dd class="font-semibold text-slate-900">{{ number_format($totalMonthly) }} {{ $currency }}</dd>
                    </div>
                </dl>
            </x-card>
        @endcan

        @include('employees.partials.profile')
        @include('employees.partials.insurance')
    </div>
</div>
