<x-layouts.app title="Time Off" header="Time Off">
    <div x-data="{ tab: 'mine', showModal: {{ $errors->any() ? 'true' : 'false' }} }">
        @if ($balances->isNotEmpty())
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($balances as $balance)
                    <x-card class="flex items-center gap-x-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                            <i class="bx bx-calendar-check text-2xl text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">{{ $balance['type']->name }}</p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $balance['available'] }} <span class="text-sm font-normal text-slate-500">days left</span></p>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between">
            <div class="flex gap-x-6 border-b border-slate-200">
                <button type="button" @click="tab = 'mine'" :class="tab === 'mine' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                    <i class="bx bx-list-ul text-base"></i> My requests
                </button>
                @can('leave.approve')
                    <button type="button" @click="tab = 'team'" :class="tab === 'team' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="flex items-center gap-x-1.5 border-b-2 px-1 py-3 text-sm font-medium">
                        <i class="bx bx-check-shield text-base"></i> Team approvals
                        @if ($teamRequests->isNotEmpty())
                            <span class="ml-1 rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $teamRequests->count() }}</span>
                        @endif
                    </button>
                @endcan
            </div>

            @if ($leaveTypes->isNotEmpty())
                <x-button icon="bx-plus" @click="showModal = true">Request time off</x-button>
            @endif
        </div>

        <div x-show="tab === 'mine'">
            @include('leave.partials.my-requests')
        </div>

        @can('leave.approve')
            <div x-show="tab === 'team'" x-cloak>
                @include('leave.partials.team-approvals')
            </div>
        @endcan

        <div x-show="showModal" x-cloak class="dialog-backdrop fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="showModal = false">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.outside="showModal = false">
                <h2 class="text-base font-semibold text-slate-900">Request time off</h2>

                <form method="POST" action="{{ route('leave.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-label for="leave_type_id" value="Type" />
                        <x-select id="leave_type_id" name="leave_type_id" class="mt-1">
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('leave_type_id')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="start_date" value="Start date" />
                            <x-input id="start_date" type="date" name="start_date" required class="mt-1" />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                        </div>
                        <div>
                            <x-label for="end_date" value="End date" />
                            <x-input id="end_date" type="date" name="end_date" required class="mt-1" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-label for="reason" value="Reason (optional)" />
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-x-3 pt-2">
                        <x-button type="button" variant="secondary" @click="showModal = false">Cancel</x-button>
                        <x-button type="submit">Submit request</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
