<?php

require_once __DIR__ . '/../models/Pig.php';
require_once __DIR__ . '/../models/Pen.php';
require_once __DIR__ . '/../models/Vaccination.php';
require_once __DIR__ . '/../models/Mortality.php';
require_once __DIR__ . '/../models/Medication.php';
require_once __DIR__ . '/../models/HealthLog.php';

class PigController
{
    public function show(int $id): void
    {
        $pig = Pig::find($id);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $healthLogs = HealthLog::byPig($id);
        $vaccinations = Vaccination::byPig($id);
        $medications = Medication::byPig($id);
        $mortalityRecord = Mortality::findByPigId($id);
        $commonVaccines = Vaccination::commonVaccines();

        require __DIR__ . '/../views/pigs/show.php';
    }

    public function create(): void
    {
        $errors = [];
        $prefilledPen = trim($_GET['pen'] ?? '');

        $pig = [
            'id' => 0,
            'ear_tag' => '',
            'breed' => '',
            'sex' => '',
            'pen_location' => $prefilledPen,
            'status' => 'active',
            'origin_date' => '',
            'latest_weight' => '',
            'weight_date_added' => '',
            'asset_value' => '',
            'date_sold' => '',
            'weight_sold_kg' => '',
            'price_sold' => '',
        ];

        $pens = Pen::all();

        require __DIR__ . '/../views/pigs/create.php';
    }

    public function store(): void
    {
        $data = $this->collectPigInput();
        $errors = $this->validatePigInput($data);
        $pens = Pen::all();

        if (!empty($errors)) {
            $pig = $data;
            require __DIR__ . '/../views/pigs/create.php';
            return;
        }

        Pig::create($data);

        header('Location: /pens/' . rawurlencode($data['pen_location']));
        exit;
    }

    public function edit(int $id): void
    {
        $pig = Pig::find($id);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $errors = [];
        $pens = Pen::all();

        require __DIR__ . '/../views/pigs/edit.php';
    }

    public function update(): void
    {
        $data = $this->collectPigInput();
        $data['id'] = (int) ($_POST['id'] ?? 0);

        $errors = $this->validatePigInput($data);
        $pens = Pen::all();

        if (!empty($errors)) {
            $pig = $data;
            require __DIR__ . '/../views/pigs/edit.php';
            return;
        }

        Pig::update($data);

        header('Location: /pens/' . rawurlencode($data['pen_location']));
        exit;
    }

    public function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $pig = Pig::find($id);

        Pig::delete($id);

        if ($pig) {
            header('Location: /pens/' . rawurlencode($pig['pen_location']));
            exit;
        }

        header('Location: /dashboard');
        exit;
    }

    public function createHealth(int $pigId): void
    {
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $errors = [];
        $health = [
            'pig_id' => $pigId,
            'symptoms' => '',
            'temperature' => '',
            'notes' => '',
            'date' => '',
        ];

        require __DIR__ . '/../views/pigs/health-create.php';
    }

    public function storeHealth(): void
    {
        $pigId = (int) ($_POST['pig_id'] ?? 0);
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $health = [
            'pig_id' => $pigId,
            'symptoms' => trim($_POST['symptoms'] ?? ''),
            'temperature' => trim($_POST['temperature'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'date' => trim($_POST['date'] ?? ''),
        ];

        $errors = [];

        if ($health['symptoms'] === '') $errors[] = 'Symptoms are required.';
        if ($health['temperature'] === '') $errors[] = 'Temperature is required.';
        if ($health['date'] === '') $errors[] = 'Date is required.';

        if (!empty($errors)) {
            require __DIR__ . '/../views/pigs/health-create.php';
            return;
        }

        HealthLog::create($health);

        header('Location: /pigs/' . $pigId);
        exit;
    }

    public function createVaccination(int $pigId): void
    {
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $errors = [];
        $commonVaccines = Vaccination::commonVaccines();
        $vaccination = [
            'pig_id' => $pigId,
            'vaccine_name' => '',
            'date_given' => '',
            'next_due' => '',
            'notes' => '',
        ];

        require __DIR__ . '/../views/pigs/vaccination-create.php';
    }

    public function storeVaccination(): void
    {
        $pigId = (int) ($_POST['pig_id'] ?? 0);
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $vaccination = [
            'pig_id' => $pigId,
            'vaccine_name' => trim($_POST['vaccine_name'] ?? ''),
            'date_given' => trim($_POST['date_given'] ?? ''),
            'next_due' => trim($_POST['next_due'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $errors = [];

        if ($vaccination['vaccine_name'] === '') $errors[] = 'Vaccine name is required.';
        if ($vaccination['date_given'] === '') $errors[] = 'Date given is required.';
        if ($vaccination['next_due'] === '') $errors[] = 'Next due is required.';

        if (!empty($errors)) {
            $commonVaccines = Vaccination::commonVaccines();
            require __DIR__ . '/../views/pigs/vaccination-create.php';
            return;
        }

        Vaccination::create($vaccination);

        header('Location: /pigs/' . $pigId);
        exit;
    }

    public function createMortality(int $pigId): void
    {
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $errors = [];
        $mortality = [
            'pig_id' => $pigId,
            'date' => '',
            'cause' => '',
            'notes' => '',
        ];

        require __DIR__ . '/../views/pigs/mortality-create.php';
    }

    public function storeMortality(): void
    {
        $pigId = (int) ($_POST['pig_id'] ?? 0);
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $mortality = [
            'pig_id' => $pigId,
            'date' => trim($_POST['date'] ?? ''),
            'cause' => trim($_POST['cause'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $errors = [];

        if ($mortality['date'] === '') $errors[] = 'Mortality date is required.';
        if ($mortality['cause'] === '') $errors[] = 'Cause is required.';

        if (!empty($errors)) {
            require __DIR__ . '/../views/pigs/mortality-create.php';
            return;
        }

        Mortality::create($mortality);

        $pig['status'] = 'dead';
        Pig::update($pig);

        header('Location: /pigs/' . $pigId);
        exit;
    }

    public function createMedication(int $pigId): void
    {
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $errors = [];
        $medication = [
            'pig_id' => $pigId,
            'drug_name' => '',
            'dosage' => '',
            'start_date' => '',
            'end_date' => '',
            'notes' => '',
            'cost' => '',
        ];

        require __DIR__ . '/../views/pigs/medication-create.php';
    }

    public function storeMedication(): void
    {
        $pigId = (int) ($_POST['pig_id'] ?? 0);
        $pig = Pig::find($pigId);

        if (!$pig) {
            http_response_code(404);
            echo 'Pig not found';
            return;
        }

        $medication = [
            'pig_id' => $pigId,
            'drug_name' => trim($_POST['drug_name'] ?? ''),
            'dosage' => trim($_POST['dosage'] ?? ''),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'cost' => trim($_POST['cost'] ?? ''),
        ];

        $errors = [];

        if ($medication['drug_name'] === '') $errors[] = 'Drug name is required.';
        if ($medication['dosage'] === '') $errors[] = 'Dosage is required.';
        if ($medication['start_date'] === '') $errors[] = 'Start date is required.';
        if ($medication['end_date'] === '') $errors[] = 'End date is required.';
        if ($medication['cost'] !== '' && !is_numeric($medication['cost'])) $errors[] = 'Cost must be numeric.';

        if (!empty($errors)) {
            require __DIR__ . '/../views/pigs/medication-create.php';
            return;
        }

        Medication::create($medication);

        header('Location: /pigs/' . $pigId);
        exit;
    }

    private function collectPigInput(): array
    {
        $status = trim($_POST['status'] ?? 'active');

        return [
            'ear_tag' => trim($_POST['ear_tag'] ?? ''),
            'breed' => trim($_POST['breed'] ?? ''),
            'sex' => trim($_POST['sex'] ?? ''),
            'pen_location' => trim($_POST['pen_location'] ?? ''),
            'status' => $status,
            'origin_date' => trim($_POST['origin_date'] ?? ''),
            'latest_weight' => trim($_POST['latest_weight'] ?? ''),
            'weight_date_added' => trim($_POST['weight_date_added'] ?? ''),
            'asset_value' => trim($_POST['asset_value'] ?? ''),
            'date_sold' => $status === 'sold' ? trim($_POST['date_sold'] ?? '') : '',
            'weight_sold_kg' => $status === 'sold' ? trim($_POST['weight_sold_kg'] ?? '') : '',
            'price_sold' => $status === 'sold' ? trim($_POST['price_sold'] ?? '') : '',
        ];
    }

    private function validatePigInput(array $data): array
    {
        $errors = [];

        if ($data['ear_tag'] === '') $errors[] = 'Ear tag is required.';
        if ($data['breed'] === '') $errors[] = 'Breed is required.';
        if ($data['sex'] === '') $errors[] = 'Sex is required.';
        if ($data['pen_location'] === '') $errors[] = 'Pen location is required.';
        if ($data['status'] === '') $errors[] = 'Status is required.';
        if ($data['origin_date'] === '') $errors[] = 'Birth/Bought date is required.';
        if ($data['latest_weight'] === '' || !is_numeric($data['latest_weight'])) $errors[] = 'Latest weight is required and must be numeric.';
        if ($data['weight_date_added'] === '') $errors[] = 'Weight date added is required.';
        if ($data['asset_value'] === '' || !is_numeric($data['asset_value'])) $errors[] = 'Asset value is required and must be numeric.';

        if ($data['status'] === 'sold') {
            if ($data['date_sold'] === '') $errors[] = 'Date sold is required when status is sold.';
            if ($data['weight_sold_kg'] === '' || !is_numeric($data['weight_sold_kg'])) $errors[] = 'Weight sold (kg) is required and must be numeric when status is sold.';
            if ($data['price_sold'] === '' || !is_numeric($data['price_sold'])) $errors[] = 'Price sold is required and must be numeric when status is sold.';
        }

        return $errors;
    }
}
