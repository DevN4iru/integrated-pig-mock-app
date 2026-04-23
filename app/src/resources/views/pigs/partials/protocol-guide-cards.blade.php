@php
    $isPigletProgram = str_contains((string) $protocolTemplateCode, 'piglet');
    $isSowProgram = str_contains((string) $protocolTemplateCode, 'sow');

    $medicationReferenceTags = $protocolMedicationProgramItems
        ->map(function ($item) {
            $parts = array_filter([
                $item['action'] ?? null,
                !empty($item['product_note']) ? $item['product_note'] : null,
            ]);

            return trim(implode(' — ', $parts));
        })
        ->filter()
        ->unique()
        ->values();

    $vaccinationReferenceTags = $protocolVaccinationProgramItems
        ->map(function ($item) {
            $parts = array_filter([
                $item['action'] ?? null,
                !empty($item['product_note']) ? $item['product_note'] : null,
            ]);

            return trim(implode(' — ', $parts));
        })
        ->filter()
        ->unique()
        ->values();
@endphp

<div class="protocol-guide-grid-cards">
    <div class="protocol-guide-card">
        <h4>Medication Program</h4>
        <p>Current non-vaccine actions visible in this live pig-facing program, including medication, support, procedure, and management items when present.</p>

        @if ($protocolMedicationProgramItems->isEmpty())
            <div class="protocol-empty">No current non-vaccine items are visible in this live schedule.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($protocolMedicationProgramItems as $item)
                    <div class="protocol-guide-list-item">
                        <strong>{{ $item['action'] }}</strong><br>
                        {{ ucfirst(str_replace('_', ' ', (string) ($item['type'] ?? 'item'))) }}
                        · {{ ucfirst((string) ($item['requirement'] ?? 'recommended')) }}
                        · {{ $item['due_start'] ?? '—' }}@if(!empty($item['due_end']) && $item['due_end'] !== $item['due_start']) to {{ $item['due_end'] }}@endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Medication Guide / Options</h4>

        @if ($isPigletProgram)
            <p>Use the piglet guide as reference only. Iron is a core early step. Coccidiosis prevention is herd-history based. Deworming is strategic, and support-only products do not replace diagnosis, hygiene, or core prevention.</p>

            <div class="protocol-guide-list">
                <div class="protocol-guide-list-item">
                    <strong>Iron support</strong><br>
                    Local example: Jectran Premium. Alternatives can include iron dextran or gleptoferron products.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Coccidiosis prevention</strong><br>
                    Use only when herd history supports it. Toltrazuril-type prevention belongs to herd-need logic, not one rigid universal day claim.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Deworming</strong><br>
                    Strategic by parasite pressure and product label. Local example: Latigo (levamisole). Alternatives may include fenbendazole or ivermectin.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Support-only products</strong><br>
                    B-complex, vitamin ADE, and anti-stress products are optional support tools. They should not be treated as equal to iron, hygiene, vaccines, or diagnosis.
                </div>
            </div>
        @elseif ($isSowProgram)
            <p>Fresh-sow medication support is sign-driven, not automatic. Postpartum treatment should follow the sow’s condition, nursing status, udder findings, discharge, appetite, and veterinary direction.</p>

            <div class="protocol-guide-list">
                <div class="protocol-guide-list-item">
                    <strong>Fresh-sow treatment</strong><br>
                    Treat sick or problem sows based on signs. Common categories may include NSAIDs, oxytocin when appropriate, fluids/support, and antimicrobials when clearly indicated.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Routine blanket antibiotic warning</strong><br>
                    Do not assume every fresh sow needs the same antibiotic by routine. This should not be presented as universal standard truth.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Support-only products</strong><br>
                    B-complex or vitamin ADE can support recovery, but they are optional support items, not universal requirements.
                </div>
            </div>
        @endif

        @if ($medicationReferenceTags->isNotEmpty())
            <div class="protocol-guide-tag-row">
                @foreach ($medicationReferenceTags as $tag)
                    <span class="protocol-guide-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Vaccination Program</h4>
        <p>Current vaccine items visible in this live pig-facing program.</p>

        @if ($protocolVaccinationProgramItems->isEmpty())
            <div class="protocol-empty">No current vaccination items are visible in this live schedule.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($protocolVaccinationProgramItems as $item)
                    <div class="protocol-guide-list-item">
                        <strong>{{ $item['action'] }}</strong><br>
                        {{ ucfirst((string) ($item['requirement'] ?? 'recommended')) }}
                        · {{ $item['due_start'] ?? '—' }}@if(!empty($item['due_end']) && $item['due_end'] !== $item['due_start']) to {{ $item['due_end'] }}@endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Vaccination Guide / Options</h4>

        @if ($isPigletProgram)
            <p>Vaccination timing is not universally rigid. Mycoplasma and hog cholera timing can be product-label or herd-program dependent, so the guide should stay separate from schedule truth.</p>

            <div class="protocol-guide-list">
                <div class="protocol-guide-list-item">
                    <strong>Mycoplasma</strong><br>
                    Timing differs by product. Examples include RespiSure / RespiSure-ONE, Ingelvac MycoFLEX, Porcilis PCV M. Hyo, and Hyogen.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Classical swine fever / hog cholera</strong><br>
                    Use where applicable to the local program and exact vaccine label. Examples include Coglapest and Porcilis CSF Live.
                </div>
            </div>
        @elseif ($isSowProgram)
            <p>Reproductive vaccines belong to the correct breeding-stage logic. They should not be blindly displayed as fixed lactation-day shots.</p>

            <div class="protocol-guide-list">
                <div class="protocol-guide-list-item">
                    <strong>Breeding-stage reproductive vaccines</strong><br>
                    Porcilis Parvo and FarrowSure Gold family products fit best before breeding or mating, not as a universal fixed day-14 lactation shot.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Pre-farrow piglet protection through sow</strong><br>
                    Some sow vaccines are given before farrowing so piglets receive antibodies through colostrum.
                </div>
            </div>
        @endif

        @if ($vaccinationReferenceTags->isNotEmpty())
            <div class="protocol-guide-tag-row">
                @foreach ($vaccinationReferenceTags as $tag)
                    <span class="protocol-guide-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Why / Explanation</h4>
        <p>This card explains why actions matter and what should never be misread as rigid universal truth.</p>

        <div class="protocol-guide-list">
            @if ($isPigletProgram)
                <div class="protocol-guide-list-item">
                    <strong>Foundation first</strong><br>
                    Colostrum, warmth, dry body, clean navel, and strong suckling are the true early survival foundation. No product replaces them.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Iron matters</strong><br>
                    Piglets are born with low iron stores, and sow milk does not provide enough iron.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Scours is not one disease</strong><br>
                    Diarrhea may be bacterial, viral, or parasitic. Supportive care and diagnosis matter more than repeating one blanket antibiotic routine.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Procedure vs medicine</strong><br>
                    Castration belongs in procedure/management logic, not inside a medication-only understanding of the program.
                </div>
            @elseif ($isSowProgram)
                <div class="protocol-guide-list-item">
                    <strong>Fresh-sow monitoring matters</strong><br>
                    Appetite, water intake, temperature, udder, milk let-down, discharge, and piglet nursing are the real postpartum watch points.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Parvo is breeding-stage logic</strong><br>
                    Reproductive vaccines such as Porcilis Parvo and FarrowSure Gold should follow breeding-stage timing rather than a blanket lactation-day rule.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Illness-driven care stays clinical</strong><br>
                    Post-farrow treatment should be driven by signs, not displayed as automatic universal medication routine.
                </div>

                <div class="protocol-guide-list-item">
                    <strong>Weaning is a management event</strong><br>
                    It should still be recorded because it affects next-step breeding workflow.
                </div>
            @endif

            <div class="protocol-guide-list-item">
                <strong>Important caution</strong><br>
                Product label, route, withdrawal period, herd veterinarian advice, and local disease program still govern real use. The guide is farm knowledge-base reference, not one-size-fits-all prescription truth.
            </div>
        </div>
    </div>
</div>
