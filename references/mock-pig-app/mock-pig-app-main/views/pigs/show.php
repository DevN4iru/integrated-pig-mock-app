<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pig Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 32px; background: #f6f7fb; color: #222; }
        .wrapper { max-width: 980px; margin: auto; }
        .card { background: #fff; padding: 20px; border-radius: 12px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .tab { padding: 8px 14px; border-radius: 8px; background: #eee; cursor: pointer; }
        .tab.active { background: #2563eb; color: #fff; }
        .section { display: none; }
        .section.active { display: block; }
        .row { margin-bottom: 10px; }
        .back { display: inline-block; margin-bottom: 10px; color: #2563eb; text-decoration: none; }
        .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background: #eef2ff; }
        .vax-status, .med-status { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; }
        .upcoming { background: #fff7ed; }
        .missed { background: #fee2e2; }
        .scheduled { background: #eef2ff; }
        .ending { background: #fff7ed; }
        .overdue { background: #fee2e2; }
        .ongoing { background: #eef2ff; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #f0f2f5; }
        .top-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 12px; }
        .search { width: 280px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        .btn-link { display: inline-block; padding: 10px 14px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; }
        .notes { color: #555; white-space: pre-wrap; }
    </style>
    <script>
        function showTab(id) {
            document.querySelectorAll('.section').forEach(function (s) {
                s.classList.remove('active');
            });

            document.querySelectorAll('.tab').forEach(function (t) {
                t.classList.remove('active');
            });

            document.getElementById(id).classList.add('active');
            document.getElementById('tab-' + id).classList.add('active');
        }

        function filterVaccines() {
            const q = document.getElementById('vaccine-search').value.toLowerCase();
            document.querySelectorAll('.vaccine-row').forEach(function (row) {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        function filterMeds() {
            const q = document.getElementById('med-search').value.toLowerCase();
            document.querySelectorAll('.med-row').forEach(function (row) {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        function filterHealth() {
            const q = document.getElementById('health-search').value.toLowerCase();
            document.querySelectorAll('.health-row').forEach(function (row) {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }
    </script>
</head>
<body>
    <div class="wrapper">
        <a href="/pens/<?= rawurlencode($pig['pen_location']) ?>" class="back">← Back to Pen</a>

        <div class="card">
            <h1>Pig Profile</h1>

            <div class="tabs">
                <div id="tab-details" class="tab active" onclick="showTab('details')">Details</div>
                <div id="tab-health" class="tab" onclick="showTab('health')">Health</div>
                <div id="tab-vaccine" class="tab" onclick="showTab('vaccine')">Vaccines</div>
                <div id="tab-med" class="tab" onclick="showTab('med')">Medications</div>
                <div id="tab-weight" class="tab" onclick="showTab('weight')">Weights</div>
                <div id="tab-mortality" class="tab" onclick="showTab('mortality')">Mortality</div>
                <div id="tab-sales" class="tab" onclick="showTab('sales')">Sales</div>
            </div>

            <div id="details" class="section active">
                <div class="row"><strong>ID:</strong> <?= htmlspecialchars((string) $pig['id']) ?></div>
                <div class="row"><strong>Ear Tag:</strong> <?= htmlspecialchars($pig['ear_tag']) ?></div>
                <div class="row"><strong>Breed:</strong> <?= htmlspecialchars($pig['breed']) ?></div>
                <div class="row"><strong>Sex:</strong> <?= htmlspecialchars($pig['sex']) ?></div>
                <div class="row"><strong>Pen:</strong> <?= htmlspecialchars($pig['pen_location']) ?></div>
                <div class="row"><strong>Status:</strong> <span class="pill"><?= htmlspecialchars($pig['status']) ?></span></div>
                <div class="row"><strong>Birth / Bought Date:</strong> <?= htmlspecialchars($pig['origin_date'] ?? '') ?></div>
            </div>

            <div id="health" class="section">
                <div class="top-actions">
                    <a class="btn-link" href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>/health/create">+ Add Health Log</a>
                    <input id="health-search" class="search" type="text" placeholder="Search health logs..." onkeyup="filterHealth()">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Symptoms</th>
                            <th>Temperature</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($healthLogs as $h): ?>
                            <tr class="health-row">
                                <td><?= htmlspecialchars($h['date']) ?></td>
                                <td><?= htmlspecialchars($h['symptoms']) ?></td>
                                <td><?= htmlspecialchars($h['temperature']) ?></td>
                                <td class="notes"><?= htmlspecialchars($h['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($healthLogs)): ?>
                            <tr><td colspan="4">No health logs yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="vaccine" class="section">
                <div class="top-actions">
                    <a class="btn-link" href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>/vaccinations/create">+ Add Vaccination</a>
                    <input id="vaccine-search" class="search" type="text" placeholder="Search vaccine records..." onkeyup="filterVaccines()">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Date Given</th>
                            <th>Next Due</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vaccinations as $v): ?>
                            <?php $status = Vaccination::status($v); ?>
                            <tr class="vaccine-row">
                                <td><?= htmlspecialchars($v['vaccine_name']) ?></td>
                                <td><?= htmlspecialchars($v['date_given']) ?></td>
                                <td><?= htmlspecialchars($v['next_due']) ?></td>
                                <td><span class="vax-status <?= htmlspecialchars($status) ?>"><?= strtoupper(htmlspecialchars($status)) ?></span></td>
                                <td class="notes"><?= htmlspecialchars($v['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($vaccinations)): ?>
                            <tr><td colspan="5">No vaccination records</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="med" class="section">
                <div class="top-actions">
                    <a class="btn-link" href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>/medications/create">+ Add Medication</a>
                    <input id="med-search" class="search" type="text" placeholder="Search medication records..." onkeyup="filterMeds()">
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Dosage</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medications as $m): ?>
                            <?php $status = Medication::status($m); ?>
                            <tr class="med-row">
                                <td><?= htmlspecialchars($m['drug_name']) ?></td>
                                <td><?= htmlspecialchars($m['dosage']) ?></td>
                                <td><?= htmlspecialchars($m['start_date']) ?></td>
                                <td><?= htmlspecialchars($m['end_date']) ?></td>
                                <td><span class="med-status <?= htmlspecialchars($status) ?>"><?= strtoupper(htmlspecialchars($status)) ?></span></td>
                                <td class="notes"><?= htmlspecialchars($m['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($medications)): ?>
                            <tr><td colspan="6">No medication records</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="weight" class="section">
                <div class="row"><strong>Latest Weight:</strong> <?= number_format((float) $pig['latest_weight'], 2) ?> kg</div>
                <div class="row"><strong>Weight Date Added:</strong> <?= htmlspecialchars($pig['weight_date_added'] ?? '') ?></div>
            </div>

            <div id="mortality" class="section">
                <?php if ($mortalityRecord): ?>
                    <div class="row"><strong>Date:</strong> <?= htmlspecialchars($mortalityRecord['date']) ?></div>
                    <div class="row"><strong>Cause:</strong> <?= htmlspecialchars($mortalityRecord['cause']) ?></div>
                    <div class="row"><strong>Notes:</strong> <?= htmlspecialchars($mortalityRecord['notes'] ?? '') ?></div>
                <?php else: ?>
                    <a class="btn-link" href="/pigs/<?= htmlspecialchars((string) $pig['id']) ?>/mortality/create">+ Add Mortality Record</a>
                    <p style="margin-top:14px;">No mortality record yet</p>
                <?php endif; ?>
            </div>

            <div id="sales" class="section">
                <?php if ($pig['status'] === 'sold'): ?>
                    <div class="row"><strong>Date Sold:</strong> <?= htmlspecialchars($pig['date_sold']) ?></div>
                    <div class="row"><strong>Weight Sold:</strong> <?= number_format((float) $pig['weight_sold_kg'], 2) ?> kg</div>
                    <div class="row"><strong>Price Sold:</strong> ₱<?= number_format((float) $pig['price_sold'], 2) ?></div>
                    <div class="row"><strong>Price / Kg:</strong> ₱<?= number_format(((float) $pig['weight_sold_kg']) > 0 ? ((float) $pig['price_sold'] / (float) $pig['weight_sold_kg']) : 0, 2) ?></div>
                <?php else: ?>
                    <p>This pig is not marked as sold.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
