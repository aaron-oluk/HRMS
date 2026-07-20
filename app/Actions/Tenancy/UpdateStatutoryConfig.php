<?php

namespace App\Actions\Tenancy;

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use Illuminate\Support\Facades\DB;

class UpdateStatutoryConfig
{
    /**
     * @param  array{bands: array<int, array{floor: numeric-string|float, rate: numeric-string|float}>, paye_surcharge_floor: numeric-string|float, paye_surcharge_rate: numeric-string|float, nssf_employee_rate: numeric-string|float, nssf_employer_rate: numeric-string|float}  $data
     */
    public function handle(array $data): void
    {
        DB::transaction(function () use ($data): void {
            StatutoryPayeBand::query()->delete();

            foreach ($data['bands'] as $index => $band) {
                StatutoryPayeBand::create([
                    'floor' => $band['floor'],
                    'rate' => $band['rate'],
                    'order' => $index + 1,
                ]);
            }

            StatutorySetting::current()->update([
                'paye_surcharge_floor' => $data['paye_surcharge_floor'],
                'paye_surcharge_rate' => $data['paye_surcharge_rate'],
                'nssf_employee_rate' => $data['nssf_employee_rate'],
                'nssf_employer_rate' => $data['nssf_employer_rate'],
            ]);
        });
    }
}
