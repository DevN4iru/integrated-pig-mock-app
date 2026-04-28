<?php

namespace App\Services;

class PreFarrowReminderSchedule
{
    public static function items(): array
    {
        return [
            [
                'code' => 'pre_farrow_vaccine_review_35',
                'days_before_farrow' => 35,
                'days_before' => 35,
                'title' => 'Pre-farrow vaccine/program review',
                'label' => 'Pre-farrow vaccine/program review',
                'message' => 'Review sow pre-farrow vaccine plan with the farm protocol or veterinarian. This is a prevention reminder before farrowing, not an automatic drug order.',
                'note' => 'Review farm/vet vaccine plan. Not an automatic drug order.',
            ],
            [
                'code' => 'parasite_check_21',
                'days_before_farrow' => 21,
                'days_before' => 21,
                'title' => 'Pre-farrow parasite/deworming check',
                'label' => 'Parasite/deworming check',
                'message' => 'Check internal/external parasite control plan before farrowing. Follow vet direction and product label timing.',
                'note' => 'Check internal/external parasite control timing with vet/product label.',
            ],
            [
                'code' => 'booster_check_14',
                'days_before_farrow' => 14,
                'days_before' => 14,
                'title' => 'Pre-farrow booster/vaccine check',
                'label' => 'Booster/vaccine check',
                'message' => 'Check if a booster or pre-farrow vaccine action is due. Product choice and dose must follow vet/farm protocol.',
                'note' => 'Check if booster or pre-farrow vaccine action is due.',
            ],
            [
                'code' => 'final_prefarrow_check_7',
                'days_before_farrow' => 7,
                'days_before' => 7,
                'title' => 'Final pre-farrow medication and hygiene check',
                'label' => 'Final medication and hygiene check',
                'message' => 'Final check for pre-farrow parasite control, udder/belly hygiene, farrowing area readiness, and any vet-directed medication.',
                'note' => 'Final pre-farrow medication, udder/belly hygiene, and pen readiness check.',
            ],
        ];
    }
}
