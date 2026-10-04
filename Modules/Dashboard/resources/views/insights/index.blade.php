@auth
    <x-app-shell title="Market Insights">
        @include('dashboard::insights._listing')
    </x-app-shell>
@else
    <x-public-shell title="Market Insights" description="Market research and analysis from the SynthEX desk.">
        @include('dashboard::insights._listing')
    </x-public-shell>
@endauth