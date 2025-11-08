<?php
include('db_connect.php');

// ---------- Config ----------
$LOW_STOCK_THRESHOLD = 10;   // quantity <= this => low stock
$NEAR_EXPIRY_DAYS = 30;      // within next N days => near expiry
// ----------------------------

// --- Add Medicine ---
if (isset($_POST['add_medicine'])) {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $type = $conn->real_escape_string($_POST['type'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
    $expiry = $_POST['expiry_date'] ?? null;

    if ($name !== '' && $type !== '') {
        $expiry_sql = $expiry ? "'" . $conn->real_escape_string($expiry) . "'" : "NULL";
        $conn->query("INSERT INTO medicines (name,type,quantity,price,expiry_date) VALUES ('$name','$type',$quantity,$price,$expiry_sql)");
    }
    header("Location: medicines.php");
    exit;
}

// --- Delete Medicine ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM medicines WHERE medicine_id=$id");
    header("Location: medicines.php");
    exit;
}

// --- Alerts & Data Fetch ---
$today = date('Y-m-d');
$near_expiry_date = date('Y-m-d', strtotime("+$NEAR_EXPIRY_DAYS days"));

// Alerts query: expired, near-expiry, low-stock
$alerts = $conn->query("
    SELECT medicine_id, name, quantity, expiry_date,
      (expiry_date IS NOT NULL AND expiry_date <= '$today') AS is_expired,
      (expiry_date IS NOT NULL AND expiry_date > '$today' AND expiry_date <= '$near_expiry_date') AS is_near_expiry,
      (quantity <= $LOW_STOCK_THRESHOLD) AS is_low_stock
    FROM medicines
    WHERE (expiry_date IS NOT NULL AND expiry_date <= '$near_expiry_date')
       OR quantity <= $LOW_STOCK_THRESHOLD
    ORDER BY is_expired DESC, is_near_expiry DESC, is_low_stock DESC, name ASC
") or die($conn->error);

// Fetch all medicines for table
$result = $conn->query("SELECT * FROM medicines ORDER BY medicine_id DESC") or die($conn->error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Medicines</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<h1 class="text-3xl font-bold text-center text-blue-600 mb-6">💊 Manage Medicines</h1>

<!-- Alerts -->
<div class="max-w-6xl mx-auto mb-6">
  <?php if ($alerts && $alerts->num_rows > 0): ?>
    <div class="bg-white p-4 rounded shadow-sm">
      <h2 class="text-xl font-semibold mb-2">⚠ Medicine Alerts</h2>
      <ul class="space-y-2">
        <?php while($a = $alerts->fetch_assoc()):
            $msgs = [];
            if ($a['is_expired']) $msgs[] = "<span class='font-bold text-red-700'>Expired</span>";
            if ($a['is_near_expiry']) $msgs[] = "<span class='font-semibold text-orange-600'>Near expiry</span>";
            if ($a['is_low_stock']) $msgs[] = "<span class='font-semibold text-yellow-700'>Low stock</span>";
        ?>
          <li class="border-l-4 pl-3 py-2 <?=
                $a['is_expired'] ? 'bg-red-50 border-red-600' :
                ($a['is_near_expiry'] ? 'bg-orange-50 border-orange-500' : 'bg-yellow-50 border-yellow-500')
              ?>">
            <div class="flex justify-between items-start">
              <div>
                <span class="font-medium"><?= htmlspecialchars($a['name']) ?></span>
                <span class="text-sm text-gray-600"> — Qty: <?= (int)$a['quantity'] ?>, Exp: <?= $a['expiry_date'] ?? 'N/A' ?></span>
              </div>
              <div class="text-sm"><?= implode(' • ', $msgs) ?></div>
            </div>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  <?php else: ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded mb-4">
      <strong class="text-green-700">All medicines OK — no low stock or near expiry within <?= $NEAR_EXPIRY_DAYS ?> days.</strong>
    </div>
  <?php endif; ?>
</div>

<!-- Add Medicine Form -->
<form method="POST" class="bg-white p-6 rounded-2xl shadow-md max-w-md mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Add Medicine</h2>
    <input type="text" name="name" placeholder="Medicine Name" required class="w-full p-2 mb-3 border rounded">
    <input type="text" name="type" placeholder="Type (Tablet/Syrup/etc.)" required class="w-full p-2 mb-3 border rounded">
    <input type="number" name="quantity" placeholder="Quantity" required class="w-full p-2 mb-3 border rounded">
    <input type="number" step="0.01" name="price" placeholder="Price per unit" required class="w-full p-2 mb-3 border rounded">
    <input type="date" name="expiry_date" placeholder="Expiry Date" class="w-full p-2 mb-3 border rounded">

    <button type="submit" name="add_medicine" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Add Medicine
    </button>
</form>

<!-- Medicine List -->
<div class="bg-white p-6 rounded-2xl shadow-md max-w-6xl mx-auto">
    <h2 class="text-xl font-semibold mb-4">Medicine Records</h2>
    <table class="w-full border-collapse text-center">
        <thead>
        <tr class="bg-blue-100">
            <th class="border p-2">ID</th>
            <th class="border p-2">Name</th>
            <th class="border p-2">Type</th>
            <th class="border p-2">Quantity</th>
            <th class="border p-2">Price</th>
            <th class="border p-2">Expiry Date</th>
            <th class="border p-2">Status</th>
            <th class="border p-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()):
            // Determine status
            $statusMsg = 'OK';
            $row_expired = ($row['expiry_date'] !== null && $row['expiry_date'] <= $today);
            $row_near = ($row['expiry_date'] !== null && $row['expiry_date'] > $today && $row['expiry_date'] <= $near_expiry_date);
            $row_low = ($row['quantity'] <= $LOW_STOCK_THRESHOLD);

            $row_class = '';
            if ($row_expired) $row_class = 'bg-red-50';
            elseif ($row_near || $row_low) $row_class = 'bg-yellow-50';
        ?>
        <tr class="<?= $row_class ?>">
            <td class="border p-2"><?= (int)$row['medicine_id'] ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['name']) ?></td>
            <td class="border p-2"><?= htmlspecialchars($row['type']) ?></td>
            <td class="border p-2"><?= (int)$row['quantity'] ?></td>
            <td class="border p-2"><?= number_format((float)$row['price'],2) ?></td>
            <td class="border p-2"><?= $row['expiry_date'] ?? 'N/A' ?></td>
            <td class="border p-2">
                <?php
                  if ($row_expired) echo "<span class='text-red-700 font-semibold'>Expired</span>";
                  elseif ($row_near) echo "<span class='text-orange-600 font-semibold'>Near expiry</span>";
                  elseif ($row_low) echo "<span class='text-yellow-700 font-semibold'>Low stock</span>";
                  else echo "<span class='text-green-700 font-medium'>OK</span>";
                ?>
            </td>
            <td class="border p-2">
                <a href="?delete=<?= (int)$row['medicine_id'] ?>" class="text-red-600 font-semibold hover:underline" onclick="return confirm('Delete this medicine?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="text-center mt-6">
    <a href="index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
</div>

</body>
</html>
