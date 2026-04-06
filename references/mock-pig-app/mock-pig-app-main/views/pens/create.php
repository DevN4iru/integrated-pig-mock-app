<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Pen</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 700px; margin: 0 auto; }
        .card { background: #fff; padding: 24px; border-radius: 12px; }
        .back { display: inline-block; margin-bottom: 16px; text-decoration: none; color: #2563eb; }
        label { display: block; margin-top: 14px; margin-bottom: 6px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 12px 18px; border: none; border-radius: 8px; cursor: pointer; }
        .errors { background: #fff0f0; border: 1px solid #f3b4b4; padding: 12px; border-radius: 8px; margin-bottom: 16px; color: #9b1c1c; }
    </style>
</head>
<body>
    <div class="wrapper">
        <a href="/dashboard" class="back">← Back to Dashboard</a>

        <div class="card">
            <h1>Add New Pen</h1>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/pens/store">
                <label for="name">Pen Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($pen['name']) ?>" required>

                <button type="submit">Save Pen</button>
            </form>
        </div>
    </div>
</body>
</html>
