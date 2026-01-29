<?php
session_start();

// Payment data storage (in production, this should be a database)
$dataFile = __DIR__ . '/payment_data.json';

// Initialize default payment data if file doesn't exist
if (!file_exists($dataFile)) {
    $defaultData = [
        'drivers' => [
            'Tim Hockham' => ['deposit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Race Manager', 'team' => 'Management'],
            'Adrian Herrero Sanchez' => ['deposit' => 0, 'installment1' => 0, 'installment2' => 0, 'role' => 'Race Manager', 'team' => 'Management'],
            'Darren Ravenscroft' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Team Principal & Driver', 'team' => 'Alpha'],
            'Andy Tait' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha'],
            'Matt Casey' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha'],
            'Dave Parker' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha'],
            'Tomek Zet' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Alpha'],
            'Ryan Welch' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo'],
            'Luke Gore' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo'],
            'James Eaton' => ['deposit' => 100, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo'],
            'James Addison' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo'],
            'Daniel Lane' => ['deposit' => 200, 'installment1' => 0, 'installment2' => 0, 'role' => 'Driver', 'team' => 'Bravo']
        ],
        'deadlines' => [
            'deposit' => '2025-12-31',
            'installment1' => '2026-02-01',
            'installment2' => '2026-03-01'
        ],
        'total_per_driver' => 670
    ];
    file_put_contents($dataFile, json_encode($defaultData, JSON_PRETTY_PRINT));
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
    file_put_contents($dataFile, json_encode($paymentData, JSON_PRETTY_PRINT));
    $success = "Deadlines and total updated";
}

// Handle driver selection
$selectedDriver = isset($_POST['select_driver']) ? $_POST['driver_name'] : (isset($_GET['driver']) ? $_GET['driver'] : null);

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
      padding: 60px 20px 20px;
    }
    header img {
      max-width: 260px;
      height: auto;
      margin-bottom: 12px;
    }
    h2, h3 {
      color: #8b241d;
    }
    .section {
      padding: 2rem 0;
    }
    .payment-card {
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 8px;
      padding: 2rem;
      margin-bottom: 1rem;
    }
    .paid {
      color: #28a745;
      font-weight: bold;
    }
    .unpaid {
      color: #dc3545;
      font-weight: bold;
    }
    .pending {
      color: #ffc107;
      font-weight: bold;
    }
    .deadline {
      background: #8b241d;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      display: inline-block;
      margin: 0.5rem 0;
    }
    .btn-primary {
      background-color: #8b241d;
      border-color: #8b241d;
    }
    .btn-primary:hover {
      background-color: #6d1c16;
      border-color: #6d1c16;
    }
    .progress {
      height: 30px;
      background-color: #333;
    }
    .progress-bar {
      background-color: #8b241d;
      font-weight: bold;
    }
    .admin-panel {
      background: #1a1a1a;
      border: 2px solid #8b241d;
      border-radius: 8px;
      padding: 2rem;
      margin: 2rem 0;
    }
    table {
      color: #fff;
    }
    table th {
      color: #8b241d;
      border-bottom: 2px solid #8b241d;
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
    }
  </style>
</head>
<body>
  <header>
    <a href="/"><img src="https://res.cloudinary.com/dazrave/image/upload/v1602093800/Raven%20Motorsport/white-text.svg" alt="Raven Motorsport Logo"></a>
    <p>Payment Tracker | Daytona 24 Hours 2026</p>
  </header>

  <div class="container section">
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['admin'])): ?>
      <!-- DRIVER VIEW -->
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="text-center mb-4">Driver Payment Portal</h2>

          <?php if (!$selectedDriver): ?>
            <!-- Driver Selection -->
            <div class="payment-card">
              <h4 class="mb-3">Select Your Name</h4>
              <form method="POST">
                <select name="driver_name" class="form-control mb-3" required>
                  <option value="">-- Select Driver --</option>
                  <?php foreach ($paymentData['drivers'] as $name => $data): ?>
                    <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" name="select_driver" class="btn btn-primary w-100">View My Payments</button>
              </form>
            </div>
          <?php else: ?>
            <!-- Driver Payment Details -->
            <?php
            $driver = $paymentData['drivers'][$selectedDriver];
            $totalPaid = $driver['deposit'] + $driver['installment1'] + $driver['installment2'];
            $totalDue = $paymentData['total_per_driver'];
            $percentPaid = ($totalDue > 0) ? ($totalPaid / $totalDue) * 100 : 0;
            ?>

            <div class="payment-card">
              <h3 class="mb-3"><?php echo htmlspecialchars($selectedDriver); ?></h3>
              <p class="text-muted"><?php echo htmlspecialchars($driver['role']); ?></p>

              <div class="mb-4">
                <h5>Payment Progress</h5>
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: <?php echo $percentPaid; ?>%"
                       aria-valuenow="<?php echo $percentPaid; ?>" aria-valuemin="0" aria-valuemax="100">
                    £<?php echo number_format($totalPaid, 2); ?> / £<?php echo number_format($totalDue, 2); ?>
                  </div>
                </div>
                <p class="mt-2">
                  <?php if ($totalPaid >= $totalDue): ?>
                    <span class="paid">✓ Fully Paid</span>
                  <?php else: ?>
                    <span class="unpaid">Outstanding: £<?php echo number_format($totalDue - $totalPaid, 2); ?></span>
                  <?php endif; ?>
                </p>
              </div>

              <h5>Payment Breakdown</h5>
              <table class="table table-dark table-striped">
                <thead>
                  <tr>
                    <th>Payment</th>
                    <th>Deadline</th>
                    <th>Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Deposit</td>
                    <td><?php echo date('d M Y', strtotime($paymentData['deadlines']['deposit'])); ?></td>
                    <td>£<?php echo number_format($driver['deposit'], 2); ?></td>
                    <td>
                      <?php if ($driver['deposit'] >= 200): ?>
                        <span class="paid">✓ Paid</span>
                      <?php else: ?>
                        <span class="unpaid">✗ Unpaid</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Installment 1</td>
                    <td><?php echo date('d M Y', strtotime($paymentData['deadlines']['installment1'])); ?></td>
                    <td>£<?php echo number_format($driver['installment1'], 2); ?></td>
                    <td>
                      <?php if ($driver['installment1'] > 0): ?>
                        <span class="paid">✓ Paid</span>
                      <?php elseif (strtotime($paymentData['deadlines']['installment1']) > time()): ?>
                        <span class="pending">⧗ Pending</span>
                      <?php else: ?>
                        <span class="unpaid">✗ Overdue</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Installment 2</td>
                    <td><?php echo date('d M Y', strtotime($paymentData['deadlines']['installment2'])); ?></td>
                    <td>£<?php echo number_format($driver['installment2'], 2); ?></td>
                    <td>
                      <?php if ($driver['installment2'] > 0): ?>
                        <span class="paid">✓ Paid</span>
                      <?php elseif (strtotime($paymentData['deadlines']['installment2']) > time()): ?>
                        <span class="pending">⧗ Pending</span>
                      <?php else: ?>
                        <span class="unpaid">✗ Overdue</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div class="mt-4">
                <h5>Payment Methods</h5>
                <ul>
                  <li>PayPal: <a href="mailto:hello@dazrave.uk" style="color: #8b241d;">hello@dazrave.uk</a></li>
                  <li>Quick link: <a href="https://paypal.me/dazrave" target="_blank" style="color: #8b241d;">paypal.me/dazrave</a></li>
                  <li>Bank: (ac) <strong>03039125</strong> / (s) <strong>04-00-04</strong></li>
                  <li>Quick link: <a href="https://monzo.me/darrenravenscroft" target="_blank" style="color: #8b241d;">monzo.me/darrenravenscroft</a></li>
                </ul>
                <p class="mt-3"><small>After making a payment, contact the team to update your records.</small></p>
              </div>

              <a href="payments.php" class="btn btn-secondary mt-3">← Back to Selection</a>
            </div>
          <?php endif; ?>

          <!-- Admin Login -->
          <div class="payment-card mt-4">
            <h5>Team Management</h5>
            <form method="POST">
              <div class="input-group">
                <input type="password" name="passcode" class="form-control" placeholder="Enter admin passcode" required>
                <button type="submit" name="admin_login" class="btn btn-primary">Admin Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ADMIN PANEL -->
      <div class="row">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Panel</h2>
            <a href="?logout" class="btn btn-secondary">Logout</a>
          </div>

          <!-- Deadlines and Settings -->
          <div class="admin-panel mb-4">
            <h4>Payment Deadlines & Settings</h4>
            <form method="POST" class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Deposit Deadline</label>
                <input type="date" name="deadline_deposit" class="form-control" value="<?php echo $paymentData['deadlines']['deposit']; ?>" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Installment 1 Deadline</label>
                <input type="date" name="deadline_installment1" class="form-control" value="<?php echo $paymentData['deadlines']['installment1']; ?>" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Installment 2 Deadline</label>
                <input type="date" name="deadline_installment2" class="form-control" value="<?php echo $paymentData['deadlines']['installment2']; ?>" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Total Per Driver (£)</label>
                <input type="number" name="total_per_driver" class="form-control" step="0.01" value="<?php echo $paymentData['total_per_driver']; ?>" required>
              </div>
              <div class="col-12">
                <button type="submit" name="update_deadlines" class="btn btn-primary">Update Settings</button>
              </div>
            </form>
          </div>

          <!-- Driver Payment Management -->
          <div class="admin-panel">
            <h4 class="mb-4">Driver Payments</h4>

            <!-- Summary Table -->
            <div class="table-responsive">
              <table class="table table-dark table-hover">
                <thead>
                  <tr>
                    <th>Driver</th>
                    <th>Team</th>
                    <th>Role</th>
                    <th>Deposit</th>
                    <th>Installment 1</th>
                    <th>Installment 2</th>
                    <th>Total Paid</th>
                    <th>Outstanding</th>
                    <th>Actions</th>
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
                      <td><?php echo htmlspecialchars($driver['role']); ?></td>
                      <td>£<?php echo number_format($driver['deposit'], 2); ?></td>
                      <td>£<?php echo number_format($driver['installment1'], 2); ?></td>
                      <td>£<?php echo number_format($driver['installment2'], 2); ?></td>
                      <td><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                      <td class="<?php echo $outstanding <= 0 ? 'paid' : 'unpaid'; ?>">
                        £<?php echo number_format($outstanding, 2); ?>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="editDriver('<?php echo htmlspecialchars($name); ?>')">Edit</button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Edit Form (Hidden by default) -->
            <div id="editForm" style="display: none;" class="mt-4 p-3 border border-secondary rounded">
              <h5>Edit Payment for <span id="editDriverName"></span></h5>
              <form method="POST" class="row g-3">
                <input type="hidden" name="driver" id="editDriverInput">
                <div class="col-md-4">
                  <label class="form-label">Deposit (£)</label>
                  <input type="number" name="deposit" id="editDeposit" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Installment 1 (£)</label>
                  <input type="number" name="installment1" id="editInstallment1" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Installment 2 (£)</label>
                  <input type="number" name="installment2" id="editInstallment2" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="col-12">
                  <button type="submit" name="update_payment" class="btn btn-success">Save Payment</button>
                  <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
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

  <footer class="bg-black text-center py-3 small" style="color: #ccc;">
    <div class="container">
      &copy; Raven Motorsport 2025. <a href="/" style="color: #ccc; text-decoration: none;">Back to Home</a>
    </div>
  </footer>
</body>
</html>
