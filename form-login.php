<div class="d-flex justify-content-center align-items-center" style="height: 80vh;">
  <div class="card shadow" style="width: 350px;">
    <div class="card-header text-center">
      <h5>🔐 Login Akun</h5>
    </div>
    <div class="card-body">
      <form method="POST" action="proses-login.php">
        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>
