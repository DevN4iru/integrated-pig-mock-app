<?php

require_once __DIR__ . '/../models/Pig.php';
require_once __DIR__ . '/../models/Pen.php';
require_once __DIR__ . '/../models/Vaccination.php';
require_once __DIR__ . '/../models/Mortality.php';
require_once __DIR__ . '/../models/Medication.php';

class DashboardController
{
    public function index(): void
    {
        $pens = Pen::all();

        $penCounts = [];
        foreach ($pens as $pen) {
            $penCounts[$pen] = count(Pig::byPen($pen));
        }

        $vaccineSummary = Vaccination::dashboardSummary();
        $medicationSummary = Medication::dashboardSummary();

        $alerts = [
            'vaccine_upcoming' => $vaccineSummary['vaccine_upcoming'],
            'vaccine_missed' => $vaccineSummary['vaccine_missed'],
            'medication_ending' => $medicationSummary['medication_ending'],
            'medication_overdue' => $medicationSummary['medication_overdue'],
        ];

        $mortality = Mortality::summary();
        $sales = Pig::salesSummary();
        $totalAssets = Pig::totalAssets();
        $assetValue = Pig::totalAssetValue();
        $lossValue = Pig::totalLossValue();
        $liabilityValue = Medication::totalLiability();

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
