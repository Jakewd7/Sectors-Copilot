<!-- Comparison table rendered inside the right-side inspector panel.
     Scope: latestPayload (structured_payload of the live/latest research). -->
<div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead>
            <tr class="text-text-muted border-b border-border">
                <th class="py-2.5 font-medium"></th>
                <th class="py-2.5 font-semibold text-right text-text-primary"
                    x-text="latestPayload.get_company_overview?.symbol ?? 'Company'"></th>
                <th class="py-2.5 font-semibold text-right text-text-muted">Subsector</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border/40">
            <tr>
                <td class="py-2.5 text-text-muted">PER</td>
                <td class="py-2.5 text-right"
                    :class="forwardPeVsMedian === 'undervalued' ? 'text-accent font-semibold' : 'text-text-primary font-semibold'"
                    x-text="latestPayload.get_company_overview?.valuation?.forward_pe ?? '-'"></td>
                <td class="py-2.5 text-right text-text-muted"
                    x-text="subsectorMedianPe ?? '-'"></td>
            </tr>
            <tr>
                <td class="py-2.5 text-text-muted">ROE</td>
                <td class="py-2.5 text-right font-semibold text-text-primary"
                    x-text="latestRoe !== null ? (latestRoe * 100).toFixed(1) + '%' : '-'"></td>
                <td class="py-2.5 text-right text-text-muted">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-text-muted">Div yield</td>
                <td class="py-2.5 text-right font-semibold text-text-primary"
                    x-text="latestDivYield !== null ? (latestDivYield * 100).toFixed(1) + '%' : '-'"></td>
                <td class="py-2.5 text-right text-text-muted">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-text-muted">Intrinsic value</td>
                <td class="py-2.5 text-right font-semibold text-text-primary"
                    x-text="latestPayload.get_company_overview?.valuation?.intrinsic_value != null
                        ? 'Rp ' + Number(latestPayload.get_company_overview.valuation.intrinsic_value).toLocaleString('id-ID')
                        : '-'"></td>
                <td class="py-2.5 text-right text-text-muted">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-text-muted">Market cap</td>
                <td class="py-2.5 text-right font-semibold text-text-primary"
                    x-text="latestPayload.get_company_overview?.overview?.market_cap != null
                        ? (latestPayload.get_company_overview.overview.market_cap / 1e12).toFixed(2) + ' T'
                        : '-'"></td>
                <td class="py-2.5 text-right text-text-muted">-</td>
            </tr>
        </tbody>
    </table>
</div>