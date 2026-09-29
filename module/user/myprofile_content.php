<?php
// Solitario Solutions
include __DIR__ . '/style.php';
$myPhoto = (!empty($me->PHOTO)) ? WEB_ROOT.'module/user/images/'.$me->PHOTO : WEB_ROOT.'module/user/images/default.png';
?>
<section class="content">
  <div class="container-fluid">
     <?php check_message(); ?>
    <div class="row">

      <!-- left: profile card + photo -->
      <div class="col-md-4">
        <div class="card ss-hero">
          <div class="ss-cover">
            <img id="profilePhoto" src="<?php echo $myPhoto; ?>" alt="Profile photo" onerror="this.style.visibility='hidden'">
            <h3><?php echo htmlspecialchars($me->DISPLAYNAME); ?></h3>
            <small><?php echo htmlspecialchars($me->TYPE); ?></small>
          </div>
          <div class="ss-body card-body">
            <div class="ss-stat"><i class="fas fa-at"></i><div><span>Username</span><b><?php echo htmlspecialchars($me->USERNAME); ?></b></div></div>
            <div class="ss-stat"><i class="fas fa-user-tag"></i><div><span>Account Type</span><b><?php echo htmlspecialchars($me->TYPE); ?></b></div></div>
            <div class="ss-stat"><i class="fas fa-calendar-plus"></i><div><span>Date Added</span><b><?php echo htmlspecialchars($me->DATEADDED); ?></b></div></div>
          </div>
        </div>

        <div class="card ss-panel">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-camera"></i> Profile Photo</h3></div>
          <form action="controller.php?action=updatephoto" method="POST" enctype="multipart/form-data">
            <div class="card-body text-center">
              <div class="ss-photo mb-0">
                <input type="file" name="photo" id="myPhotoInput" class="form-control-file" accept="image/*" required>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn ss-btn main"><i class="fas fa-upload"></i> Upload Photo</button>
            </div>
          </form>
        </div>
      </div>

      <!-- right: change password -->
      <div class="col-md-8">
        <div class="card ss-panel">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-key"></i> Change Password</h3></div>
          <form action="controller.php?action=editpass" method="POST" id="pwForm">
            <div class="card-body">
              <input type="hidden" name="UID" value="<?php echo (int)$me->UID; ?>">
              <input type="hidden" name="USERNAME" value="<?php echo htmlspecialchars($me->USERNAME); ?>">

              <div class="ss-hint"><i class="fas fa-shield-alt"></i>Enter your current password first, then choose a new one.</div>

              <div class="ss-group"><i class="fas fa-lock"></i>Current Password</div>
              <div class="form-group">
                <label for="curPw">Current Password</label>
                <div class="ss-input has-eye"><i class="fas fa-lock ico"></i>
                  <input type="password" class="form-control" name="CURRENT_PASSWORD" id="curPw" required>
                  <button type="button" class="eye" data-eye="curPw" tabindex="-1"><i class="fas fa-eye"></i></button>
                </div>
              </div>

              <div class="ss-group"><i class="fas fa-key"></i>New Password</div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="newPw">New Password</label>
                    <div class="ss-input has-eye"><i class="fas fa-key ico"></i>
                      <input type="password" class="form-control" name="PASSWORD" id="newPw" required>
                      <button type="button" class="eye" data-eye="newPw" tabindex="-1"><i class="fas fa-eye"></i></button>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="confPw">Confirm New Password</label>
                    <div class="ss-input has-eye"><i class="fas fa-check-double ico"></i>
                      <input type="password" class="form-control" id="confPw" required>
                      <button type="button" class="eye" data-eye="confPw" tabindex="-1"><i class="fas fa-eye"></i></button>
                    </div>
                    <small id="pwMsg" class="text-danger" style="display:none">Passwords do not match.</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" name="editpass" class="btn ss-btn warn"><i class="fas fa-key"></i> Update Password</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
document.addEventListener('click', function (e) {
  var b = e.target.closest ? e.target.closest('.eye') : null;
  if (!b) return;
  var f = document.getElementById(b.getAttribute('data-eye')), show = f.type === 'password';
  f.type = show ? 'text' : 'password';
  b.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
document.getElementById('pwForm').addEventListener('submit', function (e) {
  var ok = document.getElementById('newPw').value === document.getElementById('confPw').value;
  document.getElementById('pwMsg').style.display = ok ? 'none' : 'block';
  if (!ok) e.preventDefault();
});
document.getElementById('myPhotoInput').addEventListener('change', function () {
  if (!this.files || !this.files[0]) return;
  var r = new FileReader();
  r.onload = function (ev) { var im = document.getElementById('profilePhoto'); im.style.visibility = 'visible'; im.src = ev.target.result; };
  r.readAsDataURL(this.files[0]);
});
</script>