@php
    $clockInAt = $myTimesheet->firstWhere('date', now()->toDateString())?->clock_in_at;
@endphp

<x-layouts.app title="Attendance" header="Attendance">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @if ($canRequestOvertime)
            <div
                x-data="clockWidget({{ $clockedIn ? 'true' : 'false' }}, {{ $clockInAt ? "'".$clockInAt->toIso8601String()."'" : 'null' }})"
                class="lg:col-span-1"
            >
                <x-card class="items-start gap-y-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ now()->format('l, F j, Y') }}</p>
                    <p class="font-mono text-3xl font-bold text-slate-900" x-text="elapsed"></p>

                    <form method="POST" :action="clockedIn ? '{{ route('attendance.clock-out') }}' : '{{ route('attendance.clock-in') }}'" x-ref="clockForm" class="w-full">
                        @csrf
                        <input type="hidden" name="latitude" :value="latitude">
                        <input type="hidden" name="longitude" :value="longitude">
                        <x-button type="button" class="w-full justify-center" :icon="null" @click="submitClock()">
                            <span x-text="clockedIn ? 'Clock out' : 'Clock in'"></span>
                        </x-button>
                    </form>
                </x-card>
            </div>
        @endif

        <x-card class="lg:col-span-2">
            <p class="mb-4 text-sm font-semibold text-slate-900">My timesheet — this week</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Date</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Clock in</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Clock out</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Hours</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($myTimesheet as $day)
                            <tr>
                                <td class="px-4 py-3 text-slate-900">{{ $day->date->format('D, M j') }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $day->clock_in_at?->format('g:i A') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $day->clock_out_at?->format('g:i A') ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ number_format($day->worked_minutes / 60, 1) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'late' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                            'absent' => 'bg-red-50 text-red-700 ring-red-600/20',
                                            'on_leave' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                            'holiday' => 'bg-slate-50 text-slate-600 ring-slate-500/20',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusColors[$day->status] ?? '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $day->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No attendance recorded this week.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    @if ($canRequestOvertime)
        <div class="mt-6" x-data="{ showOvertimeModal: {{ $errors->any() ? 'true' : 'false' }} }">
            <x-card>
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900">My overtime requests</p>
                    <x-button icon="bx-plus" @click="showOvertimeModal = true">Request overtime</x-button>
                </div>
                @include('attendance.partials.my-overtime')
            </x-card>

            <div x-show="showOvertimeModal" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="showOvertimeModal = false">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="showOvertimeModal = false">
                    <h2 class="text-base font-semibold text-slate-900">Request overtime</h2>

                    <form method="POST" action="{{ route('attendance.overtime.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-label for="ot_date" value="Date" />
                            <x-input id="ot_date" type="date" name="date" required class="mt-1" />
                            <x-input-error :messages="$errors->get('date')" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="ot_hours" value="Hours" />
                            <x-input id="ot_hours" type="number" step="0.25" name="hours" required class="mt-1" />
                            <x-input-error :messages="$errors->get('hours')" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="ot_reason" value="Reason (optional)" />
                            <textarea id="ot_reason" name="reason" rows="3" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                        </div>
                        <div class="flex justify-end gap-x-3 pt-2">
                            <x-button type="button" variant="secondary" @click="showOvertimeModal = false">Cancel</x-button>
                            <x-button type="submit">Submit request</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @can('attendance.approve-overtime')
        <x-card class="mt-6">
            <p class="mb-4 text-sm font-semibold text-slate-900">Overtime approvals</p>
            @include('attendance.partials.overtime-approvals')
        </x-card>
    @endcan

    @can('employees.view')
        <x-card class="mt-6">
            <p class="mb-4 text-sm font-semibold text-slate-900">Team attendance today</p>
            @include('attendance.partials.team-today')
        </x-card>
    @endcan

    @push('scripts')
    <script>
        function clockWidget(clockedIn, clockInAt) {
            return {
                clockedIn: clockedIn,
                latitude: '',
                longitude: '',
                elapsed: '00:00:00',
                startedAt: clockInAt ? new Date(clockInAt) : null,

                init() {
                    if (this.clockedIn && this.startedAt) {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    }
                },

                tick() {
                    const diff = Math.max(0, Date.now() - this.startedAt.getTime());
                    const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
                    const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    this.elapsed = `${h}:${m}:${s}`;
                },

                submitClock() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.latitude = pos.coords.latitude;
                                this.longitude = pos.coords.longitude;
                                this.$refs.clockForm.submit();
                            },
                            () => this.$refs.clockForm.submit(),
                            { timeout: 3000 }
                        );
                    } else {
                        this.$refs.clockForm.submit();
                    }
                },
            };
        }
    </script>
    @endpush
</x-layouts.app>
