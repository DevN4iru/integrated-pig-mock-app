<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 8px; }
        p { color: #666; }
        .top-link { margin: 20px 0; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .grid.two { grid-template-columns: repeat(2, 1fr); }
        .card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .card h3 { margin: 0 0 8px; font-size: 16px; }
        .big { font-size: 28px; font-weight: bold; }
        .small { font-size: 13px; color: #666; margin-top: 8px; }
        .pen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .pen-card-wrap { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
        .pen-card { text-decoration: none; color: inherit; display: block; }
        .pen-card:hover { transform: translateY(-2px); }
        .pen-title { font-weight: bold; margin-bottom: 8px; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .switcher { display: flex; gap: 10px; margin: 24px 0 16px; }
        .switch-btn {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background: #e5e7eb;
        }
        .switch-btn.active {
            background: #2563eb;
            color: #fff;
        }
        .panel { display: none; }
        .panel.active { display: block; }
        .muted { color: #888; font-size: 14px; }
        .actions { margin-bottom: 20px; }
        .pen-actions {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .delete-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
    <script>
        function showPanel(id) {
            document.querySelectorAll('.panel').forEach(function(panel) {
                panel.classList.remove('active');
            });

            document.querySelectorAll('.switch-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });

            document.getElementById(id).classList.add('active');
            document.getElementById('btn-' + id).classList.add('active');
        }
    </script>
</head>
<body>
    <div class="wrapper">
        <h1>Pig System Dashboard</h1>
        <p>Dashboard → Pen View → Pig Profile</p>

        <div class="actions">
            <a href="/pens/create">+ Add New Pen</a>
        </div>

        <div class="grid">
            <div class="card">
                <h3>Vaccine Upcoming</h3>
                <div class="big"><?= $alerts['vaccine_upcoming'] ?></div>
            </div>
            <div class="card">
                <h3>Vaccine Missed</h3>
                <div class="big"><?= $alerts['vaccine_missed'] ?></div>
            </div>
            <div class="card">
                <h3>Medication Ending</h3>
                <div class="big"><?= $alerts['medication_ending'] ?></div>
            </div>
            <div class="card">
                <h3>Medication Overdue</h3>
                <div class="big"><?= $alerts['medication_overdue'] ?></div>
            </div>
        </div>

        <div class="grid two">
            <div class="card">
                <h3>Mortality Total</h3>
                <div class="big"><?= $mortality['total_dead'] ?></div>
            </div>
            <div class="card">
                <h3>Recent Mortality Case</h3>
                <div class="big"><?= htmlspecialchars($mortality['recent_case']) ?></div>
                <div class="small"><?= htmlspecialchars($mortality['recent_cause']) ?></div>
            </div>
        </div>

        <div class="switcher">
            <button id="btn-overview-panel" class="switch-btn active" onclick="showPanel('overview-panel')">Overview</button>
            <button id="btn-sales-panel" class="switch-btn" onclick="showPanel('sales-panel')">Sales</button>
        </div>

        <div id="overview-panel" class="panel active">
            <div class="grid">
                <div class="card">
                    <h3>Total Asset Count</h3>
                    <div class="big"><?= $totalAssets ?></div>
                </div>
            </div>
        </div>

        <div id="sales-panel" class="panel">
            <div class="grid">
                <div class="card">
                    <h3>Asset Value</h3>
                    <div class="big"><?= $assetValue === null ? 'Not set' : '₱' . number_format($assetValue, 2) ?></div>
                    <?php if ($assetValue === null): ?><div class="muted">Add asset values to pigs first.</div><?php endif; ?>
                </div>
                <div class="card">
                    <h3>Liability</h3>
                    <div class="big"><?= $liabilityValue === null ? 'Not set' : '₱' . number_format($liabilityValue, 2) ?></div>
                    <?php if ($liabilityValue === null): ?><div class="muted">Add medication costs first.</div><?php endif; ?>
                </div>
                <div class="card">
                    <h3>Loss</h3>
                    <div class="big"><?= $lossValue === null ? 'Not set' : '₱' . number_format($lossValue, 2) ?></div>
                    <?php if ($lossValue === null): ?><div class="muted">Dead pigs need asset values.</div><?php endif; ?>
                </div>
                <div class="card">
                    <h3>Sold Pigs</h3>
                    <div class="big"><?= $sales['sold_count'] ?></div>
                </div>
            </div>

            <div class="grid">
                <div class="card">
                    <h3>Total Sales</h3>
                    <div class="big">₱<?= number_format($sales['total_sales'], 2) ?></div>
                </div>
                <div class="card">
                    <h3>Total Sold Weight</h3>
                    <div class="big"><?= number_format($sales['total_sold_weight'], 2) ?> kg</div>
                </div>
                <div class="card">
                    <h3>Average Price / Kg</h3>
                    <div class="big">₱<?= number_format($sales['avg_price_per_kg'], 2) ?></div>
                </div>
            </div>
        </div>

        <h2>Pens</h2>
        <div class="pen-grid">
            <?php foreach ($pens as $pen): ?>
                <div class="pen-card-wrap">
                    <a class="pen-card" href="/pens/<?= rawurlencode($pen) ?>">
                        <div class="pen-title"><?= htmlspecialchars($pen) ?></div>
                        <div>Pigs inside: <?= $penCounts[$pen] ?></div>
                    </a>

                    <div class="pen-actions">
                        <a href="/pens/<?= rawurlencode($pen) ?>">Open</a>

                        <form method="POST" action="/pens/delete" onsubmit="return confirm('Delete this pen?');">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($pen) ?>">
                            <button type="submit" class="delete-btn">Delete Pen</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
