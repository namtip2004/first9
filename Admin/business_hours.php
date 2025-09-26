<?php
session_start();

if (!isset($_SESSION['staff_id']) || ($_SESSION['staff_level'] ?? '') !== 'admin') {
    header('Location: loginadmin.php');
    exit;
}

require_once 'connect_db.php';

$daysOfWeek = [
    'Monday'    => 'Monday',
    'Tuesday'   => 'Tuesday',
    'Wednesday' => 'Wednesday',
    'Thursday'  => 'Thursday',
    'Friday'    => 'Friday',
    'Saturday'  => 'Saturday',
    'Sunday'    => 'Sunday',
];

$errors = [];
$successMessage = '';
$submittedValues = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];

    foreach ($daysOfWeek as $dayKey => $label) {
        $openInput = trim($_POST['open_time'][$dayKey] ?? '');
        $closeInput = trim($_POST['close_time'][$dayKey] ?? '');
        $isClosed = isset($_POST['is_closed'][$dayKey]) ? 1 : 0;

        $openTimeValue = null;
        $closeTimeValue = null;

        if ($openInput !== '') {
            $openDate = DateTime::createFromFormat('!H:i', $openInput);
            if (!$openDate || $openDate->format('H:i') !== $openInput) {
                $errors[] = "Invalid opening time format for {$label}";
                continue;
            }
            $openTimeValue = $openDate;
        }

        if ($closeInput !== '') {
            $closeDate = DateTime::createFromFormat('!H:i', $closeInput);
            if (!$closeDate || $closeDate->format('H:i') !== $closeInput) {
                $errors[] = "Invalid closing time format for {$label}";
                continue;
            }
            $closeTimeValue = $closeDate;
        }

        if ($isClosed === 0) {
            if ($openTimeValue === null) {
                $errors[] = "Please enter an opening time for {$label}";
                continue;
            }
            if ($closeTimeValue === null) {
                $errors[] = "Please enter a closing time for {$label}";
                continue;
            }
            if ($openTimeValue >= $closeTimeValue) {
                $errors[] = "Opening time must be earlier than closing time for {$label}";
                continue;
            }
        }

        $updates[$dayKey] = [
            'open_time'  => $openTimeValue ? $openTimeValue->format('H:i:s') : '00:00:00',
            'close_time' => $closeTimeValue ? $closeTimeValue->format('H:i:s') : '00:00:00',
            'is_closed'  => $isClosed,
        ];

        $submittedValues[$dayKey] = [
            'open_time'  => $openTimeValue ? $openTimeValue->format('H:i') : ($openInput !== '' ? $openInput : ''),
            'close_time' => $closeTimeValue ? $closeTimeValue->format('H:i') : ($closeInput !== '' ? $closeInput : ''),
            'is_closed'  => $isClosed,
        ];
    }

    if (empty($errors)) {
        foreach ($updates as $dayKey => $data) {
            $existingId = null;
            $checkStmt = $conn->prepare('SELECT id FROM business_hours WHERE day_of_week = ? LIMIT 1');
            $checkStmt->bind_param('s', $dayKey);
            $checkStmt->execute();
            $checkStmt->bind_result($existingId);
            $rowExists = $checkStmt->fetch();
            $checkStmt->close();

            if ($rowExists && $existingId) {
                $updateStmt = $conn->prepare('UPDATE business_hours SET open_time = ?, close_time = ?, is_closed = ? WHERE id = ?');
                $updateStmt->bind_param('ssii', $data['open_time'], $data['close_time'], $data['is_closed'], $existingId);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                $insertStmt = $conn->prepare('INSERT INTO business_hours (day_of_week, open_time, close_time, is_closed) VALUES (?, ?, ?, ?)');
                $insertStmt->bind_param('sssi', $dayKey, $data['open_time'], $data['close_time'], $data['is_closed']);
                $insertStmt->execute();
                $insertStmt->close();
            }
        }

        $successMessage = 'Business hours saved successfully.';
        $submittedValues = [];
    }
}

$hoursByDay = [];
foreach ($daysOfWeek as $dayKey => $label) {
    $hoursByDay[$dayKey] = [
        'open_time'  => '08:00',
        'close_time' => '18:00',
        'is_closed'  => 0,
    ];
}

$result = $conn->query('SELECT day_of_week, open_time, close_time, is_closed FROM business_hours');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dayKey = $row['day_of_week'];
        if (!isset($hoursByDay[$dayKey])) {
            continue;
        }
        $hoursByDay[$dayKey] = [
            'open_time'  => substr($row['open_time'], 0, 5),
            'close_time' => substr($row['close_time'], 0, 5),
            'is_closed'  => (int)$row['is_closed'],
        ];
    }
}

foreach ($submittedValues as $dayKey => $values) {
    if (isset($hoursByDay[$dayKey])) {
        $hoursByDay[$dayKey]['open_time'] = $values['open_time'] !== '' ? $values['open_time'] : $hoursByDay[$dayKey]['open_time'];
        $hoursByDay[$dayKey]['close_time'] = $values['close_time'] !== '' ? $values['close_time'] : $hoursByDay[$dayKey]['close_time'];
        $hoursByDay[$dayKey]['is_closed'] = $values['is_closed'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Hours</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'slidebar.php'; ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Business Hours</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Business Hours</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Configure Business Hours</h5>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif ($successMessage !== ''): ?>
                            <div class="alert alert-success">
                                <?= htmlspecialchars($successMessage) ?>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted">Set the opening and closing hours for each day and mark weekly closure days. The system uses this information to calculate when customers can make reservations.</p>

                        <form method="post" novalidate>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Day</th>
                                            <th style="width: 25%;">Opening Time</th>
                                            <th style="width: 25%;">Closing Time</th>
                                            <th style="width: 20%;" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daysOfWeek as $dayKey => $label):
                                            $row = $hoursByDay[$dayKey];
                                            $isClosed = (int)$row['is_closed'] === 1;
                                        ?>
                                            <tr data-day-row class="<?= $isClosed ? 'table-secondary' : '' ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($label) ?></strong>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control" name="open_time[<?= htmlspecialchars($dayKey) ?>]" value="<?= htmlspecialchars($row['open_time']) ?>">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control" name="close_time[<?= htmlspecialchars($dayKey) ?>]" value="<?= htmlspecialchars($row['close_time']) ?>">
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                                                        <input class="form-check-input toggle-closed" type="checkbox" role="switch" id="closed_<?= htmlspecialchars(strtolower($dayKey)) ?>" name="is_closed[<?= htmlspecialchars($dayKey) ?>]" value="1" <?= $isClosed ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="closed_<?= htmlspecialchars(strtolower($dayKey)) ?>">Closed</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script>
(function() {
    function toggleRow(row, checked) {
        if (!row) { return; }
        row.classList.toggle('table-secondary', checked);
        row.querySelectorAll('input[type="time"]').forEach(function(input) {
            input.readOnly = checked;
            input.classList.toggle('bg-light', checked);
        });
    }

    document.querySelectorAll('.toggle-closed').forEach(function(checkbox) {
        const row = checkbox.closest('tr');
        toggleRow(row, checkbox.checked);
        checkbox.addEventListener('change', function() {
            toggleRow(row, this.checked);
        });
    });
})();
</script>
</body>
</html>
