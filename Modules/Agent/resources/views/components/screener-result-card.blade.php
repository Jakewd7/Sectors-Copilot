<!-- Screening results rendered inside the right-side inspector panel.
     Preline card + outline-button styling via preline-bridge tokens.
     Scope: latestPayload (structured_payload of the live/latest research). -->
<div class="space-y-2">
    <template x-for="stock in (latestPayload.screen_stocks || [])" :key="stock.symbol">
        <div class="p-3 bg-card border border-card-line rounded-lg flex items-center justify-between gap-3 hover:bg-layer-hover/60 transition">
            <div class="min-w-0">
                <span class="text-sm font-bold text-primary font-mono" x-text="stock.symbol"></span>
                <span class="block text-xs text-muted-foreground-1 truncate" x-text="stock.company_name"></span>
                <span class="text-[10px] text-muted-foreground-1"
                      x-text="'ROE: ' + ((stock.roe_ttm ?? stock.roe ?? 0) * 100).toFixed(1) + '% | PER: ' + (stock.pe_ttm ?? stock.per ?? '-') + 'x'"></span>
            </div>
            <button @click="quickDrillDown(stock.symbol)"
                    class="px-2.5 py-1 text-xs font-medium shrink-0 bg-layer hover:bg-layer-hover text-foreground rounded-lg border border-layer-line transition">
                Drill Down →
            </button>
        </div>
    </template>
</div>