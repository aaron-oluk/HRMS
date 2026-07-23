@php($activeTab ??= 'pipeline')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Recruitment</h1>
    <p class="mt-1 text-sm text-slate-500">Manage job postings, candidates, and hiring pipeline</p>

    <div class="mt-4 inline-flex items-center gap-x-1 rounded-lg bg-slate-100 p-1">
        <a
            href="{{ route('recruitment.pipeline') }}"
            class="rounded-md px-4 py-1.5 text-sm font-medium transition {{ $activeTab === 'pipeline' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
        >
            Pipeline
        </a>
        <a
            href="{{ route('recruitment.requisitions.index') }}"
            class="rounded-md px-4 py-1.5 text-sm font-medium transition {{ $activeTab === 'job-postings' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}"
        >
            Job Postings
        </a>
    </div>
</div>
