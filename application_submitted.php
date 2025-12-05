<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application Submitted</title>
  <style>
    body {
      background: #1f1c2c;
      color: white;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .message-box {
      background: #2c2742;
      padding: 30px 50px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }
    .message-box h2 {
      color: #4CAF50;
      margin-bottom: 15px;
    }
    .message-box button {
      background-color: #4CAF50;
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    .message-box button:hover {
      background-color: #3e8e41;
    }
  </style>
</head>
<body>
  <div class="message-box">
    <h2>✅ Application Submitted</h2>
    <p>Your volunteer application has been successfully submitted.</p>
    <button onclick="window.location.href='apply_volunteer.php'">OK</button>
  </div>
</body>
</html>
