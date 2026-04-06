<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Vaccination</title>
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
        .help { color: #555; font-size: 14px; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <a href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>" class="back">← Back to Pig Profile</a>

        <div class="card">
            <h1>Add Vaccination</h1>
            <p><strong>Pig:</strong> <?= htmlspecialchars($pig['ear_tag']) ?></p>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/vaccinations/store">
                <input type="hidden" name="pig_id" value="<?= htmlspecialchars((string) $vaccination['pig_id']) ?>">

                <label for="vaccine_name">Vaccine Name</label>
                <input list="common-vaccines" id="vaccine_name" name="vaccine_name" value="<?= htmlspecialchars($vaccination['vaccine_name']) ?>" placeholder="Type or choose a vaccine">
                <datalist id="common-vaccines">
                    <?php foreach ($commonVaccines as $name): ?>
                        <option value="<?= htmlspecialchars($name) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="help">Common suggestions are preloaded for faster entry.</div>

                <label for="date_given">Date Given</label>
                <input type="date" id="date_given" name="date_given" value="<?= htmlspecialchars($vaccination['date_given']) ?>">

                <label for="next_due">Next Due</label>
                <input type="date" id="next_due" name="next_due" value="<?= htmlspecialchars($vaccination['next_due']) ?>">

                <label for="notes">Notes / Reminder Message</label>
                <textarea id="notes" name="notes" placeholder="Write notes or reminder text here..."><?= htmlspecialchars($vaccination['notes']) ?></textarea>

                <button type="submit">Save Vaccination</button>
            </form>
        </div>
    </div>
</body>
</html>
