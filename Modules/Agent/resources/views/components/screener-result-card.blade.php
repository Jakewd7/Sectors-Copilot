<!-- Screening results rendered inside the right-side inspector panel.
     Scope: latestPayload (structured_payload of the live/latest research). -->
<div class="space-y-2">
    <template x-for="stock in (latestPayload.screen_stocks || [])" :key="stock.symbol">
        <div class="p-3 bg-surface border border-border rounded-lg flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-sm font-bold text-accent font-mono" x-text="stock.symbol"></span>
                <span class="block text-xs text-text-muted truncate" x-text="stock.company_name"></span>
                <span class="text-[10px] text-text-muted"
                      x-text="'ROE: ' + ((stock.roe_ttm ?? stock.roe ?? 0) * 100).toFixed(1) + '% | PER: ' + (stock.pe_ttm ?? stock.per ?? '-') + 'x'"></span>
            </div>
            <button @click="quickDrillDown(stock.symbol)"
                    class="px-2.5 py-1 text-xs shrink-0 bg-base hover:bg-border text-text-primary rounded-md border border-border transition">
                Drill Down →
            </button>
        </div>
    </template>
</div>