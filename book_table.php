<?php
// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "food";

// Create connection (mysqli)
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Helper to render a styled page (success or error)
function render_result_page($title, $message, $is_success = true, $extra = '') {
  $bg = $is_success ? '#f0fff4' : '#fff5f5';
  $border = $is_success ? '#34d399' : '#f87171';
  $accent = $is_success ? '#10b981' : '#ef4444';
  $btnText = $is_success ? 'Back to Home' : 'Try Again';
  echo <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{$title}</title>
  <style>
    :root{font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;}
    body{
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background: linear-gradient(180deg, #fafafa 0%, #f6f7fb 100%);
      padding:20px;
    }
    .card{
      width:100%;
      max-width:520px;
      background:{$bg};
      border:1px solid {$border};
      border-radius:16px;
      box-shadow: 0 10px 30px rgba(20,20,50,0.06);
      padding:32px;
      text-align:center;
    }
    .icon{
      width:84px;
      height:84px;
      margin:0 auto 18px auto;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius:999px;
      background:rgba(0,0,0,0.03);
    }
    h1{margin:0 0 6px 0;font-size:22px;color:#0f172a;}
    p{margin:0 0 18px 0;color:#334155;line-height:1.4;}
    .accent{
      display:inline-block;
      background:linear-gradient(90deg, rgba(255,255,255,0.05), rgba(255,255,255,0));
      padding:10px 18px;
      border-radius:999px;
      font-weight:600;
      color:{$accent};
      box-shadow: inset 0 -1px 0 rgba(0,0,0,0.02);
    }
    .meta{
      margin-top:12px;
      color:#475569;
      font-size:14px;
    }
    .btn{
      display:inline-block;
      margin-top:20px;
      padding:10px 18px;
      border-radius:10px;
      text-decoration:none;
      font-weight:600;
      border:1px solid rgba(15,23,42,0.06);
      background:white;
      color:#0f172a;
      box-shadow: 0 6px 18px rgba(15,23,42,0.06);
    }
    .small{
      font-size:13px;color:#64748b;margin-top:14px;
    }
    @media (max-width:520px){ .card{padding:22px;border-radius:12px;} h1{font-size:18px;} }
  </style>
</head>
<body>
  <div class="card" role="status" aria-live="polite">
    <div class="icon">
      <!-- Success or Error SVG -->
HTML;
  if ($is_success) {
    echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="1.5" fill="rgba(16,185,129,0.08)"/>
      <path d="M7.5 12.5l2.5 2.5L16.5 9.5" stroke="#059669" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>';
  } else {
    echo '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="12" cy="12" r="10" stroke="#ef4444" stroke-width="1.5" fill="rgba(239,68,68,0.06)"/>
      <path d="M9 9l6 6M15 9l-6 6" stroke="#dc2626" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>';
  }

  // Continue HTML
  $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $safeMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
  $safeExtra = $extra ? "<div class=\"meta\">{$extra}</div>" : "";
  echo <<<HTML
    </div>
    <h1>{$safeTitle}</h1>
    <p>{$safeMsg}</p>
    {$safeExtra}
    <a class="btn" href="index.html">{$btnText}</a>
    <div class="small">You will also receive a confirmation from our side shortly.</div>
  </div>
</body>
</html>
HTML;
  exit; // stop further execution after rendering
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // sanitize incoming raw values minimally (we use prepared statements below)
  $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
  $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
  $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
  $persons = isset($_POST['persons']) ? (int)$_POST['persons'] : 0;
  $date    = isset($_POST['date']) ? $_POST['date'] : '';

  // Basic validation
  if ($name === '' || $phone === '' || $email === '' || $persons <= 0 || $date === '') {
    render_result_page('Missing information', 'Please fill all required fields and submit again.', false);
  }

  // Prepared statement to insert safely
  $stmt = $conn->prepare("INSERT INTO bookings (customer_name, phone, email, persons, booking_date) VALUES (?, ?, ?, ?, ?)");
  if (!$stmt) {
    render_result_page('Server error', 'Failed to prepare booking. Please try again later.', false, 'Error detail: prepare failed');
  }
  $stmt->bind_param('sssds', $name, $phone, $email, $persons, $date); 
  // Note: 'd' for persons is fine if it's numeric; use 'i' if it's integer - mysqli accepts 'i' as integer:
  // If you prefer, change bind_param to 'sssis' and cast $persons to int.

  if ($stmt->execute()) {
    // Render styled success
    $extra = "Name: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " • Date: " . htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
    render_result_page('Booking successful!', 'Thank you — your table is reserved. We will contact you for confirmation shortly.', true, $extra);
  } else {
    // Render styled error with safe message
    render_result_page('Could not save booking', 'There was a problem saving your booking. Please try again or contact us.', false);
  }

  $stmt->close();
}

$conn->close();
?>
