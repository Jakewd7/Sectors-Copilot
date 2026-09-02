<?php

namespace Modules\Agent\Actions;

class InjectComplianceDisclaimerAction
{
    public function execute(string $content): string
    {
        $disclaimer = "\n\n---\n**Disclaimer Regulasi & Kepatuhan Pasar Modal:**\n" .
            "*Analisis dan data di atas dihasilkan secara otomatis oleh Stock AI Research Copilot untuk keperluan edukasi dan referensi riset semata. " .
            "Informasi ini bukan merupakan rekomendasi, ajakan, atau paksaan untuk membeli atau menjual efek tertentu. " .
            "Keputusan investasi sepenuhnya berada di tangan investor dengan mempertimbangkan profil risiko masing-masing.*";

        return rtrim($content) . $disclaimer;
    }
}