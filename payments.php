<?php
session_start();

// Payment data storage
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
        'team_kit_fee' => 0,
        'expected_amounts' => [
            'deposit' => 200,
            'installment1' => 223.50,
            'installment2' => 246.50
        ]
    ];
    file_put_contents($dataFile, json_encode($defaultData, JSON_PRETTY_PRINT));
}

// Load payment data
$paymentData = json_decode(file_get_contents($dataFile), true);

// Ensure data loaded correctly
if (!is_array($paymentData)) {
    $paymentData = [
        'drivers' => [],
        'deadlines' => [
            'deposit' => '2026-01-01',
            'installment1' => '2026-02-01',
            'installment2' => '2026-03-01'
        ],
        'total_per_driver' => 670,
        'team_kit_fee' => 0,
        'expected_amounts' => [
            'deposit' => 200,
            'installment1' => 223.50,
            'installment2' => 246.50
        ]
    ];
}

// Ensure team_kit_fee exists
if (!isset($paymentData['team_kit_fee'])) {
    $paymentData['team_kit_fee'] = 0;
}

// Ensure expected_amounts exists
if (!isset($paymentData['expected_amounts'])) {
    $paymentData['expected_amounts'] = [
        'deposit' => 200,
        'installment1' => 223.50,
        'installment2' => 246.50
    ];
}

// Ensure drivers array exists
if (!isset($paymentData['drivers']) || !is_array($paymentData['drivers'])) {
    $paymentData['drivers'] = [];
}

// Ensure all drivers have required fields
foreach ($paymentData['drivers'] as $name => &$driver) {
    if (!is_array($driver)) {
        continue;
    }
    if (!isset($driver['team_kit'])) {
        $driver['team_kit'] = 0;
    }
    if (!isset($driver['is_driver'])) {
        $driver['is_driver'] = (isset($driver['team']) && $driver['team'] !== 'Management');
    }
}

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
    header('Location: /payments');
    exit;
}

// Handle logging a new payment
if (isset($_POST['log_payment']) && isset($_SESSION['admin'])) {
    $driver = $_POST['driver'];
    $paymentType = $_POST['payment_type'];
    $amount = floatval($_POST['amount']);

    if (isset($paymentData['drivers'][$driver]) && $amount > 0) {
        // Add to existing amount
        $paymentData['drivers'][$driver][$paymentType] += $amount;

        // Log the payment in history
        if (!isset($paymentData['payment_history'])) {
            $paymentData['payment_history'] = [];
        }
        $paymentData['payment_history'][] = [
            'driver' => $driver,
            'type' => $paymentType,
            'amount' => $amount,
            'date' => date('Y-m-d H:i:s')
        ];

        file_put_contents($dataFile, json_encode($paymentData, JSON_PRETTY_PRINT));
        $success = "Logged £" . number_format($amount, 2) . " payment for " . htmlspecialchars($driver) . " (" . ucfirst(str_replace('_', ' ', $paymentType)) . ")";
    }
}

// Handle payment updates (manual edit)
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

// Handle settings updates
if (isset($_POST['update_deadlines']) && isset($_SESSION['admin'])) {
    $paymentData['deadlines']['deposit'] = $_POST['deadline_deposit'];
    $paymentData['deadlines']['installment1'] = $_POST['deadline_installment1'];
    $paymentData['deadlines']['installment2'] = $_POST['deadline_installment2'];
    $paymentData['total_per_driver'] = floatval($_POST['total_per_driver']);
    $paymentData['team_kit_fee'] = floatval($_POST['team_kit_fee']);
    $paymentData['expected_amounts']['deposit'] = floatval($_POST['expected_deposit']);
    $paymentData['expected_amounts']['installment1'] = floatval($_POST['expected_installment1']);
    $paymentData['expected_amounts']['installment2'] = floatval($_POST['expected_installment2']);
    file_put_contents($dataFile, json_encode($paymentData, JSON_PRETTY_PRINT));
    $success = "Settings updated successfully";
}

// Calculate totals
$totalCollected = 0;
$totalOutstanding = 0;
foreach ($paymentData['drivers'] as $driver) {
    $paid = $driver['deposit'] + $driver['team_kit'] + $driver['installment1'] + $driver['installment2'];
    $totalCollected += $paid;
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
  <meta name="description" content="Track driver payments for Raven Motorsport's 2026 Daytona 24 Hours entry. View payment status and deadlines.">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://ravenmotorsport.com/payments">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #000;
      color: #fff;
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
    .info-box {
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    .deadline-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    .deadline-item {
      background: #2a2a2a;
      padding: 1rem;
      border-radius: 4px;
      text-align: center;
    }
    .deadline-item strong {
      color: #8b241d;
      display: block;
      margin-bottom: 0.5rem;
    }
    table {
      background: #1a1a1a;
      border: 1px solid #333;
    }
    table thead {
      background: #2a2a2a;
      border-bottom: 2px solid #8b241d;
    }
    table th {
      color: #8b241d;
      font-weight: 600;
      border-bottom: none;
      padding: 1rem 0.75rem;
    }
    table td {
      border-bottom: 1px solid #2a2a2a;
      padding: 0.75rem;
      vertical-align: middle;
    }
    table tbody tr:hover {
      background: #222;
    }
    .team-section {
      margin-bottom: 2rem;
    }
    .team-header {
      background: #8b241d;
      color: #fff;
      padding: 0.75rem 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
      font-weight: bold;
    }
    .paid {
      color: #28a745;
    }
    .unpaid {
      color: #666;
    }
    .partial {
      color: #ffc107;
    }
    .check {
      font-size: 1.2rem;
    }
    .btn-primary {
      background-color: #8b241d;
      border-color: #8b241d;
    }
    .btn-primary:hover {
      background-color: #6d1c16;
      border-color: #6d1c16;
    }
    .btn-success {
      background-color: #28a745;
      border-color: #28a745;
    }
    .btn-success:hover {
      background-color: #218838;
      border-color: #1e7e34;
    }
    .admin-panel {
      background: #1a1a1a;
      border: 2px solid #8b241d;
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    input[type="number"], input[type="date"], input[type="password"], select.form-control {
      background: #333;
      color: #fff;
      border: 1px solid #555;
    }
    input[type="number"]:focus, input[type="date"]:focus, input[type="password"]:focus, select.form-control:focus {
      background: #444;
      color: #fff;
      border-color: #8b241d;
    }
    select.form-control option {
      background: #333;
      color: #fff;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
      table {
        font-size: 0.85rem;
      }
      table th, table td {
        padding: 0.5rem 0.4rem;
      }
      .deadline-grid {
        grid-template-columns: 1fr;
      }
      header img {
        max-width: 150px;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="/"><img src="https://res.cloudinary.com/dazrave/image/upload/v1602093800/Raven%20Motorsport/white-text.svg" alt="Raven Motorsport Logo"></a>
    <p>Payment Tracker | Daytona 24 Hours 2026</p>
  </header>

  <div class="container-fluid px-3 px-lg-5">
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['admin'])): ?>
      <!-- DRIVER VIEW -->

      <!-- Payment Schedule -->
      <div class="info-box">
        <h4>Payment Schedule</h4>
        <div class="deadline-grid">
          <div class="deadline-item">
            <strong>Deposit</strong>
            £<?php echo number_format($paymentData['expected_amounts']['deposit'], 2); ?><br>
            <small>Due: <?php echo date('d M Y', strtotime($paymentData['deadlines']['deposit'])); ?></small>
          </div>
          <div class="deadline-item">
            <strong>Installment 1</strong>
            £<?php echo number_format($paymentData['expected_amounts']['installment1'], 2); ?><br>
            <small>Due: <?php echo date('d M Y', strtotime($paymentData['deadlines']['installment1'])); ?></small>
          </div>
          <div class="deadline-item">
            <strong>Installment 2</strong>
            £<?php echo number_format($paymentData['expected_amounts']['installment2'], 2); ?><br>
            <small>Due: <?php echo date('d M Y', strtotime($paymentData['deadlines']['installment2'])); ?></small>
          </div>
          <?php if ($paymentData['team_kit_fee'] > 0): ?>
          <div class="deadline-item">
            <strong>Team Kit</strong>
            £<?php echo number_format($paymentData['team_kit_fee'], 2); ?><br>
            <small>Optional</small>
          </div>
          <?php endif; ?>
        </div>
        <div class="text-center mt-3">
          <small>Total per driver: <strong>£<?php echo number_format($paymentData['total_per_driver'], 2); ?></strong></small>
        </div>
      </div>

      <!-- Alpha Team -->
      <div class="team-section">
        <div class="team-header">Alpha Team</div>
        <div class="table-responsive">
          <table class="table table-dark table-hover">
            <thead>
              <tr>
                <th>Driver</th>
                <th class="text-center">Deposit</th>
                <?php if ($paymentData['team_kit_fee'] > 0): ?>
                <th class="text-center">Kit</th>
                <?php endif; ?>
                <th class="text-center">Inst 1</th>
                <th class="text-center">Inst 2</th>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                <?php if ($driver['team'] === 'Alpha' && $driver['is_driver']): ?>
                  <?php
                  $totalPaid = $driver['deposit'] + $driver['team_kit'] + $driver['installment1'] + $driver['installment2'];
                  $totalDue = $paymentData['total_per_driver'] + $paymentData['team_kit_fee'];
                  $outstanding = $totalDue - $totalPaid;
                  ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                    <td class="text-center">
                      <?php if ($driver['deposit'] >= $paymentData['expected_amounts']['deposit']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['deposit'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['deposit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php if ($paymentData['team_kit_fee'] > 0): ?>
                    <td class="text-center">
                      <?php if ($driver['team_kit'] >= $paymentData['team_kit_fee']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['team_kit'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['team_kit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="text-center">
                      <?php if ($driver['installment1'] >= $paymentData['expected_amounts']['installment1']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['installment1'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['installment1'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ($driver['installment2'] >= $paymentData['expected_amounts']['installment2']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['installment2'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['installment2'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end"><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                    <td class="text-end <?php echo $outstanding <= 0 ? 'paid' : 'unpaid'; ?>">
                      £<?php echo number_format($outstanding, 2); ?>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bravo Team -->
      <div class="team-section">
        <div class="team-header">Bravo Team</div>
        <div class="table-responsive">
          <table class="table table-dark table-hover">
            <thead>
              <tr>
                <th>Driver</th>
                <th class="text-center">Deposit</th>
                <?php if ($paymentData['team_kit_fee'] > 0): ?>
                <th class="text-center">Kit</th>
                <?php endif; ?>
                <th class="text-center">Inst 1</th>
                <th class="text-center">Inst 2</th>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                <?php if ($driver['team'] === 'Bravo' && $driver['is_driver']): ?>
                  <?php
                  $totalPaid = $driver['deposit'] + $driver['team_kit'] + $driver['installment1'] + $driver['installment2'];
                  $totalDue = $paymentData['total_per_driver'] + $paymentData['team_kit_fee'];
                  $outstanding = $totalDue - $totalPaid;
                  ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                    <td class="text-center">
                      <?php if ($driver['deposit'] >= $paymentData['expected_amounts']['deposit']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['deposit'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['deposit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php if ($paymentData['team_kit_fee'] > 0): ?>
                    <td class="text-center">
                      <?php if ($driver['team_kit'] >= $paymentData['team_kit_fee']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['team_kit'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['team_kit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="text-center">
                      <?php if ($driver['installment1'] >= $paymentData['expected_amounts']['installment1']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['installment1'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['installment1'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ($driver['installment2'] >= $paymentData['expected_amounts']['installment2']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['installment2'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['installment2'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end"><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                    <td class="text-end <?php echo $outstanding <= 0 ? 'paid' : 'unpaid'; ?>">
                      £<?php echo number_format($outstanding, 2); ?>
                    </td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Management Team -->
      <?php
      $hasManagement = false;
      foreach ($paymentData['drivers'] as $driver) {
        if (!$driver['is_driver']) {
          $hasManagement = true;
          break;
        }
      }
      ?>
      <?php if ($hasManagement): ?>
      <div class="team-section">
        <div class="team-header">Management & Support</div>
        <div class="table-responsive">
          <table class="table table-dark table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th class="text-center">Deposit</th>
                <?php if ($paymentData['team_kit_fee'] > 0): ?>
                <th class="text-center">Team Kit</th>
                <?php endif; ?>
                <th class="text-end">Total Paid</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                <?php if (!$driver['is_driver']): ?>
                  <?php $totalPaid = $driver['deposit'] + $driver['team_kit']; ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                    <td><?php echo htmlspecialchars($driver['role']); ?></td>
                    <td class="text-center">
                      <?php if ($driver['deposit'] > 0): ?>
                        <span class="paid">£<?php echo number_format($driver['deposit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php if ($paymentData['team_kit_fee'] > 0): ?>
                    <td class="text-center">
                      <?php if ($driver['team_kit'] >= $paymentData['team_kit_fee']): ?>
                        <span class="paid check">✓</span>
                      <?php elseif ($driver['team_kit'] > 0): ?>
                        <span class="partial">£<?php echo number_format($driver['team_kit'], 0); ?></span>
                      <?php else: ?>
                        <span class="unpaid">-</span>
                      <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="text-end"><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Payment Info -->
      <div class="info-box">
        <h5 style="color: #8b241d;">Payment Methods</h5>
        <ul class="mb-0">
          <li>PayPal: <a href="mailto:hello@dazrave.uk" style="color: #8b241d;">hello@dazrave.uk</a> or <a href="https://paypal.me/dazrave" target="_blank" style="color: #8b241d;">paypal.me/dazrave</a></li>
          <li>Bank: (ac) <strong>03039125</strong> / (s) <strong>04-00-04</strong> or <a href="https://monzo.me/darrenravenscroft" target="_blank" style="color: #8b241d;">monzo.me/darrenravenscroft</a></li>
        </ul>
      </div>

      <!-- Admin Login Button -->
      <div class="text-center mb-4">
        <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#adminModal">
          Team Management Login
        </button>
      </div>

      <!-- Admin Login Modal -->
      <div class="modal fade" id="adminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content" style="background: #1a1a1a; border: 1px solid #333;">
            <div class="modal-header" style="border-bottom: 1px solid #333;">
              <h5 class="modal-title" style="color: #8b241d;">Team Management</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
              <div class="modal-body">
                <label class="form-label">Admin Passcode</label>
                <input type="password" name="passcode" class="form-control" placeholder="Enter passcode" required autofocus>
              </div>
              <div class="modal-footer" style="border-top: 1px solid #333;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="admin_login" class="btn btn-primary">Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ADMIN PANEL -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Admin Panel</h2>
        <a href="?logout" class="btn btn-secondary btn-sm">Logout</a>
      </div>

      <!-- Admin Summary -->
      <div class="row mb-4">
        <div class="col-md-6 mb-3">
          <div class="info-box text-center">
            <h3 class="mb-0">£<?php echo number_format($totalCollected, 2); ?></h3>
            <small>Total Collected</small>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="info-box text-center">
            <h3 class="mb-0">£<?php echo number_format($totalOutstanding, 2); ?></h3>
            <small>Outstanding</small>
          </div>
        </div>
      </div>

      <!-- Log Payment -->
      <div class="admin-panel">
        <h4>Log Payment</h4>
        <form method="POST" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select name="driver" class="form-control" required>
              <option value="">Select driver...</option>
              <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                <option value="<?php echo htmlspecialchars($name); ?>"><?php echo htmlspecialchars($name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Payment Type</label>
            <select name="payment_type" class="form-control" required>
              <option value="deposit">Deposit</option>
              <option value="installment1">Installment 1</option>
              <option value="installment2">Installment 2</option>
              <option value="team_kit">Team Kit</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount (£)</label>
            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" name="log_payment" class="btn btn-success w-100">Log Payment</button>
          </div>
        </form>

        <!-- Recent Payments -->
        <?php if (isset($paymentData['payment_history']) && count($paymentData['payment_history']) > 0): ?>
          <div class="mt-4">
            <h5 class="h6">Recent Payments (Last 10)</h5>
            <div class="table-responsive">
              <table class="table table-dark table-sm">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Driver</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $recentPayments = array_slice(array_reverse($paymentData['payment_history']), 0, 10);
                  foreach ($recentPayments as $payment):
                  ?>
                    <tr>
                      <td><?php echo date('d M Y H:i', strtotime($payment['date'])); ?></td>
                      <td><?php echo htmlspecialchars($payment['driver']); ?></td>
                      <td><?php echo ucfirst(str_replace('_', ' ', $payment['type'])); ?></td>
                      <td class="text-end">£<?php echo number_format($payment['amount'], 2); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Settings -->
      <div class="admin-panel">
        <h4>Payment Settings</h4>
        <form method="POST" class="row g-3">
          <div class="col-12"><h5 class="h6 text-light">Deadlines</h5></div>
          <div class="col-md-4">
            <label class="form-label small">Deposit Deadline</label>
            <input type="date" name="deadline_deposit" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['deposit']; ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Installment 1</label>
            <input type="date" name="deadline_installment1" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['installment1']; ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Installment 2</label>
            <input type="date" name="deadline_installment2" class="form-control form-control-sm" value="<?php echo $paymentData['deadlines']['installment2']; ?>" required>
          </div>

          <div class="col-12 mt-3"><h5 class="h6 text-light">Expected Amounts Per Driver</h5></div>
          <div class="col-md-4">
            <label class="form-label small">Deposit (£)</label>
            <input type="number" name="expected_deposit" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['expected_amounts']['deposit']; ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Installment 1 (£)</label>
            <input type="number" name="expected_installment1" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['expected_amounts']['installment1']; ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Installment 2 (£)</label>
            <input type="number" name="expected_installment2" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['expected_amounts']['installment2']; ?>" required>
          </div>

          <div class="col-12 mt-3"><h5 class="h6 text-light">Totals</h5></div>
          <div class="col-md-6">
            <label class="form-label small">Total Per Driver (£)</label>
            <input type="number" name="total_per_driver" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['total_per_driver']; ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Team Kit Fee (£)</label>
            <input type="number" name="team_kit_fee" class="form-control form-control-sm" step="0.01" value="<?php echo $paymentData['team_kit_fee']; ?>" required>
          </div>
          <div class="col-12 mt-3">
            <button type="submit" name="update_deadlines" class="btn btn-primary btn-sm">Update Settings</button>
          </div>
        </form>
      </div>

      <!-- Driver Payments -->
      <div class="admin-panel">
        <h4>Edit Driver Payments</h4>
        <div class="table-responsive">
          <table class="table table-dark table-hover table-sm">
            <thead>
              <tr>
                <th>Driver</th>
                <th>Team</th>
                <th>Deposit</th>
                <th>Kit</th>
                <th>Inst 1</th>
                <th>Inst 2</th>
                <th>Total</th>
                <th>Outstanding</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($paymentData['drivers'] as $name => $driver): ?>
                <?php
                $totalPaid = $driver['deposit'] + $driver['team_kit'] + $driver['installment1'] + $driver['installment2'];
                $expected = $driver['is_driver'] ? ($paymentData['total_per_driver'] + $paymentData['team_kit_fee']) : 0;
                $outstanding = $expected - $totalPaid;
                ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                  <td><?php echo htmlspecialchars($driver['team']); ?></td>
                  <td>£<?php echo number_format($driver['deposit'], 0); ?></td>
                  <td>£<?php echo number_format($driver['team_kit'], 0); ?></td>
                  <td>£<?php echo number_format($driver['installment1'], 0); ?></td>
                  <td>£<?php echo number_format($driver['installment2'], 0); ?></td>
                  <td><strong>£<?php echo number_format($totalPaid, 0); ?></strong></td>
                  <td><?php echo $driver['is_driver'] ? '£' . number_format($outstanding, 0) : '-'; ?></td>
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
          <h5>Edit: <span id="editDriverName"></span></h5>
          <form method="POST" class="row g-3">
            <input type="hidden" name="driver" id="editDriverInput">
            <div class="col-3">
              <label class="form-label small">Deposit (£)</label>
              <input type="number" name="deposit" id="editDeposit" class="form-control form-control-sm" step="0.01" min="0" required>
            </div>
            <div class="col-3">
              <label class="form-label small">Team Kit (£)</label>
              <input type="number" name="team_kit" id="editTeamKit" class="form-control form-control-sm" step="0.01" min="0" required>
            </div>
            <div class="col-3">
              <label class="form-label small">Installment 1 (£)</label>
              <input type="number" name="installment1" id="editInstallment1" class="form-control form-control-sm" step="0.01" min="0" required>
            </div>
            <div class="col-3">
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

      <script>
        const drivers = <?php echo json_encode($paymentData['drivers']); ?>;

        function editDriver(name) {
          document.getElementById('editForm').style.display = 'block';
          document.getElementById('editDriverName').textContent = name;
          document.getElementById('editDriverInput').value = name;
          document.getElementById('editDeposit').value = drivers[name].deposit;
          document.getElementById('editTeamKit').value = drivers[name].team_kit;
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

  <footer style="background: #000; color: #ccc; text-align: center; padding: 20px 0; margin-top: 3rem;">
    &copy; Raven Motorsport 2025. <a href="/" style="color: #ccc; text-decoration: none;">Back to Home</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
