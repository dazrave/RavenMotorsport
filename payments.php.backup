<?php
session_start();

// Payment data storage (in production, this should be a database)
$dataFile = __DIR__ . '/payment_data.json';

// Initialize default payment data if file doesn't exist
if (!file_exists($dataFile)) {
    $defaultData = [
        'drivers' => [
            'Tim Hockham' => ['deposit' => 0, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Race Manager', 'team' => 'Management', 'is_driver' => false],
            'Adrian Herrero Sanchez' => ['deposit' => 0, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Race Manager', 'team' => 'Management', 'is_driver' => false],
            'Darren Ravenscroft' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Team Principal & Driver', 'team' => 'Alpha', 'is_driver' => true],
            'Andy Tait' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha', 'is_driver' => true],
            'Matt Casey' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha', 'is_driver' => true],
            'Dave Parker' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha', 'is_driver' => true],
            'Tomek Zet' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha', 'is_driver' => true],
            'Ryan Welch' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo', 'is_driver' => true],
            'Luke Gore' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo', 'is_driver' => true],
            'James Eaton' => ['deposit' => 100, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo', 'is_driver' => true],
            'James Addison' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo', 'is_driver' => true],
            'Daniel Lane' => ['deposit' => 200, 'team_kit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo', 'is_driver' => true]
        ],
        'deadlines' => [
            'deposit' => '2026-01-01',
            'installment1' => '2026-02-01',
            'installment2' => '2026-03-01'
        ],
        'total_per_driver' => 670,
        'team_kit_fee' => 0
    ];
    file_put_contents($dataFile, json_encode($defaultData, JSON_PRETTY_PRINT));
}

// Ensure team_kit_fee exists in loaded data (for backwards compatibility)
if (!isset($paymentData['team_kit_fee'])) {
    $paymentData['team_kit_fee'] = 0;
}

// Ensure all drivers have team_kit and is_driver fields (backwards compatibility)
foreach ($paymentData['drivers'] as $name => &$driver) {
    if (!isset($driver['team_kit'])) {
        $driver['team_kit'] = 0;
    }
    if (!isset($driver['is_driver'])) {
        $driver['is_driver'] = ($driver['team'] !== 'Management');
    }
}

// Load payment data
$paymentData = json_decode(file_get_contents($dataFile), true);

// Handle admin login
if (isset($_POST['admin_login'])) {
    if ($_POST['passcode'] === '3040') {
        $_SESSION['admin'] = true;
    } else {
        $error = "Incorrect passcode";
    }
}

// Handle admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: payments.php');
    exit;
}

// Handle payment updates from admin
if (isset($_POST['update_payment']) && isset($_SESSION['admin'])) {
    $driver = $_POST['driver'];
    if (isset($paymentData['drivers'][$driver])) {
        $paymentData['drivers'][$driver]['deposit'] = floatval($_POST['deposit']);
        $paymentData['drivers'][$driver]['team_kit'] = floatval($_POST['team_kit']);
        $paymentData['drivers'][$driver]['installment1'] = floatval($_POST['installment1']);
        $paymentData['drivers'][$driver]['installment2'] = floatval($_POST['installment2']);
        file_put_contents($dataFile, json_encode($paymentData, JSON_PRETTY_PRINT));
        $success = "Payment updated for " . htmlspecialchars($driver);
    }
}

// Handle deadline updates
if (isset($_POST['update_deadlines']) && isset($_SESSION['admin'])) {
    $paymentData['deadlines']['deposit'] = $_POST['deadline_deposit'];
    $paymentData['deadlines']['installment1'] = $_POST['deadline_installment1'];
    $paymentData['deadlines']['installment2'] = $_POST['deadline_installment2'];
    $paymentData['total_per_driver'] = floatval($_POST['total_per_driver']);
    $paymentData['team_kit_fee'] = floatval($_POST['team_kit_fee']);
    file_put_contents($dataFile, json_encode($paymentData, JSON_PRETTY_PRINT));
    $success = "Settings updated successfully";
}

// Calculate totals
$totalCollected = 0;
$totalOutstanding = 0;
foreach ($paymentData['drivers'] as $driver) {
    $paid = $driver['deposit'] + $driver['team_kit'] + $driver['installment1'] + $driver['installment2'];
    $totalCollected += $paid;
    // For drivers, add team kit fee to total expected
    $expected = $driver['is_driver'] ? ($paymentData['total_per_driver'] + $paymentData['team_kit_fee']) : 0;
    $totalOutstanding += ($expected - $paid);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Tracker | Raven Motorsport</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #000;
      color: #fff;
      line-height: 1.6;
    }
    header {
      text-align: center;
      padding: 40px 20px 20px;
    }
    header img {
      max-width: 200px;
      height: auto;
      margin-bottom: 12px;
    }
    h2, h3, h4 {
      color: #8b241d;
    }
    .section {
      padding: 1rem 0;
    }
    .payment-card {
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 0.75rem;
    }
    .driver-name {
      font-size: 1.1rem;
      font-weight: bold;
      margin-bottom: 0.25rem;
    }
    .driver-info {
      font-size: 0.85rem;
      color: #aaa;
      margin-bottom: 0.5rem;
    }
    .payment-status {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-top: 0.5rem;
    }
    .payment-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: bold;
      flex: 1;
      min-width: 80px;
      text-align: center;
    }
    .paid {
      background: #28a745;
      color: #fff;
    }
    .unpaid {
      background: #dc3545;
      color: #fff;
    }
    .pending {
      background: #ffc107;
      color: #000;
    }
    .partial {
      background: #ff8c00;
      color: #fff;
    }
    .progress {
      height: 20px;
      background-color: #333;
      margin-top: 0.5rem;
      border-radius: 4px;
    }
    .progress-bar {
      background-color: #8b241d;
      font-weight: bold;
      font-size: 0.75rem;
    }
    .deadline-box {
      background: #8b241d;
      padding: 1rem;
      border-radius: 8px;
      margin: 1rem 0;
      text-align: center;
    }
    .deadline-item {
      margin: 0.5rem 0;
      font-size: 0.9rem;
    }
    .btn-primary {
      background-color: #8b241d;
      border-color: #8b241d;
    }
    .btn-primary:hover {
      background-color: #6d1c16;
      border-color: #6d1c16;
    }
    .admin-panel {
      background: #1a1a1a;
      border: 2px solid #8b241d;
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
    }
    .summary-card {
      background: #2a2a2a;
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
      margin-bottom: 1rem;
    }
    .summary-amount {
      font-size: 1.5rem;
      font-weight: bold;
      color: #8b241d;
    }
    table {
      color: #fff;
      font-size: 0.9rem;
    }
    table th {
      color: #8b241d;
      border-bottom: 2px solid #8b241d;
      font-size: 0.85rem;
    }
    table td {
      border-bottom: 1px solid #333;
    }
    input[type="number"], input[type="date"], input[type="password"], select {
      background: #333;
      color: #fff;
      border: 1px solid #555;
    }
    input[type="number"]:focus, input[type="date"]:focus, input[type="password"]:focus, select:focus {
      background: #444;
      color: #fff;
      border-color: #8b241d;
    }
    .alert {
      border-radius: 4px;
      font-size: 0.9rem;
    }
    .team-header {
      background: #8b241d;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      margin: 1.5rem 0 1rem 0;
      font-weight: bold;
      font-size: 1.1rem;
    }
    footer {
      background: #000;
      color: #ccc;
      text-align: center;
      padding: 20px 0;
      font-size: 0.9rem;
      margin-top: 2rem;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
      header img {
        max-width: 150px;
      }
      .payment-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
        min-width: 70px;
      }
      .driver-name {
        font-size: 1rem;
      }
      table {
        font-size: 0.75rem;
      }
      table th, table td {
        padding: 0.5rem 0.25rem;
      }
      .summary-amount {
        font-size: 1.2rem;
      }
      .admin-panel {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="/"><img src="https://res.cloudinary.com/dazrave/image/upload/v1602093800/Raven%20Motorsport/white-text.svg" alt="Raven Motorsport Logo"></a>
    <p>Payment Tracker | Daytona 24 Hours 2026</p>
  </header>

  <div class="container-fluid px-3 px-md-4">
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['admin'])): ?>
      <!-- DRIVER VIEW -->
      <div class="row">
        <div class="col-12">
          <!-- Payment Deadlines -->
          <div class="deadline-box">
            <h4 class="mb-3" style="color: #fff;">Payment Schedule</h4>
            <div class="deadline-item">
              <strong>Deposit (£200):</strong> Due <?php echo date('d M Y', strtotime($paymentData['deadlines']['deposit'])); ?>
            </div>
            <div class="deadline-item">
              <strong>Installment 1:</strong> Due <?php echo date('d M Y', strtotime($paymentData['deadlines']['installment1'])); ?>
            </div>
            <div class="deadline-item">
              <strong>Installment 2:</strong> Due <?php echo date('d M Y', strtotime($paymentData['deadlines']['installment2'])); ?>
            </div>
            <div class="mt-3" style="font-size: 0.85rem; opacity: 0.9;">
              Total per driver: <strong>£<?php echo number_format($paymentData['total_per_driver'], 2); ?></strong>
            </div>
          </div>

          <!-- Summary Stats -->
          <div class="row mb-3">
            <div class="col-6 col-md-6">
              <div class="summary-card">
                <div class="summary-amount">£<?php echo number_format($totalCollected, 2); ?></div>
                <div style="font-size: 0.85rem; color: #aaa;">Total Collected</div>
              </div>
            </div>
            <div class="col-6 col-md-6">
              <div class="summary-card">
                <div class="summary-amount">£<?php echo number_format($totalOutstanding, 2); ?></div>
                <div style="font-size: 0.85rem; color: #aaa;">Outstanding</div>
              </div>
            </div>
          </div>

          <!-- Alpha Team -->
          <div class="team-header">Alpha Team</div>
          <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
            <?php if (isset($driver['team']) && $driver['team'] === 'Alpha'): ?>
              <?php
              $totalPaid = $driver['deposit'] + $driver['installment1'] + $driver['installment2'];
              $totalDue = $paymentData['total_per_driver'];
              $percentPaid = ($totalDue > 0) ? ($totalPaid / $totalDue) * 100 : 0;
              ?>
              <div class="payment-card">
                <div class="driver-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="driver-info"><?php echo htmlspecialchars($driver['role']); ?></div>

                <div class="payment-status">
                  <div class="payment-badge <?php echo $driver['deposit'] >= 200 ? 'paid' : ($driver['deposit'] > 0 ? 'partial' : 'unpaid'); ?>">
                    Deposit: £<?php echo number_format($driver['deposit'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment1'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment1']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 1: £<?php echo number_format($driver['installment1'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment2'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment2']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 2: £<?php echo number_format($driver['installment2'], 0); ?>
                  </div>
                </div>

                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $percentPaid; ?>%">
                    £<?php echo number_format($totalPaid, 0); ?> / £<?php echo number_format($totalDue, 0); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Bravo Team -->
          <div class="team-header">Bravo Team</div>
          <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
            <?php if (isset($driver['team']) && $driver['team'] === 'Bravo'): ?>
              <?php
              $totalPaid = $driver['deposit'] + $driver['installment1'] + $driver['installment2'];
              $totalDue = $paymentData['total_per_driver'];
              $percentPaid = ($totalDue > 0) ? ($totalPaid / $totalDue) * 100 : 0;
              ?>
              <div class="payment-card">
                <div class="driver-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="driver-info"><?php echo htmlspecialchars($driver['role']); ?></div>

                <div class="payment-status">
                  <div class="payment-badge <?php echo $driver['deposit'] >= 200 ? 'paid' : ($driver['deposit'] > 0 ? 'partial' : 'unpaid'); ?>">
                    Deposit: £<?php echo number_format($driver['deposit'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment1'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment1']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 1: £<?php echo number_format($driver['installment1'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment2'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment2']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 2: £<?php echo number_format($driver['installment2'], 0); ?>
                  </div>
                </div>

                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $percentPaid; ?>%">
                    £<?php echo number_format($totalPaid, 0); ?> / £<?php echo number_format($totalDue, 0); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Management Team -->
          <div class="team-header">Management Team</div>
          <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
            <?php if (isset($driver['team']) && $driver['team'] === 'Management'): ?>
              <?php
              $totalPaid = $driver['deposit'] + $driver['installment1'] + $driver['installment2'];
              $totalDue = $paymentData['total_per_driver'];
              $percentPaid = ($totalDue > 0) ? ($totalPaid / $totalDue) * 100 : 0;
              ?>
              <div class="payment-card">
                <div class="driver-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="driver-info"><?php echo htmlspecialchars($driver['role']); ?></div>

                <div class="payment-status">
                  <div class="payment-badge <?php echo $driver['deposit'] >= 200 ? 'paid' : ($driver['deposit'] > 0 ? 'partial' : 'unpaid'); ?>">
                    Deposit: £<?php echo number_format($driver['deposit'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment1'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment1']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 1: £<?php echo number_format($driver['installment1'], 0); ?>
                  </div>
                  <div class="payment-badge <?php echo $driver['installment2'] > 0 ? 'paid' : (strtotime($paymentData['deadlines']['installment2']) > time() ? 'pending' : 'unpaid'); ?>">
                    Inst 2: £<?php echo number_format($driver['installment2'], 0); ?>
                  </div>
                </div>

                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $percentPaid; ?>%">
                    £<?php echo number_format($totalPaid, 0); ?> / £<?php echo number_format($totalDue, 0); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Payment Methods -->
          <div class="payment-card mt-4">
            <h5 style="color: #8b241d;">Payment Methods</h5>
            <ul style="font-size: 0.9rem; margin-bottom: 0;">
              <li>PayPal: <a href="mailto:hello@dazrave.uk" style="color: #8b241d;">hello@dazrave.uk</a></li>
              <li>Quick link: <a href="https://paypal.me/dazrave" target="_blank" style="color: #8b241d;">paypal.me/dazrave</a></li>
              <li>Bank: (ac) <strong>03039125</strong> / (s) <strong>04-00-04</strong></li>
              <li>Quick link: <a href="https://monzo.me/darrenravenscroft" target="_blank" style="color: #8b241d;">monzo.me/darrenravenscroft</a></li>
            </ul>
          </div>

          <!-- Admin Login -->
          <div class="payment-card mt-3">
            <h5 style="color: #8b241d;">Team Management</h5>
            <form method="POST">
              <div class="input-group">
                <input type="password" name="passcode" class="form-control" placeholder="Admin passcode" required>
                <button type="submit" name="admin_login" class="btn btn-primary">Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ADMIN PANEL -->
      <div class="row">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Admin Panel</h2>
            <a href="?logout" class="btn btn-secondary btn-sm">Logout</a>
          </div>

          <!-- Deadlines and Settings -->
          <div class="admin-panel mb-3">
            <h4>Payment Settings</h4>
            <form method="POST" class="row g-3">
              <div class="col-6 col-md-3">
                <label class="form-label small">Deposit Deadline</label>
                <input type="date" name="deadline_deposit" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['deposit']; ?>" required>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small">Installment 1</label>
                <input type="date" name="deadline_installment1" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['installment1']; ?>" required>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small">Installment 2</label>
                <input type="date" name="deadline_installment2" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['installment2']; ?>" required>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small">Total Per Driver (£)</label>
                <input type="number" name="total_per_driver" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['total_per_driver']; ?>" required>
              </div>
              <div class="col-12">
                <button type="submit" name="update_deadlines" class="btn btn-primary btn-sm">Update Settings</button>
              </div>
            </form>
          </div>

          <!-- Driver Payment Management -->
          <div class="admin-panel">
            <h4 class="mb-3">Driver Payments</h4>

            <div class="table-responsive">
              <table class="table table-dark table-hover table-sm">
                <thead>
                  <tr>
                    <th>Driver</th>
                    <th>Team</th>
                    <th>Deposit</th>
                    <th>Inst 1</th>
                    <th>Inst 2</th>
                    <th>Total</th>
                    <th>Outstanding</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                    <?php
                    $totalPaid = $driver['deposit'] + $driver['installment1'] + $driver['installment2'];
                    $outstanding = $paymentData['total_per_driver'] - $totalPaid;
                    ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                      <td><?php echo isset($driver['team']) ? htmlspecialchars($driver['team']) : '-'; ?></td>
                      <td>£<?php echo number_format($driver['deposit'], 0); ?></td>
                      <td>£<?php echo number_format($driver['installment1'], 0); ?></td>
                      <td>£<?php echo number_format($driver['installment2'], 0); ?></td>
                      <td><strong>£<?php echo number_format($totalPaid, 0); ?></strong></td>
                      <td class="<?php echo $outstanding <= 0 ? 'paid' : 'unpaid'; ?>">
                        £<?php echo number_format($outstanding, 0); ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="editDriver('<?php echo htmlspecialchars($name); ?>')">Edit</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Edit Form -->
            <div id="editForm" style="display: none;" class="mt-3 p-3 border border-secondary rounded">
              <h5>Edit Payment for <span id="editDriverName"></span></h5>
              <form method="POST" class="row g-3">
                <input type="hidden" name="driver" id="editDriverInput">
                <div class="col-4">
                  <label class="form-label small">Deposit (£)</label>
                  <input type="number" name="deposit" id="editDeposit" class="form-control form-control-sm" step="0.01" min="0" required>
                </div>
                <div class="col-4">
                  <label class="form-label small">Installment 1 (£)</label>
                  <input type="number" name="installment1" id="editInstallment1" class="form-control form-control-sm" step="0.01" min="0" required>
                </div>
                <div class="col-4">
                  <label class="form-label small">Installment 2 (£)</label>
                  <input type="number" name="installment2" id="editInstallment2" class="form-control form-control-sm" step="0.01" min="0" required>
                </div>
                <div class="col-12">
                  <button type="submit" name="update_payment" class="btn btn-success btn-sm">Save</button>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit()">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <script>
        const drivers = <?php echo json_encode($paymentData['drivers']); ?>;

        function editDriver(name) {
          document.getElementById('editForm').style.display = 'block';
          document.getElementById('editDriverName').textContent = name;
          document.getElementById('editDriverInput').value = name;
          document.getElementById('editDeposit').value = drivers[name].deposit;
          document.getElementById('editInstallment1').value = drivers[name].installment1;
          document.getElementById('editInstallment2').value = drivers[name].installment2;
          document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
        }

        function cancelEdit() {
          document.getElementById('editForm').style.display = 'none';
        }
      </script>
    <?php endif; ?>
  </div>

  <footer>
    <div class="container">
      &copy; Raven Motorsport 2025. <a href="/" style="color: #ccc; text-decoration: none;">Back to Home</a>
    </div>
  </footer>
</body>
</html>
