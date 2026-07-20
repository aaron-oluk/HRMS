@php($statusColor = match ($document->status) {
    'sent' => 'warning',
    'signed' => 'success',
    'declined' => 'danger',
    default => 'neutral',
})

<x-layouts.app title="Document" :header="$document->title">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-x-3">
            <x-badge :color="$statusColor">{{ ucfirst($document->status) }}</x-badge>
            <span class="text-sm text-slate-500">Signer: {{ $document->signer->name }} &middot; Sent by {{ $document->uploader->name }}</span>
        </div>
        <a href="{{ route('documents.download', $document) }}" target="_blank">
            <x-button variant="secondary" icon="bx-download">View / Download</x-button>
        </a>
    </div>

    @if ($canSign)
        @if (! $hasSignature)
            <x-alert type="info">
                Upload your signature on <a href="{{ route('profile.edit') }}" class="font-medium underline">your profile</a> before signing.
            </x-alert>
        @else
            <x-card>
                <p class="mb-4 text-sm text-slate-600">Click on the document to drop your signature, then drag it into place.</p>

                <div
                    x-data="signingPad({
                        pageCount: {{ $document->page_count }},
                        pageUrlBase: @js(url('documents/'.$document->id.'/page')),
                        signatureUrl: @js(route('profile.signature.show')),
                        signUrl: @js(route('documents.sign', $document)),
                        csrfToken: @js(csrf_token()),
                    })"
                    x-init="init()"
                >
                    <template x-if="pageCount > 1">
                        <div class="mb-3">
                            <select x-model.number="currentPage" @change="changePage()" class="rounded-sm border border-slate-300 px-3 py-1.5 text-sm">
                                <template x-for="p in pageCount" :key="p">
                                    <option :value="p - 1" x-text="'Page ' + p"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div class="relative inline-block border border-slate-200" x-ref="container" @click="placeAt($event)">
                        <img :src="pageImageUrl" class="block max-w-full select-none" draggable="false" alt="Document page">
                        <img
                            x-show="placed"
                            :src="signatureUrl"
                            @mousedown.stop="startDrag($event)"
                            :style="signatureStyle"
                            class="absolute cursor-move select-none border border-dashed border-emerald-500 object-contain"
                            draggable="false"
                            alt="Your signature"
                        >
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-button type="button" @click="submit()" :disabled="false" x-bind:class="!placed && 'opacity-50 pointer-events-none'">
                            Confirm &amp; sign
                        </x-button>
                    </div>
                </div>
            </x-card>
        @endif
    @endif
</x-layouts.app>

@push('scripts')
<script>
    function signingPad(config) {
        return {
            pageCount: config.pageCount,
            currentPage: 0,
            pageImageUrl: '',
            signatureUrl: config.signatureUrl,
            placed: false,
            left: 80,
            top: 80,
            width: 160,
            height: 80,
            dragging: false,
            dragOffsetX: 0,
            dragOffsetY: 0,

            init() {
                this.pageImageUrl = `${config.pageUrlBase}/${this.currentPage}`;
            },

            changePage() {
                this.placed = false;
                this.pageImageUrl = `${config.pageUrlBase}/${this.currentPage}`;
            },

            get signatureStyle() {
                return `left:${this.left}px; top:${this.top}px; width:${this.width}px; height:${this.height}px;`;
            },

            placeAt(event) {
                if (this.placed) {
                    return;
                }
                const rect = this.$refs.container.getBoundingClientRect();
                this.left = Math.min(Math.max(0, event.clientX - rect.left - this.width / 2), Math.max(0, rect.width - this.width));
                this.top = Math.min(Math.max(0, event.clientY - rect.top - this.height / 2), Math.max(0, rect.height - this.height));
                this.placed = true;
            },

            startDrag(event) {
                this.dragging = true;
                const rect = this.$refs.container.getBoundingClientRect();
                this.dragOffsetX = event.clientX - rect.left - this.left;
                this.dragOffsetY = event.clientY - rect.top - this.top;

                const move = (moveEvent) => {
                    if (!this.dragging) {
                        return;
                    }
                    const r = this.$refs.container.getBoundingClientRect();
                    this.left = Math.min(Math.max(0, moveEvent.clientX - r.left - this.dragOffsetX), Math.max(0, r.width - this.width));
                    this.top = Math.min(Math.max(0, moveEvent.clientY - r.top - this.dragOffsetY), Math.max(0, r.height - this.height));
                };
                const up = () => {
                    this.dragging = false;
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);
                };
                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            },

            submit() {
                if (!this.placed) {
                    return;
                }
                const rect = this.$refs.container.getBoundingClientRect();
                const payload = {
                    page_number: this.currentPage,
                    x: this.left / rect.width,
                    y: this.top / rect.height,
                    width: this.width / rect.width,
                    height: this.height / rect.height,
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = config.signUrl;
                for (const [key, value] of Object.entries({ _token: config.csrfToken, ...payload })) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            },
        };
    }
</script>
@endpush
