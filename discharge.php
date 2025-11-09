<?php
include('db_connect.php');

// ---------- Require FPDF ----------
if (!file_exists(__DIR__ . '/fpdf.php')) {
    die("FPDF library not found. Download fpdf.php from http://www.fpdf.org/ and place it in the same folder as this file.");
}
require_once __DIR__ . '/fpdf.php';

// ---------- Ensure discharge_summaries table + columns exist ----------
$conn->query("
CREATE TABLE IF NOT EXISTS discharge_summaries (
  summary_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NULL,
  patient_name VARCHAR(255) NULL,
  discharge_date DATE,
  total_bill DECIMAL(10,2) DEFAULT 0,
  medicines TEXT NULL,
  bed_charge DECIMAL(10,2) DEFAULT 0,
  doctor_fee DECIMAL(10,2) DEFAULT 0,
  diagnosis TEXT NULL,
  treatment TEXT NULL
)");

// extra safety to ensure patient_name exists
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM discharge_summaries");
if ($res) {
    while ($c = $res->fetch_assoc()) $cols[] = $c['Field'];
}
if (!in_array('patient_name', $cols)) {
    $conn->query("ALTER TABLE discharge_summaries ADD COLUMN patient_name VARCHAR(255) NULL");
}

// Fetch patients & medicines for the form
$patients = $conn->query("SELECT patient_id, name, bed_id FROM patients ORDER BY name");
$medicines_res = $conn->query("SELECT medicine_id, name, price, quantity, expiry_date FROM medicines ORDER BY name");

// ---------- Handle Discharge POST (calculate + insert + PDF) ----------
if (isset($_POST['discharge'])) {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $bed_charge = isset($_POST['bed_charge']) ? (float)$_POST['bed_charge'] : 0.0;
    $doctor_fee = isset($_POST['doctor_fee']) ? (float)$_POST['doctor_fee'] : 0.0;
    $discharge_date = $conn->real_escape_string($_POST['discharge_date'] ?? date('Y-m-d'));
    $diagnosis = $conn->real_escape_string($_POST['diagnosis'] ?? '');
    $treatment = $conn->real_escape_string($_POST['treatment'] ?? '');

    // fetch patient snapshot BEFORE deletion
    $pinfo = $conn->query("SELECT * FROM patients WHERE patient_id=$patient_id")->fetch_assoc();
    if (!$pinfo) die("Patient not found or already discharged.");

    $patient_name = $pinfo['name'] ?? '';
    $patient_age = isset($pinfo['age']) ? (int)$pinfo['age'] : '';
    $patient_gender = $pinfo['gender'] ?? '';
    $patient_contact = $pinfo['contact'] ?? '';
    $patient_address = $pinfo['address'] ?? '';
    $patient_disease = $pinfo['disease'] ?? '';
    $patient_severity = $pinfo['severity'] ?? '';
    $patient_bed_id = !empty($pinfo['bed_id']) ? (int)$pinfo['bed_id'] : null;

    // process medicines
    $med_qty = $_POST['med_qty'] ?? [];
    $med_text_parts = [];
    $total_medicine_cost = 0.0;
    $med_items_for_pdf = [];

    if (!empty($med_qty) && is_array($med_qty)) {
        foreach ($med_qty as $mid => $qty_raw) {
            $mid = (int)$mid;
            $qty = (int)$qty_raw;
            if ($mid > 0 && $qty > 0) {
                $mrow = $conn->query("SELECT name, price, quantity FROM medicines WHERE medicine_id=$mid")->fetch_assoc();
                if ($mrow) {
                    $price = (float)$mrow['price'];
                    $avail = (int)$mrow['quantity'];
                    $used_qty = min($qty, $avail);
                    if ($used_qty <= 0) continue;
                    $cost = $price * $used_qty;
                    $total_medicine_cost += $cost;
                    $med_text_parts[] = $conn->real_escape_string($mrow['name']) . "-" . $used_qty;
                    $med_items_for_pdf[] = [
                        'name' => $mrow['name'],
                        'qty'  => $used_qty,
                        'unit' => number_format($price,2),
                        'cost' => number_format($cost,2)
                    ];
                    // reduce stock
                    $conn->query("UPDATE medicines SET quantity = quantity - $used_qty WHERE medicine_id=$mid");
                }
            }
        }
    }

    $medicines_text = !empty($med_text_parts) ? implode(",", $med_text_parts) : null;
    $total_bill = $total_medicine_cost + $bed_charge + $doctor_fee;

    // insert discharge record
    $ins_sql = "INSERT INTO discharge_summaries
        (patient_id, patient_name, discharge_date, total_bill, medicines, bed_charge, doctor_fee, diagnosis, treatment)
        VALUES (
            ".($patient_id ? $patient_id : "NULL").",
            '".$conn->real_escape_string($patient_name)."',
            '".$conn->real_escape_string($discharge_date)."',
            ".(float)$total_bill.",
            ".($medicines_text ? "'".$conn->real_escape_string($medicines_text)."'" : "NULL").",
            ".(float)$bed_charge.",
            ".(float)$doctor_fee.",
            '".$diagnosis."',
            '".$treatment."'
        )";
    if (!$conn->query($ins_sql)) {
        die("Insert error: " . $conn->error);
    }

    // free bed
    if ($patient_bed_id) {
        $conn->query("UPDATE beds SET status='Available', patient_id=NULL WHERE bed_id=$patient_bed_id");
    }

    // delete patient record
    $conn->query("DELETE FROM patients WHERE patient_id=$patient_id");

    // ---------- Generate PDF with FPDF ----------
    class PDF extends FPDF {
        function Header(){
            $this->SetFont('Arial','B',14);
            $this->Cell(0,8,'Hospital Discharge Summary',0,1,'C');
            $this->Ln(2);
        }
        function Footer(){
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Generated on '.date('Y-m-d H:i:s'),0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',11);

    // patient block
    $pdf->Cell(40,7,'Patient Name:'); $pdf->Cell(0,7,$patient_name,0,1);
    $pdf->Cell(40,7,'Age/Gender:'); $pdf->Cell(0,7,($patient_age? $patient_age : '-') . ' / ' . ($patient_gender?: '-'),0,1);
    $pdf->Cell(40,7,'Contact:'); $pdf->Cell(0,7,$patient_contact ?: '-',0,1);
    $pdf->Cell(40,7,'Address:'); $pdf->MultiCell(0,7,$patient_address ?: '-',0,1);
    $pdf->Cell(40,7,'Diagnosis:'); $pdf->MultiCell(0,7,$diagnosis ?: '-',0,1);
    $pdf->Cell(40,7,'Treatment Notes:'); $pdf->MultiCell(0,7,$treatment ?: '-',0,1);
    $pdf->Ln(3);

    // bed
    if ($patient_bed_id) {
        $b = $conn->query("SELECT bed_number, type FROM beds WHERE bed_id=$patient_bed_id")->fetch_assoc();
        if ($b) $pdf->Cell(40,7,'Bed Assigned:'); $pdf->Cell(0,7,$b['bed_number'].' ('.$b['type'].')',0,1);
    } else {
        $pdf->Cell(40,7,'Bed Assigned:'); $pdf->Cell(0,7,'- (No bed assigned)',0,1);
    }
    $pdf->Ln(3);

    // medicines table
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(80,7,'Medicine',1,0);
    $pdf->Cell(30,7,'Qty',1,0,'C');
    $pdf->Cell(35,7,'Unit Price',1,0,'R');
    $pdf->Cell(35,7,'Cost',1,1,'R');
    $pdf->SetFont('Arial','',11);
    if (!empty($med_items_for_pdf)) {
        foreach ($med_items_for_pdf as $mi) {
            $pdf->Cell(80,7,$mi['name'],1,0);
            $pdf->Cell(30,7,$mi['qty'],1,0,'C');
            $pdf->Cell(35,7,$mi['unit'],1,0,'R');
            $pdf->Cell(35,7,$mi['cost'],1,1,'R');
        }
    } else {
        $pdf->Cell(180,7,'No medicines used',1,1,'C');
    }

    $pdf->Ln(4);
    // charges
    $pdf->Cell(130,7,'',0,0);
    $pdf->Cell(35,7,'Bed Charge',0,0,'R');
    $pdf->Cell(35,7,number_format((float)$bed_charge,2),1,1,'R');

    $pdf->Cell(130,7,'',0,0);
    $pdf->Cell(35,7,'Doctor Fee',0,0,'R');
    $pdf->Cell(35,7,number_format((float)$doctor_fee,2),1,1,'R');

    $pdf->Cell(130,7,'',0,0);
    $pdf->Cell(35,7,'Medicine Total',0,0,'R');
    $pdf->Cell(35,7,number_format((float)$total_medicine_cost,2),1,1,'R');

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(130,9,'',0,0);
    $pdf->Cell(35,9,'Total',0,0,'R');
    $pdf->Cell(35,9,number_format((float)$total_bill,2),1,1,'R');

    $pdf->Ln(6);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Discharge Date: '.$discharge_date,0,1);

    // output PDF
    $safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $patient_name);
    $filename = "discharge_{$safe_name}_".date('Ymd_His').".pdf";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $pdf->Output('D', $filename);
    exit;
}

// ---------- If not posted, show form ----------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Discharge & Billing (PDF)</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">📝 Discharge & Billing (PDF)</h1>

  <div class="bg-white p-6 rounded-2xl shadow-md max-w-4xl mx-auto mb-8">
    <h2 class="text-xl font-semibold mb-4">Discharge Patient</h2>
    <form method="POST" class="space-y-3">
      <div>
        <label class="block mb-1 font-medium">Select Patient (admitted)</label>
        <select name="patient_id" required class="w-full p-2 border rounded">
          <option value="">-- Select Patient --</option>
          <?php
          // re-fetch patients fresh
          $patients = $conn->query("SELECT patient_id, name, bed_id FROM patients ORDER BY name");
          while ($p = $patients->fetch_assoc()):
              $has_bed = !empty($p['bed_id']);
              $bed_info = '';
              if ($has_bed) {
                  $b = $conn->query("SELECT bed_number, type FROM beds WHERE bed_id=".(int)$p['bed_id'])->fetch_assoc();
                  if ($b) $bed_info = " — Bed: ".$b['bed_number']." (".$b['type'].")";
              } else {
                  $bed_info = " — (No bed assigned)";
              }
          ?>
            <option value="<?= (int)$p['patient_id'] ?>" <?= $has_bed ? '' : 'disabled style="color:gray;"' ?>>
              <?= htmlspecialchars($p['name']) . $bed_info ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block mb-1 font-medium">Diagnosis</label>
        <textarea name="diagnosis" required class="w-full p-2 border rounded"></textarea>
      </div>

      <div>
        <label class="block mb-1 font-medium">Treatment / Notes</label>
        <textarea name="treatment" required class="w-full p-2 border rounded"></textarea>
      </div>

      <div>
        <label class="block mb-1 font-medium">Discharge Date</label>
        <input type="date" name="discharge_date" value="<?= date('Y-m-d') ?>" required class="w-full p-2 border rounded">
      </div>

      <div>
        <label class="block mb-1 font-medium">Medicines used (enter qty > 0 to include)</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-y-auto border p-2 rounded">
          <?php
          $medicines_res = $conn->query("SELECT medicine_id, name, price, quantity, expiry_date FROM medicines ORDER BY name");
          while ($m = $medicines_res->fetch_assoc()): ?>
            <div class="flex items-center gap-2">
              <div class="flex-1">
                <div class="font-medium"><?= htmlspecialchars($m['name']) ?></div>
                <div class="text-sm text-gray-600">Price: <?= number_format((float)$m['price'],2) ?> — Avail: <?= (int)$m['quantity'] ?> — Exp: <?= $m['expiry_date'] ?? 'N/A' ?></div>
              </div>
              <div class="w-28">
                <input type="number" min="0" name="med_qty[<?= (int)$m['medicine_id'] ?>]" value="0" class="w-full p-1 border rounded">
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="number" step="0.01" name="bed_charge" placeholder="Bed Charge" class="p-2 border rounded" required>
        <input type="number" step="0.01" name="doctor_fee" placeholder="Doctor Fee" class="p-2 border rounded" required>
        <div class="p-2 text-sm text-gray-600">After submitting, a PDF bill will download automatically.</div>
      </div>

      <div>
        <button type="submit" name="discharge" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Discharge Patient & Download PDF</button>
      </div>
    </form>
  </div>

  <div class="text-center mt-6">
    <a href="index.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
