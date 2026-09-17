
<div class="overflow-x-auto">
    <table class="w-full text-left text-xs divide-y divide-table-line">
        <thead>
            <tr class="text-muted-foreground-1">
                <th class="py-2.5 font-medium"></th>
                <th class="py-2.5 font-semibold text-right text-foreground uppercase tracking-wide"
                    x-text="latestPayload.get_company_overview?.symbol ?? 'Company'"></th>
                <th class="py-2.5 font-semibold text-right text-muted-foreground-1">Subsector</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="py-2.5 text-muted-foreground-1">PER</td>
                <td class="py-2.5 text-right"
                    :class="forwardPeVsMedian === 'undervalued' ? 'text-primary-active font-semibold' : 'text-foreground font-semibold'"
                    x-text="latestPayload.get_company_overview?.valuation?.forward_pe ?? '-'"></td>
                <td class="py-2.5 text-right text-muted-foreground-1"
                    x-text="subsectorMedianPe ?? '-'"></td>
            </tr>
            <tr>
                <td class="py-2.5 text-muted-foreground-1">ROE</td>
                <td class="py-2.5 text-right font-semibold text-foreground"
                    x-text="latestRoe !== null ? (latestRoe * 100).toFixed(1) + '%' : '-'"></td>
                <td class="py-2.5 text-right text-muted-foreground-1">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-muted-foreground-1">Div yield</td>
                <td class="py-2.5 text-right font-semibold text-foreground"
                    x-text="latestDivYield !== null ? (latestDivYield * 100).toFixed(1) + '%' : '-'"></td>
                <td class="py-2.5 text-right text-muted-foreground-1">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-muted-foreground-1">Intrinsic value</td>
                <td class="py-2.5 text-right font-semibold text-foreground"
                    x-text="latestPayload.get_company_overview?.valuation?.intrinsic_value != null
                        ? 'Rp ' + Number(latestPayload.get_company_overview.valuation.intrinsic_value).toLocaleString('id-ID')
                        : '-'"></td>
                <td class="py-2.5 text-right text-muted-foreground-1">-</td>
            </tr>
            <tr>
                <td class="py-2.5 text-muted-foreground-1">Market cap</td>
                <td class="py-2.5 text-right font-semibold text-foreground"
                    x-text="latestPayload.get_company_overview?.overview?.market_cap != null
                        ? (latestPayload.get_company_overview.overview.market_cap / 1e12).toFixed(2) + ' T'
                        : '-'"></td>
                <td class="py-2.5 text-right text-muted-foreground-1">-</td>
            </tr>
        </tbody>
    </table>
</div>