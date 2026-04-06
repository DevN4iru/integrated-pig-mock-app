<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submitted Pig</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f6f7fb;
            color: #222;
        }

        .wrapper {
            max-width: 700px;
            margin: 0 auto;
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
        }

        .back {
            display: inline-block;
            margin-bottom: 16px;
            text-decoration: none;
            color: #2563eb;
        }

        .row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        strong {
            display: inline-block;
            width: 140px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <a href="/pigs/create" class="back">← Back to Form</a>

        <div class="card">
            <h1>Submitted Pig Data</h1>

            <div class="row"><strong>Ear Tag:</strong> <?= htmlspecialchars($pig['ear_tag']) ?></div>
            <div class="row"><strong>Breed:</strong> <?= htmlspecialchars($pig['breed']) ?></div>
            <div class="row"><strong>Sex:</strong> <?= htmlspecialchars($pig['sex']) ?></div>
            <div class="row"><strong>Pen Location:</strong> <?= htmlspecialchars($pig['pen_location']) ?></div>
            <div class="row"><strong>Status:</strong> <?= htmlspecialchars($pig['status']) ?></div>
        </div>
    </div>
</body>
</html>
