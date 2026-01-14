<div wire:init="loadMethods" class="flw-payment-methods">
    @if ($loading)
        <div class="flw-loading">
            <svg class="flw-spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="32" stroke-dashoffset="12"></circle></svg>
            <span>Loading payment methods...</span>
        </div>
    @elseif ($error)
        <div class="flw-alert flw-alert-error">{{ $error }}</div>
    @else
        <div class="flw-methods-list">
            @forelse ($methods as $method)
                <button
                    type="button"
                    wire:click="selectMethod('{{ $method['id'] }}')"
                    class="flw-method-card {{ $selectedMethodId === $method['id'] ? 'flw-method-selected' : '' }} {{ $this->isExpired($method) ? 'flw-method-expired' : '' }}"
                >
                    <div class="flw-method-icon">
                        @if (!$this->usesInlineIcon($method))
                            {{-- Card-based payment methods use external images --}}
                            <img src="https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/{{ $this->getCardIcon($method) }}.svg" alt="{{ $method['type'] ?? 'Card' }}">
                        @elseif (($method['type'] ?? '') === 'mobile_money')
                            {{-- Mobile Money icon --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="2" width="14" height="20" rx="2" />
                                <line x1="12" y1="18" x2="12" y2="18.01" stroke-linecap="round" />
                            </svg>
                        @elseif (($method['type'] ?? '') === 'bank_account')
                            {{-- Bank Account icon --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3" />
                            </svg>
                        @elseif (($method['type'] ?? '') === 'ussd')
                            {{-- USSD icon --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h.01M10 16h.01M14 16h.01M18 16h.01" stroke-linecap="round" />
                            </svg>
                        @elseif (($method['type'] ?? '') === 'opay')
                            {{-- OPay icon --}}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <circle cx="12" cy="12" r="3" />
                                <path d="M6 12h.01M18 12h.01" stroke-linecap="round" />
                            </svg>
                        @elseif (($method['type'] ?? '') === 'applepay')
                            {{-- Apple Pay icon --}}
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.53 4.08zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                            </svg>
                        @elseif (($method['type'] ?? '') === 'googlepay')
                            {{-- Google Pay icon --}}
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flw-method-info">
                        <span class="flw-method-name">{{ $this->getMethodDisplay($method) }}</span>
                        <span class="flw-method-expiry">
                            {{ $this->getMethodSubtitle($method) }}
                            @if ($this->isExpired($method)) <span class="flw-expired-badge">Expired</span> @endif
                        </span>
                    </div>
                    @if ($selectedMethodId === $method['id'])
                        <svg class="flw-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @endif
                </button>
            @empty
                <p class="flw-no-methods">No saved payment methods</p>
            @endforelse
        </div>

        <button type="button" wire:click="toggleAddNew" class="flw-add-new-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Payment Method
        </button>
    @endif
</div>

<style>
.flw-payment-methods { max-width: 400px; }
.flw-loading { display: flex; align-items: center; gap: 0.5rem; padding: 1rem; color: #6b7280; }
.flw-spinner { width: 1.25rem; height: 1.25rem; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); }}
.flw-methods-list { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
.flw-method-card { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; background: white; cursor: pointer; text-align: left; width: 100%; }
.flw-method-card:hover { border-color: #d1d5db; }
.flw-method-selected { border-color: #f5a623; background: #fffbeb; }
.flw-method-expired { opacity: 0.6; }
.flw-method-icon { width: 2.5rem; height: 1.5rem; color: #6b7280; }
.flw-method-icon img { width: 100%; height: auto; }
.flw-method-icon svg { width: 100%; height: 100%; }
.flw-method-info { flex: 1; }
.flw-method-name { display: block; font-weight: 600; color: #111827; }
.flw-method-expiry { display: block; font-size: 0.75rem; color: #6b7280; }
.flw-expired-badge { background: #fef2f2; color: #dc2626; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.625rem; }
.flw-check-icon { width: 1.5rem; height: 1.5rem; color: #f5a623; }
.flw-add-new-btn { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.75rem; border: 2px dashed #d1d5db; border-radius: 0.5rem; background: transparent; color: #6b7280; cursor: pointer; font-weight: 500; }
.flw-add-new-btn:hover { border-color: #9ca3af; color: #374151; }
.flw-add-new-btn svg { width: 1.25rem; height: 1.25rem; }
.flw-no-methods { text-align: center; color: #6b7280; padding: 1rem; }
.flw-alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; }
</style>

