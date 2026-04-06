<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($penName) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 1100px; margin: 0 auto; }
        .back { display: inline-block; margin-bottom: 16px; color: #2563eb; text-decoration: none; }
        .top-actions { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e9e9e9; text-align: left; }
        th { background: #f0f2f5; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background: #eef2ff; }
        .actions { display: flex; gap: 8px; align-items: center; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        button { padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="wrapper">
        <a class="back" href="/dashboard">← Back to Dashboard</a>
        <h1><?= htmlspecialchars($penName) ?></h1>

        <div class="top-actions">
            <a href="/pigs/create?pen=<?= rawurlencode($penName) ?>">+ Add New Pig</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ear Tag</th>
                    <th>Breed</th>
                    <th>Sex</th>
                    <th>Latest Weight</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pigs as $pig): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $pig['id']) ?></td>
                        <td><a href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>"><?= htmlspecialchars($pig['ear_tag']) ?></a></td>
                        <td><?= htmlspecialchars($pig['breed']) ?></td>
                        <td><?= htmlspecialchars($pig['sex']) ?></td>
                        <td><?= number_format((float) $pig['latest_weight'], 2) ?> kg</td>
                        <td><span class="status"><?= htmlspecialchars($pig['status']) ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="/pigs/edit/<?= htmlspecialchars((string) $pig['id']) ?>">Edit</a>
                                <form method="POST" action="/pigs/delete" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $pig['id']) ?>">
                                    <button type="submit" onclick="return confirm('Delete this pig?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($pigs) === 0): ?>
                    <tr>
                        <td colspan="7">No pigs in this pen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
