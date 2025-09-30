<?php
// Show errors (for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname     = "food";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
$conn->set_charset("utf8");

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = $_POST['username'];
  $pass = $_POST['password'];

  // Hash password before saving
  $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

  // Insert query
  $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  $stmt->bind_param("ss", $user, $hashedPass);

  if ($stmt->execute()) {
    // ✅ Success page with design
    echo <<<HTML
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Success</title>
      <style>
        body {
          margin: 0;
          height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: linear-gradient(135deg,#f0fff4,#ecfdf5);
          font-family: Arial, sans-serif;
        }
        .card {
          background: white;
          border-radius: 16px;
          box-shadow: 0 8px 20px rgba(0,0,0,0.08);
          padding: 30px;
          text-align: center;
          max-width: 400px;
          width: 90%;
        }
        .icon {
          width: 70px;
          height: 70px;
          margin: 0 auto 20px;
          border-radius: 50%;
          background: #d1fae5;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .icon svg {
          width: 40px;
          height: 40px;
          stroke: #10b981;
        }
        h2 {
          margin: 10px 0;
          color: #065f46;
        }
        p {
          color: #374151;
          margin-bottom: 20px;
        }
        a {
          display: inline-block;
          padding: 10px 20px;
          background: #10b981;
          color: white;
          border-radius: 8px;
          text-decoration: none;
          font-weight: bold;
          transition: 0.2s;
        }
        a:hover {
          background: #059669;
        }
      </style>
    </head>
    <body>
      <div class="card">
        <div class="icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h2>User Saved Successfully!</h2>
        <p>Your account has been stored securely in our system.</p>
        <a href="index.html">Back to Home</a>
      </div>
    </body>
    </html>
    HTML;
  } else {
    echo "<h2 style='color:red;'>❌ Error: " . $stmt->error . "</h2>";
  }

  $stmt->close();
}
$conn->close();
?>
