<?php
// Simple admin login page (posts to api/login.php)
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <style>
    body {
        font-family:Arial;
        padding:40px
    }
    form {
        max-width:320px;
        margin:auto
    }
    input {
        display:block;
        width:100%;
        padding:8px;
        margin:8px 0
    }
</style>
</head>

<body>
  <h2>Admin Login</h2>
  <form id="loginForm">
    <input type="email" id="email" placeholder="Email" required>
    <input type="password" id="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
  <p id="status"></p>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const data = { 
        email: document.getElementById('email').value.trim(), 
        password: document.getElementById('password').value
      };
      try {
        const res = await fetch('/Medicus/api/login.php', { 
            method:'POST',
            credentials: 'same-origin',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data) 
        });
        const json = await res.json();
        if (res.ok) {
          // After successful admin login, send to the full admin dashboard
          window.location.href = '/Medicus/admin/dashboard';
        } else {
          document.getElementById('status').innerText = json.message || 'Login failed';
        }
      } catch (err) {
        document.getElementById('status').innerText = 'Server error';
      }
    });
  </script>
</body>
</html>
