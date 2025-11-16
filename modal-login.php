<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="proses-login.php">
      <div class="modal-content" style="border-radius: var(--radius-md); box-shadow: var(--shadow-md);">

        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="loginModalLabel" style="color: var(--app-blue);">Login Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="modal_username" class="fw-bold">Username</label>
            <input type="text" name="username" id="modal_username" class="form-control" style="border-radius: var(--radius-md);" required>
          </div>

          <div class="mb-3">
            <label for="modal_password" class="fw-bold">Password</label>
            <input type="password" name="password" id="modal_password" class="form-control" style="border-radius: var(--radius-md);" required>
          </div>

          </div>

        <div class="modal-footer justify-content-center border-0 pt-0">
          <button type="submit" class="btn btn-primary w-100 py-2">
            Login
          </button>
        </div>
      </div>
    </form>
  </div>
</div>