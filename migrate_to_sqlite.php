<?php
/**
 * One-time migration script to import payment_data.json into SQLite database
 * Run this once on the server to migrate existing payment data
 */

$jsonFile = __DIR__ . '/payment_data.json';
$dbFile = __DIR__ . '/payment_tracker.db';

if (!file_exists($jsonFile)) {
    die("Error: payment_data.json not found\n");
}

// Read JSON data
$jsonData = json_decode(file_get_contents($jsonFile), true);

// Open database
$db = new SQLite3($dbFile);

echo "Starting migration...\n";

// Get installment IDs (assuming the default installments are already created)
$depositId = $db->querySingle("SELECT id FROM installments WHERE name='Deposit'");
$inst1Id = $db->querySingle("SELECT id FROM installments WHERE name='Installment 1'");
$inst2Id = $db->querySingle("SELECT id FROM installments WHERE name='Installment 2'");
$teamKitId = $db->querySingle("SELECT id FROM installments WHERE name='Team Kit'");

if (!$depositId || !$inst1Id || !$inst2Id || !$teamKitId) {
    die("Error: Default installments not found in database. Please access payments.php first to initialize the database.\n");
}

echo "Found installments: Deposit=$depositId, Installment 1=$inst1Id, Installment 2=$inst2Id, Team Kit=$teamKitId\n";

// Special update: Set James Eaton's deposit to 200 (user requested)
if (isset($jsonData['drivers']['James Eaton'])) {
    $jsonData['drivers']['James Eaton']['deposit'] = 200;
    echo "Updated James Eaton's deposit to £200\n";
}

// Migrate payments for each driver
$paymentCount = 0;
foreach ($jsonData['drivers'] as $name => $driverData) {
    // Get driver ID from database
    $stmt = $db->prepare("SELECT id FROM drivers WHERE name = ?");
    $stmt->bindValue(1, $name, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if (!$row) {
        echo "Warning: Driver '$name' not found in database, skipping...\n";
        continue;
    }

    $driverId = $row['id'];

    // Insert deposit payment if > 0
    if (isset($driverData['deposit']) && $driverData['deposit'] > 0) {
        $stmt = $db->prepare("INSERT INTO payments (driver_id, installment_id, amount) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $driverId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $depositId, SQLITE3_INTEGER);
        $stmt->bindValue(3, $driverData['deposit'], SQLITE3_FLOAT);
        $stmt->execute();
        echo "  - $name: Deposit £{$driverData['deposit']}\n";
        $paymentCount++;
    }

    // Insert installment 1 payment if > 0
    if (isset($driverData['installment1']) && $driverData['installment1'] > 0) {
        $stmt = $db->prepare("INSERT INTO payments (driver_id, installment_id, amount) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $driverId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $inst1Id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $driverData['installment1'], SQLITE3_FLOAT);
        $stmt->execute();
        echo "  - $name: Installment 1 £{$driverData['installment1']}\n";
        $paymentCount++;
    }

    // Insert installment 2 payment if > 0
    if (isset($driverData['installment2']) && $driverData['installment2'] > 0) {
        $stmt = $db->prepare("INSERT INTO payments (driver_id, installment_id, amount) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $driverId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $inst2Id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $driverData['installment2'], SQLITE3_FLOAT);
        $stmt->execute();
        echo "  - $name: Installment 2 £{$driverData['installment2']}\n";
        $paymentCount++;
    }

    // Insert team kit payment if > 0
    if (isset($driverData['team_kit']) && $driverData['team_kit'] > 0) {
        $stmt = $db->prepare("INSERT INTO payments (driver_id, installment_id, amount) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $driverId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $teamKitId, SQLITE3_INTEGER);
        $stmt->bindValue(3, $driverData['team_kit'], SQLITE3_FLOAT);
        $stmt->execute();
        echo "  - $name: Team Kit £{$driverData['team_kit']}\n";
        $paymentCount++;
    }
}

echo "\nMigration complete! Imported $paymentCount payments.\n";
echo "You can now delete payment_data.json and this migration script.\n";

$db->close();
?>
