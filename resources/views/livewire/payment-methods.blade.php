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
                        <img src="https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/{{ $this->getCardIcon($method) }}.svg" alt="{{ $method['card']['brand'] ?? 'Card' }}">
                    </div>
                    <div class="flw-method-info">
                        <span class="flw-method-name">{{ $this->getCardDisplay($method) }}</span>
                        <span class="flw-method-expiry">
                            Expires {{ $method['card']['exp_month'] ?? '--' }}/{{ $method['card']['exp_year'] ?? '--' }}
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
            Add New Card
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
.flw-method-icon { width: 2.5rem; height: 1.5rem; }
.flw-method-icon img { width: 100%; height: auto; }
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
