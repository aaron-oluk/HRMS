<x-layouts.app title="Security" header="Security">
    <div
        x-data="twoFactorAuth({{ $twoFactorEnabled ? 'true' : 'false' }})"
        class="max-w-xl"
    >
        <x-card>
            <h2 class="text-base font-semibold text-slate-900">Two-factor authentication</h2>
            <p class="mt-1 text-sm text-slate-500">
                Add an extra layer of security to your account using a TOTP-compatible authenticator app.
            </p>

            <div x-show="error" x-cloak class="mt-4">
                <x-alert type="error" x-text="error"></x-alert>
            </div>

            <template x-if="!enabled && !setupInProgress">
                <div class="mt-6">
                    <x-button type="button" icon="bx-shield-plus" @click="enable">Enable two-factor authentication</x-button>
                </div>
            </template>

            <template x-if="setupInProgress">
                <div class="mt-6 space-y-4">
                    <div x-html="qrCodeSvg" class="w-48"></div>
                    <p class="text-sm text-slate-500">Scan the QR code with your authenticator app, then enter the 6-digit code below.</p>
                    <div class="max-w-xs">
                        <x-label for="confirm_code" value="Authentication code" />
                        <x-input id="confirm_code" type="text" inputmode="numeric" x-model="code" class="mt-1" />
                    </div>
                    <x-button type="button" @click="confirm">Confirm</x-button>
                </div>
            </template>

            <template x-if="enabled && !setupInProgress">
                <div class="mt-6 space-y-4">
                    <x-alert type="success">Two-factor authentication is enabled.</x-alert>

                    <div>
                        <button type="button" @click="toggleRecoveryCodes" class="text-sm text-emerald-600 hover:text-emerald-500">
                            <span x-show="!showRecoveryCodes">Show recovery codes</span>
                            <span x-show="showRecoveryCodes" x-cloak>Hide recovery codes</span>
                        </button>

                        <ul x-show="showRecoveryCodes" x-cloak class="mt-3 grid grid-cols-2 gap-2 rounded-md bg-slate-50 p-4 font-mono text-xs text-slate-700">
                            <template x-for="recoveryCode in recoveryCodes" :key="recoveryCode">
                                <li x-text="recoveryCode"></li>
                            </template>
                        </ul>
                    </div>

                    <x-button type="button" variant="danger" @click="disable">Disable two-factor authentication</x-button>
                </div>
            </template>
        </x-card>
    </div>

    @push('scripts')
    <script>
        function twoFactorAuth(initiallyEnabled) {
            return {
                enabled: initiallyEnabled,
                setupInProgress: false,
                qrCodeSvg: '',
                code: '',
                error: '',
                showRecoveryCodes: false,
                recoveryCodes: [],

                handleError(e) {
                    if (e.response?.status === 423) {
                        window.location = '{{ route('password.confirm') }}';
                        return;
                    }
                    this.error = e.response?.data?.message ?? 'Something went wrong.';
                },

                async enable() {
                    this.error = '';
                    try {
                        await axios.post('/user/two-factor-authentication');
                        const qr = await axios.get('/user/two-factor-qr-code');
                        this.qrCodeSvg = qr.data.svg;
                        this.setupInProgress = true;
                    } catch (e) {
                        this.handleError(e);
                    }
                },

                async confirm() {
                    this.error = '';
                    try {
                        await axios.post('/user/confirmed-two-factor-authentication', { code: this.code });
                        this.setupInProgress = false;
                        this.enabled = true;
                        this.code = '';
                        await this.fetchRecoveryCodes();
                    } catch (e) {
                        this.handleError(e);
                    }
                },

                async disable() {
                    this.error = '';
                    try {
                        await axios.delete('/user/two-factor-authentication');
                        this.enabled = false;
                        this.showRecoveryCodes = false;
                    } catch (e) {
                        this.handleError(e);
                    }
                },

                async fetchRecoveryCodes() {
                    try {
                        const res = await axios.get('/user/two-factor-recovery-codes');
                        this.recoveryCodes = res.data;
                    } catch (e) {
                        this.handleError(e);
                    }
                },

                async toggleRecoveryCodes() {
                    if (!this.showRecoveryCodes && this.recoveryCodes.length === 0) {
                        await this.fetchRecoveryCodes();
                    }
                    this.showRecoveryCodes = !this.showRecoveryCodes;
                },
            };
        }
    </script>
    @endpush
</x-layouts.app>
