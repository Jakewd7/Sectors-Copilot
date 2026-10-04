<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Riset - {{ $session->title }}</title>

    <style>
        @page {
            margin: 24mm 16mm 26mm 16mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5pt;
            line-height: 1.65;
            color: #1a2620;
            background: #ffffff;
        }

        /* ---------- Header ---------- */
        .doc-header {
            border-bottom: 2px solid #34a06a;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .brand {
            font-size: 13pt;
            font-weight: bold;
            color: #1a2620;
            letter-spacing: -0.2pt;
        }

        .brand-mark {
            color: #34a06a;
        }

        .brand-sub {
            font-size: 7.5pt;
            color: #64756c;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .meta-table td {
            padding: 2px 0;
            font-size: 8pt;
            vertical-align: top;
        }

        .meta-label {
            color: #64756c;
            width: 90px;
        }

        .meta-value {
            color: #1a2620;
            font-weight: bold;
        }

        /* ---------- Pesan ---------- */
        .msg {
            margin-bottom: 16px;
            page-break-inside: auto;
        }

        .msg-head {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            padding: 4px 8px;
            margin-bottom: 7px;
        }

        .msg-user .msg-head {
            background: #eef4f0;
            color: #2b8759;
            border-left: 3px solid #34a06a;
        }

        .msg-assistant .msg-head {
            background: #f2f5f3;
            color: #47564d;
            border-left: 3px solid #9fb3a8;
        }

        .msg-body {
            padding-left: 8px;
        }

        /* ---------- Markdown ---------- */
        .msg-body h1,
        .msg-body h2,
        .msg-body h3,
        .msg-body h4 {
            font-size: 10.5pt;
            color: #1a2620;
            margin: 12px 0 6px 0;
            page-break-after: avoid;
        }

        .msg-body h3 {
            font-size: 9.5pt;
        }

        .msg-body p {
            margin-bottom: 7px;
        }

        .msg-body strong {
            color: #14201a;
        }

        .msg-body em {
            color: #47564d;
        }

        .msg-body ul,
        .msg-body ol {
            margin: 6px 0 8px 18px;
        }

        .msg-body li {
            margin-bottom: 3px;
        }

        .msg-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 9px 0;
            font-size: 8pt;
            page-break-inside: avoid;
        }

        .msg-body th {
            background: #eef4f0;
            color: #1a2620;
            text-align: left;
            padding: 5px 7px;
            border: 0.5px solid #c8d6ce;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }

        .msg-body td {
            padding: 5px 7px;
            border: 0.5px solid #dbe5df;
        }

        .msg-body tr:nth-child(even) td {
            background: #fafcfb;
        }

        .msg-body code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 8pt;
            background: #f2f5f3;
            padding: 1px 3px;
            color: #2b5c42;
        }

        .msg-body pre {
            background: #f2f5f3;
            border-left: 3px solid #9fb3a8;
            padding: 8px 10px;
            margin: 8px 0;
            font-size: 8pt;
        }

        .msg-body pre code {
            background: none;
            padding: 0;
        }

        .msg-body blockquote {
            border-left: 3px solid #dbe5df;
            padding-left: 10px;
            color: #47564d;
            margin: 8px 0;
        }

        .msg-body hr {
            border: none;
            border-top: 0.5px solid #dbe5df;
            margin: 12px 0;
        }

        .msg-body a {
            color: #2b8759;
            text-decoration: none;
        }

        /* ---------- Footer ---------- */
        .doc-footer {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 1px solid #dbe5df;
            font-size: 7.5pt;
            color: #64756c;
            page-break-inside: avoid;
        }

        .doc-footer .footer-title {
            font-weight: bold;
            color: #47564d;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

    <div class="doc-header">
        <div class="brand"><span class="brand-mark">Sectors</span> Copilot</div>
        <div class="brand-sub">Multi-Step AI Research Agent for Indonesian Market Intelligence</div>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Session</td>
                <td class="meta-value">{{ $session->title ?: 'Untitled session' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Exported</td>
                <td class="meta-value">{{ $exportedAt->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Messages</td>
                <td class="meta-value">{{ $messages->count() }}</td>
            </tr>
        </table>
    </div>

    @forelse ($messages as $message)
        @php
            $isUser = $message->role === 'user';
            $body = \Illuminate\Support\Str::markdown($message->content ?? '');
        @endphp

        <div class="msg {{ $isUser ? 'msg-user' : 'msg-assistant' }}">
            <div class="msg-head">{{ $isUser ? 'Research Question' : 'Copilot Analysis' }}</div>
            <div class="msg-body">{!! $body !!}</div>
        </div>
    @empty
        <p>This research session has no messages yet.</p>
    @endforelse

    <div class="doc-footer">
        <div class="footer-title">Disclaimer Regulasi &amp; Kepatuhan Pasar Modal</div>
        <div>
            Analisis dan data di atas dihasilkan secara otomatis oleh SynthEX AI Research Agent untuk
            keperluan edukasi dan referensi riset semata. Informasi ini bukan merupakan rekomendasi, ajakan,
            atau paksaan untuk membeli atau menjual efek tertentu. Keputusan investasi sepenuhnya berada di
            tangan investor dengan mempertimbangkan profil risiko masing-masing.
        </div>
        <div style="margin-top: 6px;">
            Generated automatically &middot; SynthEX &middot; {{ $exportedAt->format('Y') }}
        </div>
    </div>

</body>

</html>