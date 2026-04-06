<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Medication Record</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 760px; margin: 0 auto; }
        .card { background: #fff; padding: 24px; border-radius: 12px; }
        .back { display: inline-block; margin-bottom: 16px; text-decoration: none; color: #2563eb; }
        label { display: block; margin-top: 14px; margin-bottom: 6px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        textarea { min-height: 110px; resize: vertical; }
        button { margin-top: 20px; padding: 12px 18px; border: none; border-radius: 8px; cursor: pointer; }
        .errors { background: #fff0f0; border: 1px solid #f3b4b4; padding: 12px; border-radius: 8px; margin-bottom: 16px; color: #9b1c1c; }
    </style>
</head>
<body>
    <div class="wrapper">
        <a href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>" class="back">← Back to Pig Profile</a>

        <div class="card">
            <h1>Add Medication</h1>
            <p><strong>Pig:</strong> <?= htmlspecialchars($pig['ear_tag']) ?></p>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/medications/store">
                <input type="hidden" name="pig_id" value="<?= htmlspecialchars((string) $medication['pig_id']) ?>">

                <label for="drug_name">Drug Name</label>
                <input type="text" id="drug_name" name="drug_name" value="<?= htmlspecialchars($medication['drug_name']) ?>">

                <label for="dosage">Dosage</label>
                <input type="text" id="dosage" name="dosage" value="<?= htmlspecialchars($medication['dosage']) ?>">

                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($medication['start_date']) ?>">

                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($medication['end_date']) ?>">

                <label for="cost">Cost</label>
                <input type="number" step="0.01" id="cost" name="cost" value="<?= htmlspecialchars((string) $medication['cost']) ?>">

                <label for="notes">Notes</label>
                <textarea id="notes" name="notes"><?= htmlspecialchars($medication['notes']) ?></textarea>

                <button type="submit">Save Medication</button>
            </form>
        </div>
    </div>
</body>
</html>
