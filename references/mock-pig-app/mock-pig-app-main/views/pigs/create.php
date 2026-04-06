<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pig</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 760px; margin: 0 auto; }
        .card { background: #fff; padding: 24px; border-radius: 12px; }
        .back { display: inline-block; margin-bottom: 16px; text-decoration: none; color: #2563eb; }
        label { display: block; margin-top: 14px; margin-bottom: 6px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 12px 18px; border: none; border-radius: 8px; cursor: pointer; }
        .errors { background: #fff0f0; border: 1px solid #f3b4b4; padding: 12px; border-radius: 8px; margin-bottom: 16px; color: #9b1c1c; }
        .sold-fields { margin-top: 12px; padding: 14px; background: #f8fafc; border-radius: 10px; }
    </style>
    <script>
        function toggleSoldFields() {
            const status = document.getElementById('status').value;
            const soldFields = document.getElementById('sold-fields');
            const soldInputs = soldFields.querySelectorAll('input');

            if (status === 'sold') {
                soldFields.style.display = 'block';
                soldInputs.forEach(input => input.required = true);
            } else {
                soldFields.style.display = 'none';
                soldInputs.forEach(input => input.required = false);
            }
        }

        window.addEventListener('DOMContentLoaded', toggleSoldFields);
    </script>
</head>
<body>
    <div class="wrapper">
        <a href="/dashboard" class="back">← Back</a>

        <div class="card">
            <h1>Add New Pig</h1>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/pigs/store">
                <label for="ear_tag">Ear Tag</label>
                <input type="text" id="ear_tag" name="ear_tag" value="<?= htmlspecialchars($pig['ear_tag']) ?>" required>

                <label for="breed">Breed</label>
                <input type="text" id="breed" name="breed" value="<?= htmlspecialchars($pig['breed']) ?>" required>

                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="">Select sex</option>
                    <option value="male" <?= $pig['sex'] === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $pig['sex'] === 'female' ? 'selected' : '' ?>>Female</option>
                </select>

                <label for="pen_location">Pen Location</label>
                <select id="pen_location" name="pen_location" required>
                    <option value="">Select pen</option>
                    <?php foreach ($pens as $pen): ?>
                        <option value="<?= htmlspecialchars($pen) ?>" <?= $pig['pen_location'] === $pen ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pen) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="origin_date">Birth / Bought Date</label>
                <input type="date" id="origin_date" name="origin_date" value="<?= htmlspecialchars($pig['origin_date']) ?>" required>

                <label for="latest_weight">Latest Weight (kg)</label>
                <input type="number" step="0.01" id="latest_weight" name="latest_weight" value="<?= htmlspecialchars((string) $pig['latest_weight']) ?>" required>

                <label for="weight_date_added">Weight Date Added</label>
                <input type="date" id="weight_date_added" name="weight_date_added" value="<?= htmlspecialchars($pig['weight_date_added']) ?>" required>

                <label for="asset_value">Asset Value</label>
                <input type="number" step="0.01" id="asset_value" name="asset_value" value="<?= htmlspecialchars((string) $pig['asset_value']) ?>" required>

                <label for="status">Status</label>
                <select id="status" name="status" onchange="toggleSoldFields()" required>
                    <option value="active" <?= $pig['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="sold" <?= $pig['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="dead" <?= $pig['status'] === 'dead' ? 'selected' : '' ?>>Dead</option>
                </select>

                <div id="sold-fields" class="sold-fields">
                    <label for="date_sold">Date Sold</label>
                    <input type="date" id="date_sold" name="date_sold" value="<?= htmlspecialchars($pig['date_sold']) ?>">

                    <label for="weight_sold_kg">Weight Sold (kg)</label>
                    <input type="number" step="0.01" id="weight_sold_kg" name="weight_sold_kg" value="<?= htmlspecialchars((string) $pig['weight_sold_kg']) ?>">

                    <label for="price_sold">Price Sold</label>
                    <input type="number" step="0.01" id="price_sold" name="price_sold" value="<?= htmlspecialchars((string) $pig['price_sold']) ?>">
                </div>

                <button type="submit">Save Pig</button>
            </form>
        </div>
    </div>
</body>
</html>
