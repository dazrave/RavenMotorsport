<?php
session_start();

// Database initialization
$dbFile = __DIR__ . '/payment_tracker.db';
$db = new SQLite3($dbFile);

// Create tables if they don't exist
$db->exec("
CREATE TABLE IF NOT EXISTS teams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    entry_cost REAL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    team_id INTEGER NOT NULL,
    role TEXT,
    is_driver INTEGER DEFAULT 1,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id)
);

CREATE TABLE IF NOT EXISTS installments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    due_date TEXT,
    amount_per_driver REAL DEFAULT 0,
    display_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    driver_id INTEGER NOT NULL,
    installment_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    payment_date TEXT DEFAULT CURRENT_TIMESTAMP,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (installment_id) REFERENCES installments(id)
);

CREATE TABLE IF NOT EXISTS settings (
    key TEXT PRIMARY KEY,
    value TEXT
);
");

// Initialize default data if tables are empty
$teamCount = $db->querySingle("SELECT COUNT(*) FROM teams");
if ($teamCount == 0) {
    // Insert teams
    $db->exec("
        INSERT INTO teams (name, entry_cost) VALUES
        ('Alpha', 3350),
        ('Bravo', 3350),
        ('Management', 0);
    ");

    // Insert drivers
    $alphaId = $db->querySingle("SELECT id FROM teams WHERE name='Alpha'");
    $bravoId = $db->querySingle("SELECT id FROM teams WHERE name='Bravo'");
    $mgmtId = $db->querySingle("SELECT id FROM teams WHERE name='Management'");

    $db->exec("
        INSERT INTO drivers (name, team_id, role, is_driver) VALUES
        ('Tim Hockham', $mgmtId, 'Race Manager', 0),
        ('Adrian Herrero Sanchez', $mgmtId, 'Race Manager', 0),
        ('Darren Ravenscroft', $alphaId, 'Team Principal & Driver', 1),
        ('Andy Tait', $alphaId, 'Driver', 1),
        ('Matt Casey', $alphaId, 'Driver', 1),
        ('Dave Parker', $alphaId, 'Driver', 1),
        ('Tomek Zet', $alphaId, 'Driver', 1),
        ('Ryan Welch', $bravoId, 'Driver', 1),
        ('Luke Gore', $bravoId, 'Driver', 1),
        ('James Eaton', $bravoId, 'Driver', 1),
        ('James Addison', $bravoId, 'Driver', 1),
        ('Daniel Lane', $bravoId, 'Driver', 1);
    ");

    // Insert default installments
    $db->exec("
        INSERT INTO installments (name, due_date, amount_per_driver, display_order) VALUES
        ('Deposit', '2026-01-01', 200.00, 1),
        ('Installment 1', '2026-02-01', 223.50, 2),
        ('Installment 2', '2026-03-01', 246.50, 3),
        ('Team Kit', '2026-03-01', 0.00, 4);
    ");

    // Insert settings
    $db->exec("
        INSERT OR REPLACE INTO settings (key, value) VALUES
        ('total_per_driver', '670'),
        ('team_kit_fee', '0');
    ");
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

// Admin functions
if (isset($_SESSION['admin'])) {
    // Add new installment
    if (isset($_POST['add_installment'])) {
        $name = $_POST['installment_name'];
        $dueDate = $_POST['installment_due_date'];
        $amountPerDriver = floatval($_POST['installment_amount']);
        $maxOrder = $db->querySingle("SELECT MAX(display_order) FROM installments") ?: 0;

        $stmt = $db->prepare("INSERT INTO installments (name, due_date, amount_per_driver, display_order) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $dueDate, SQLITE3_TEXT);
        $stmt->bindValue(3, $amountPerDriver, SQLITE3_FLOAT);
        $stmt->bindValue(4, $maxOrder + 1, SQLITE3_INTEGER);
        $stmt->execute();

        $success = "Installment '$name' added successfully";
    }

    // Update installment
    if (isset($_POST['update_installment'])) {
        $id = intval($_POST['installment_id']);
        $name = $_POST['installment_name'];
        $dueDate = $_POST['installment_due_date'];
        $amountPerDriver = floatval($_POST['installment_amount']);

        $stmt = $db->prepare("UPDATE installments SET name=?, due_date=?, amount_per_driver=? WHERE id=?");
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $dueDate, SQLITE3_TEXT);
        $stmt->bindValue(3, $amountPerDriver, SQLITE3_FLOAT);
        $stmt->bindValue(4, $id, SQLITE3_INTEGER);
        $stmt->execute();

        $success = "Installment updated successfully";
    }

    // Delete installment (only if no payments)
    if (isset($_POST['delete_installment'])) {
        $id = intval($_POST['installment_id']);

        // Check if any payments exist for this installment
        $paymentCount = $db->querySingle("SELECT COUNT(*) FROM payments WHERE installment_id=$id");
        if ($paymentCount > 0) {
            $error = "Cannot delete installment with existing payments. Remove payments first.";
        } else {
            $db->exec("DELETE FROM installments WHERE id=$id");
            $success = "Installment deleted successfully";
        }
    }

    // Log payment
    if (isset($_POST['log_payment'])) {
        $driverId = intval($_POST['driver_id']);
        $installmentId = intval($_POST['installment_id']);
        $amount = floatval($_POST['amount']);

        if ($driverId > 0 && $installmentId > 0 && $amount > 0) {
            $stmt = $db->prepare("INSERT INTO payments (driver_id, installment_id, amount) VALUES (?, ?, ?)");
            $stmt->bindValue(1, $driverId, SQLITE3_INTEGER);
            $stmt->bindValue(2, $installmentId, SQLITE3_INTEGER);
            $stmt->bindValue(3, $amount, SQLITE3_FLOAT);
            $stmt->execute();

            $driverName = $db->querySingle("SELECT name FROM drivers WHERE id=$driverId");
            $installmentName = $db->querySingle("SELECT name FROM installments WHERE id=$installmentId");
            $success = "Logged £" . number_format($amount, 2) . " for " . htmlspecialchars($driverName) . " (" . htmlspecialchars($installmentName) . ")";
        }
    }

    // Update team entry cost
    if (isset($_POST['update_team_costs'])) {
        $alphaId = $db->querySingle("SELECT id FROM teams WHERE name='Alpha'");
        $bravoId = $db->querySingle("SELECT id FROM teams WHERE name='Bravo'");

        $alphaCost = floatval($_POST['alpha_cost']);
        $bravoCost = floatval($_POST['bravo_cost']);

        $db->exec("UPDATE teams SET entry_cost=$alphaCost WHERE id=$alphaId");
        $db->exec("UPDATE teams SET entry_cost=$bravoCost WHERE id=$bravoId");

        $success = "Team costs updated successfully";
    }

    // Update settings
    if (isset($_POST['update_settings'])) {
        $totalPerDriver = floatval($_POST['total_per_driver']);
        $teamKitFee = floatval($_POST['team_kit_fee']);

        $db->exec("INSERT OR REPLACE INTO settings (key, value) VALUES ('total_per_driver', '$totalPerDriver')");
        $db->exec("INSERT OR REPLACE INTO settings (key, value) VALUES ('team_kit_fee', '$teamKitFee')");

        $success = "Settings updated successfully";
    }
}

// Get all data
$installments = [];
$result = $db->query("SELECT * FROM installments ORDER BY display_order ASC");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $installments[] = $row;
}

$teams = [];
$result = $db->query("SELECT * FROM teams ORDER BY name ASC");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $teams[] = $row;
}

$drivers = [];
$result = $db->query("SELECT d.*, t.name as team_name FROM drivers d JOIN teams t ON d.team_id = t.id ORDER BY t.name, d.name");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $drivers[] = $row;
}

// Get payment totals per driver per installment
function getPaymentTotal($db, $driverId, $installmentId) {
    $total = $db->querySingle("SELECT SUM(amount) FROM payments WHERE driver_id=$driverId AND installment_id=$installmentId");
    return $total ?: 0;
}

// Get total collected and outstanding
$totalCollected = $db->querySingle("SELECT SUM(amount) FROM payments") ?: 0;
$totalPerDriver = floatval($db->querySingle("SELECT value FROM settings WHERE key='total_per_driver'") ?: 670);
$teamKitFee = floatval($db->querySingle("SELECT value FROM settings WHERE key='team_kit_fee'") ?: 0);

$driverCount = $db->querySingle("SELECT COUNT(*) FROM drivers WHERE is_driver=1");
$totalExpected = $driverCount * ($totalPerDriver + $teamKitFee);
$totalOutstanding = $totalExpected - $totalCollected;

// Get team totals and remaining per installment
function getTeamInstallmentTotals($db, $teamName, $installmentId) {
    $result = $db->query("
        SELECT SUM(p.amount) as total
        FROM payments p
        JOIN drivers d ON p.driver_id = d.id
        JOIN teams t ON d.team_id = t.id
        WHERE t.name='$teamName' AND p.installment_id=$installmentId
    ");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return floatval($row['total'] ?: 0);
}

// Recent payments
$recentPayments = [];
if (isset($_SESSION['admin'])) {
    $result = $db->query("
        SELECT p.*, d.name as driver_name, i.name as installment_name
        FROM payments p
        JOIN drivers d ON p.driver_id = d.id
        JOIN installments i ON p.installment_id = i.id
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $recentPayments[] = $row;
    }
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
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
    }
    .admin-panel {
      background: #1a1a1a;
      border: 2px solid #8b241d;
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    input[type="number"], input[type="date"], input[type="password"], input[type="text"], select.form-control {
      background: #333;
      color: #fff;
      border: 1px solid #555;
    }
    input[type="number"]:focus, input[type="date"]:focus, input[type="password"]:focus, input[type="text"]:focus, select.form-control:focus {
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

    <!-- Payment Methods (Always at Top) -->
    <div class="info-box">
      <h5 style="color: #8b241d;">Payment Methods</h5>
      <ul class="mb-0">
        <li>PayPal: <a href="mailto:hello@dazrave.uk" style="color: #8b241d;">hello@dazrave.uk</a> or <a href="https://paypal.me/dazrave" target="_blank" style="color: #8b241d;">paypal.me/dazrave</a></li>
        <li>Bank: (ac) <strong>03039125</strong> / (s) <strong>04-00-04</strong> or <a href="https://monzo.me/darrenravenscroft" target="_blank" style="color: #8b241d;">monzo.me/darrenravenscroft</a></li>
      </ul>
    </div>

    <?php if (!isset($_SESSION['admin'])): ?>
      <!-- DRIVER VIEW -->

      <!-- Payment Schedule -->
      <div class="info-box">
        <h4>Payment Schedule</h4>
        <div class="deadline-grid">
          <?php foreach ($installments as $inst): ?>
            <?php if ($inst['amount_per_driver'] > 0): ?>
            <div class="deadline-item">
              <strong><?php echo htmlspecialchars($inst['name']); ?></strong>
              £<?php echo number_format($inst['amount_per_driver'], 2); ?><br>
              <small>Due: <?php echo $inst['due_date'] ? date('d M Y', strtotime($inst['due_date'])) : 'TBD'; ?></small>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
          <small>Total per driver: <strong>£<?php echo number_format($totalPerDriver, 2); ?></strong></small>
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
                <?php foreach ($installments as $inst): ?>
                  <th class="text-center"><?php echo htmlspecialchars($inst['name']); ?></th>
                <?php endforeach; ?>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drivers as $driver): ?>
                <?php if ($driver['team_name'] === 'Alpha' && $driver['is_driver']): ?>
                  <?php
                  $totalPaid = 0;
                  foreach ($installments as $inst) {
                      $totalPaid += getPaymentTotal($db, $driver['id'], $inst['id']);
                  }
                  $outstanding = ($totalPerDriver + $teamKitFee) - $totalPaid;
                  ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($driver['name']); ?></strong></td>
                    <?php foreach ($installments as $inst): ?>
                      <?php
                      $paid = getPaymentTotal($db, $driver['id'], $inst['id']);
                      $expected = $inst['amount_per_driver'];
                      ?>
                      <td class="text-center">
                        <?php if ($expected > 0 && $paid >= $expected): ?>
                          <span class="paid check">✓</span>
                        <?php elseif ($paid > 0): ?>
                          <span class="partial">£<?php echo number_format($paid, 0); ?></span>
                        <?php else: ?>
                          <span class="unpaid">-</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
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
                <?php foreach ($installments as $inst): ?>
                  <th class="text-center"><?php echo htmlspecialchars($inst['name']); ?></th>
                <?php endforeach; ?>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drivers as $driver): ?>
                <?php if ($driver['team_name'] === 'Bravo' && $driver['is_driver']): ?>
                  <?php
                  $totalPaid = 0;
                  foreach ($installments as $inst) {
                      $totalPaid += getPaymentTotal($db, $driver['id'], $inst['id']);
                  }
                  $outstanding = ($totalPerDriver + $teamKitFee) - $totalPaid;
                  ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($driver['name']); ?></strong></td>
                    <?php foreach ($installments as $inst): ?>
                      <?php
                      $paid = getPaymentTotal($db, $driver['id'], $inst['id']);
                      $expected = $inst['amount_per_driver'];
                      ?>
                      <td class="text-center">
                        <?php if ($expected > 0 && $paid >= $expected): ?>
                          <span class="paid check">✓</span>
                        <?php elseif ($paid > 0): ?>
                          <span class="partial">£<?php echo number_format($paid, 0); ?></span>
                        <?php else: ?>
                          <span class="unpaid">-</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
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
      foreach ($drivers as $driver) {
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
                <?php foreach ($installments as $inst): ?>
                  <th class="text-center"><?php echo htmlspecialchars($inst['name']); ?></th>
                <?php endforeach; ?>
                <th class="text-end">Total Paid</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drivers as $driver): ?>
                <?php if (!$driver['is_driver']): ?>
                  <?php
                  $totalPaid = 0;
                  foreach ($installments as $inst) {
                      $totalPaid += getPaymentTotal($db, $driver['id'], $inst['id']);
                  }
                  ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($driver['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($driver['role']); ?></td>
                    <?php foreach ($installments as $inst): ?>
                      <?php $paid = getPaymentTotal($db, $driver['id'], $inst['id']); ?>
                      <td class="text-center">
                        <?php if ($paid > 0): ?>
                          <span class="paid">£<?php echo number_format($paid, 0); ?></span>
                        <?php else: ?>
                          <span class="unpaid">-</span>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                    <td class="text-end"><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

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

      <!-- Team Costs & Installment Overview -->
      <div class="admin-panel">
        <h4>Team Entry Costs</h4>
        <form method="POST" class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label small">Alpha Team Entry Cost (£)</label>
            <input type="number" name="alpha_cost" class="form-control form-control-sm" step="0.01" value="<?php echo $teams[0]['entry_cost']; ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Bravo Team Entry Cost (£)</label>
            <input type="number" name="bravo_cost" class="form-control form-control-sm" step="0.01" value="<?php echo $teams[1]['entry_cost']; ?>" required>
          </div>
          <div class="col-12">
            <button type="submit" name="update_team_costs" class="btn btn-primary btn-sm">Update Team Costs</button>
          </div>
        </form>

        <h4 class="mt-4">Installment Summary</h4>
        <div class="table-responsive">
          <table class="table table-dark table-sm">
            <thead>
              <tr>
                <th>Installment</th>
                <th>Due Date</th>
                <th>Per Driver</th>
                <th class="text-center">Alpha Collected</th>
                <th class="text-center">Bravo Collected</th>
                <th class="text-center">Total Collected</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($installments as $inst): ?>
                <?php
                $alphaTotal = getTeamInstallmentTotals($db, 'Alpha', $inst['id']);
                $bravoTotal = getTeamInstallmentTotals($db, 'Bravo', $inst['id']);
                $instTotal = $alphaTotal + $bravoTotal;
                ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($inst['name']); ?></strong></td>
                  <td><?php echo $inst['due_date'] ? date('d M Y', strtotime($inst['due_date'])) : 'TBD'; ?></td>
                  <td>£<?php echo number_format($inst['amount_per_driver'], 2); ?></td>
                  <td class="text-center">£<?php echo number_format($alphaTotal, 2); ?></td>
                  <td class="text-center">£<?php echo number_format($bravoTotal, 2); ?></td>
                  <td class="text-center"><strong>£<?php echo number_format($instTotal, 2); ?></strong></td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-primary" onclick="editInstallment(<?php echo $inst['id']; ?>)">Edit</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this installment?');">
                      <input type="hidden" name="installment_id" value="<?php echo $inst['id']; ?>">
                      <button type="submit" name="delete_installment" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Add/Edit Installment Form -->
        <div class="mt-4 p-3 border border-secondary rounded">
          <h5 id="installmentFormTitle">Add New Installment</h5>
          <form method="POST" id="installmentForm" class="row g-3">
            <input type="hidden" name="installment_id" id="installment_id">
            <div class="col-md-4">
              <label class="form-label small">Name</label>
              <input type="text" name="installment_name" id="installment_name" class="form-control form-control-sm" placeholder="e.g. Installment 3" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Due Date</label>
              <input type="date" name="installment_due_date" id="installment_due_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Amount Per Driver (£)</label>
              <input type="number" name="installment_amount" id="installment_amount" class="form-control form-control-sm" step="0.01" min="0" required>
            </div>
            <div class="col-12">
              <button type="submit" name="add_installment" id="addInstallmentBtn" class="btn btn-success btn-sm">Add Installment</button>
              <button type="submit" name="update_installment" id="updateInstallmentBtn" class="btn btn-success btn-sm" style="display:none;">Update Installment</button>
              <button type="button" class="btn btn-secondary btn-sm" onclick="resetInstallmentForm()">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Log Payment -->
      <div class="admin-panel">
        <h4>Log Payment</h4>
        <form method="POST" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Driver</label>
            <select name="driver_id" class="form-control" required>
              <option value="">Select driver...</option>
              <?php foreach ($drivers as $driver): ?>
                <option value="<?php echo $driver['id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Installment</label>
            <select name="installment_id" class="form-control" required>
              <option value="">Select installment...</option>
              <?php foreach ($installments as $inst): ?>
                <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['name']); ?></option>
              <?php endforeach; ?>
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
        <?php if (count($recentPayments) > 0): ?>
          <div class="mt-4">
            <h5 class="h6">Recent Payments (Last 10)</h5>
            <div class="table-responsive">
              <table class="table table-dark table-sm">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Driver</th>
                    <th>Installment</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentPayments as $payment): ?>
                    <tr>
                      <td><?php echo date('d M Y H:i', strtotime($payment['created_at'])); ?></td>
                      <td><?php echo htmlspecialchars($payment['driver_name']); ?></td>
                      <td><?php echo htmlspecialchars($payment['installment_name']); ?></td>
                      <td class="text-end">£<?php echo number_format($payment['amount'], 2); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Driver Payments Overview -->
      <div class="admin-panel">
        <h4>Driver Payments Overview</h4>
        <div class="table-responsive">
          <table class="table table-dark table-hover table-sm">
            <thead>
              <tr>
                <th>Driver</th>
                <th>Team</th>
                <?php foreach ($installments as $inst): ?>
                  <th class="text-center"><?php echo htmlspecialchars($inst['name']); ?></th>
                <?php endforeach; ?>
                <th class="text-end">Total</th>
                <th class="text-end">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($drivers as $driver): ?>
                <?php
                $totalPaid = 0;
                foreach ($installments as $inst) {
                    $totalPaid += getPaymentTotal($db, $driver['id'], $inst['id']);
                }
                $expected = $driver['is_driver'] ? ($totalPerDriver + $teamKitFee) : 0;
                $outstanding = $expected - $totalPaid;
                ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($driver['name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($driver['team_name']); ?></td>
                  <?php foreach ($installments as $inst): ?>
                    <?php $paid = getPaymentTotal($db, $driver['id'], $inst['id']); ?>
                    <td class="text-center">£<?php echo number_format($paid, 2); ?></td>
                  <?php endforeach; ?>
                  <td class="text-end"><strong>£<?php echo number_format($totalPaid, 2); ?></strong></td>
                  <td class="text-end <?php echo $outstanding <= 0 ? 'paid' : 'unpaid'; ?>">
                    <?php echo $driver['is_driver'] ? '£' . number_format($outstanding, 2) : '-'; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <script>
        const installments = <?php echo json_encode($installments); ?>;

        function editInstallment(id) {
          const inst = installments.find(i => i.id == id);
          if (!inst) return;

          document.getElementById('installmentFormTitle').textContent = 'Edit Installment';
          document.getElementById('installment_id').value = inst.id;
          document.getElementById('installment_name').value = inst.name;
          document.getElementById('installment_due_date').value = inst.due_date || '';
          document.getElementById('installment_amount').value = inst.amount_per_driver;
          document.getElementById('addInstallmentBtn').style.display = 'none';
          document.getElementById('updateInstallmentBtn').style.display = 'inline-block';
          document.getElementById('installmentForm').scrollIntoView({ behavior: 'smooth' });
        }

        function resetInstallmentForm() {
          document.getElementById('installmentFormTitle').textContent = 'Add New Installment';
          document.getElementById('installmentForm').reset();
          document.getElementById('addInstallmentBtn').style.display = 'inline-block';
          document.getElementById('updateInstallmentBtn').style.display = 'none';
        }
      </script>
    <?php endif; ?>
  </div>

  <footer style="background: #000; color: #ccc; text-align: center; padding: 20px 0; margin-top: 3rem;">
    &copy; Raven Motorsport 2026. <a href="/" style="color: #ccc; text-decoration: none;">Back to Home</a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
