<div
    x-data="{ polling: @entangle('polling') }"
    x-init="if (polling) startPolling()"
    class="flw-payment-status"
>
    <div class="flw-status-container">
        <div class="flw-status-icon {{ $this->getStatusColor() }}">
            @if ($this->getStatusIcon() === 'check-circle')
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @elseif ($this->getStatusIcon() === 'x-circle')
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            @else
                <svg class="flw-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            @endif
        </div>

        <h3 class="flw-status-title">{{ $statusMessage }}</h3>

        @if ($showDetails && $amount)
            <div class="flw-payment-details">
                <div class="flw-detail-row">
                    <span class="flw-detail-label">{{ __('flutterwave::messages.amount') }}</span>
                    <span class="flw-detail-value">{{ $currency }} {{ number_format($amount, 2) }}</span>
                </div>
                @if ($reference)
                    <div class="flw-detail-row">
                        <span class="flw-detail-label">{{ __('flutterwave::messages.reference') }}</span>
                        <span class="flw-detail-value">{{ $reference }}</span>
                    </div>
                @endif
            </div>
        @endif

        @if ($polling)
            <div class="flw-polling-indicator">
                <div class="flw-polling-dots"><span></span><span></span><span></span></div>
                <p>{{ __('flutterwave::messages.checking_status') }}</p>
            </div>
        @endif

        <div class="flw-status-actions">
            @if ($status === 'succeeded')
                <button type="button" class="flw-btn flw-btn-success flw-btn-full">{{ __('flutterwave::messages.continue') }}</button>
            @elseif (in_array($status, ['failed', 'cancelled', 'timeout']))
                <button type="button" class="flw-btn flw-btn-primary flw-btn-full">{{ __('flutterwave::messages.try_again') }}</button>
            @endif
        </div>
    </div>
</div>

<script>
function startPolling() {
    if (window.flwPollingInterval) return;
    window.flwPollingInterval = setInterval(() => @this.call('poll'), {{ $pollInterval }});
}
</script>

<style>
.flw-payment-status { max-width: 400px; margin: 0 auto; padding: 2rem; text-align: center; }
.flw-status-container { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.flw-status-icon { width: 5rem; height: 5rem; margin: 0 auto 1.5rem; }
.flw-status-icon svg { width: 100%; height: 100%; }
.text-green-500 { color: #10b981; }
.text-red-500 { color: #ef4444; }
.text-yellow-500 { color: #f59e0b; }
.flw-status-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
.flw-payment-details { background: #f9fafb; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; }
.flw-detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; }
.flw-detail-label { color: #6b7280; }
.flw-detail-value { font-weight: 600; }
.flw-polling-dots { display: flex; justify-content: center; gap: 0.25rem; }
.flw-polling-dots span { width: 0.5rem; height: 0.5rem; background: #f5a623; border-radius: 50%; animation: flw-bounce 1.4s infinite; }
@keyframes flw-bounce { 0%,80%,100% { transform: scale(0); } 40% { transform: scale(1); } }
.flw-btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; border: none; cursor: pointer; }
.flw-btn-primary { background: linear-gradient(135deg, #f5a623, #f77f00); color: white; }
.flw-btn-success { background: #10b981; color: white; }
.flw-btn-full { width: 100%; }
.flw-spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
