<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Pig List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f6f7fb;
            color: #222;
        }

        .wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            margin-bottom: 16px;
        }

        p {
            margin-bottom: 24px;
            color: #555;
        }

        .top-link {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #e9e9e9;
            text-align: left;
        }

        th {
            background: #f0f2f5;
        }

        tr:hover {
            background: #fafafa;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: #eef2ff;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        button {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Pig List</h1>
        <p>Click any ear tag to view the pig profile.</p>

        <p class="top-link">
            <a href="/pigs/create">+ Create New Pig</a>
        </p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ear Tag</th>
                    <th>Breed</th>
                    <th>Sex</th>
                    <th>Pen Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pigs as $pig): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $pig['id']) ?></td>
                        <td>
                            <a href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>">
                                <?= htmlspecialchars($pig['ear_tag']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($pig['breed']) ?></td>
                        <td><?= htmlspecialchars($pig['sex']) ?></td>
                        <td><?= htmlspecialchars($pig['pen_location']) ?></td>
                        <td>
                            <span class="status">
                                <?= htmlspecialchars($pig['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="/pigs/edit/<?= htmlspecialchars((string) $pig['id']) ?>">Edit</a>

                                <form method="POST" action="/pigs/delete" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $pig['id']) ?>">
                                    <button type="submit" onclick="return confirm('Delete this pig?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
