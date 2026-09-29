<?php include __DIR__ . '/style.php'; ?>
<section class="content">

  <div class="container-fluid">
     <?php check_message(); ?>
    <div class="row">
      <div class="col-12">

        <div class="card ss-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users-cog"></i> List of User Accounts</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="tbluser" class="table table-bordered table-striped">
              <thead>
              <tr>
                <th>#</th>
                <th>Nickname</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Date Added</th>
                <th>Date Modified</th>
                <th>Action</th>
              </tr>
              </thead>
              <tbody>

              </tbody>
              <tfoot>

              </tfoot>
            </table>
            <div class="ss-actions mt-3">
              <button type="button" class="btn btn-add" data-toggle="modal" data-target="#AddNewEntry"><i class="fas fa-user-plus"></i> Add New</button>
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->
</section>

<!-----START of Add Form---->
<div class="modal fade ss-modal" id="AddNewEntry">
  <div class="modal-dialog">
    <form action="controller.php?action=add" enctype="multipart/form-data" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fas fa-user-plus"></i></span>Add New User</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">

          <div class="ss-photo">
            <img id="addPhotoPreview" src="<?php echo WEB_ROOT; ?>module/user/images/default.png" alt="Photo" onerror="this.style.visibility='hidden'">
            <label for="photo">Profile Photo <small class="text-muted">(optional)</small></label>
            <input type="file" class="form-control-file" name="photo" id="photo" accept="image/*">
          </div>

          <div class="ss-group"><i class="fas fa-id-card"></i>Account Details</div>
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label for="Fullname">Name</label>
                <div class="ss-input"><i class="fas fa-user ico"></i>
                  <input type="text" class="form-control form-control-sm" name="DISPLAYNAME" id="Fullname" placeholder="Enter Name" required>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="addUsername">Username</label>
                <div class="ss-input"><i class="fas fa-at ico"></i>
                  <input type="text" class="form-control form-control-sm" name="USERNAME" id="addUsername" placeholder="Enter UserName" required>
                </div>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="form-group">
                <label for="addPassword">Password</label>
                <div class="ss-input has-eye"><i class="fas fa-lock ico"></i>
                  <input type="password" class="form-control form-control-sm" name="PASSWORD" id="addPassword" placeholder="Enter Password" required>
                  <button type="button" class="eye" data-eye="addPassword" tabindex="-1"><i class="fas fa-eye"></i></button>
                </div>
              </div>
            </div>
          </div>

          <div class="ss-group"><i class="fas fa-user-shield"></i>Access</div>
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <label for="TYPE">User Type</label>
                <div class="ss-input"><i class="fas fa-user-tag ico"></i>
                  <select class="form-control form-control-sm" name="TYPE" id="TYPE">
                  <?php
                    $Usertype = new Usertype();
                    $cur = $Usertype->listOfUserTypes();
                    foreach ($cur as $m) {
                      echo '<option value="'. $m->USERTYPE.'">'.$m->USERTYPE .'</option>';
                    }
                  ?>
                  </select>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" name="save"><i class="fas fa-save"></i> Save changes</button>
        </div>
      </div>
    </form>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<!-----START of Edit Form---->
<div class="modal fade ss-modal" id="editEntry">
  <div class="modal-dialog">
    <form action="controller.php?action=edit" enctype="multipart/form-data" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fas fa-user-edit"></i></span>Modify User Account</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="UID" id="UID">

          <div class="ss-photo">
            <img id="currentUserPhoto" src="<?php echo WEB_ROOT; ?>module/user/images/default.png" alt="Photo" onerror="this.style.visibility='hidden'">
            <label for="photo1">Photo <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="file" class="form-control-file" name="photo1" id="photo1" accept="image/*">
          </div>

          <div class="ss-who">
            <i class="fas fa-user-circle"></i>
            <div>
              <small>Display Name</small>
              <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 font-weight-bold" name="FULLNAME" id="FULLNAME" placeholder="Enter Complete Name" readonly style="height:auto;box-shadow:none">
            </div>
          </div>

          <div class="ss-group"><i class="fas fa-id-card"></i>Account Details</div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="USERNAME">Username</label>
                <div class="ss-input"><i class="fas fa-at ico"></i>
                  <input type="text" class="form-control form-control-sm" id="USERNAME" name="USERNAME" placeholder="Enter Username" required>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="Password">Password</label>
                <div class="ss-input"><i class="fas fa-lock ico"></i>
                  <input type="password" name="PASSWORD" class="form-control form-control-sm" id="Password" placeholder="Use the key button" readonly>
                </div>
              </div>
            </div>
          </div>

          <div class="ss-group"><i class="fas fa-user-shield"></i>Access</div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="editType">User Type</label>
                <div class="ss-input"><i class="fas fa-user-tag ico"></i>
                  <select class="form-control form-control-sm" name="TYPE" id="editType">
                  <?php
                    $Usertype = new Usertype();
                    $cur = $Usertype->listOfUserTypes();
                    foreach ($cur as $m) {
                      echo '<option value="'. $m->USERTYPE .'">'. $m->USERTYPE .'</option>';
                    }
                  ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label for="customSwitch3">Status</label>
              <div class="ss-switch">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                  <input type="checkbox" name="status" class="custom-control-input" id="customSwitch3">
                  <label class="custom-control-label" for="customSwitch3">Active</label>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary swalDefaultSuccesss" name="edit"><i class="fas fa-save"></i> Save changes</button>
        </div>
      </div>
    </form>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<!-----Start of edit password Form---->
<div class="modal fade ss-modal" id="changepass">
  <div class="modal-dialog">
    <form action="controller.php?action=editpass" enctype="multipart/form-data" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><span class="ss-badge"><i class="fas fa-key"></i></span>Change User Password</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="UID" id="UIDpas">

          <div class="ss-who">
            <i class="fas fa-user-circle"></i>
            <div>
              <small>Resetting password for</small>
              <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 font-weight-bold" name="FULLNAME" id="dNAME" placeholder="Enter Complete Name" readonly style="height:auto;box-shadow:none">
            </div>
          </div>

          <div class="ss-hint"><i class="fas fa-info-circle"></i>The new password replaces the current one right away.</div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="UNAME">Username</label>
                <div class="ss-input"><i class="fas fa-at ico"></i>
                  <input type="text" class="form-control form-control-sm" id="UNAME" name="USERNAME" placeholder="Enter Username" readonly>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="cpPassword">New Password</label>
                <div class="ss-input has-eye"><i class="fas fa-lock ico"></i>
                  <input type="password" name="PASSWORD" class="form-control form-control-sm" id="cpPassword" required>
                  <button type="button" class="eye" data-eye="cpPassword" tabindex="-1"><i class="fas fa-eye"></i></button>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="cpType">User Type</label>
                <div class="ss-input"><i class="fas fa-user-tag ico"></i>
                  <select class="form-control form-control-sm" name="TYPE" id="cpType" disabled>
                  <?php
                    $Usertype = new Usertype();
                    $cur = $Usertype->listOfUserTypes();
                    foreach ($cur as $m) {
                      echo '<option value="'. $m->USERTYPE .'">'. $m->USERTYPE .'</option>';
                    }
                  ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <label for="cpSwitch">Status</label>
              <div class="ss-switch">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                  <input type="checkbox" name="status" class="custom-control-input" id="cpSwitch" disabled>
                  <label class="custom-control-label" for="cpSwitch">Active</label>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary swalDefaultSuccesss" name="editpass"><i class="fas fa-key"></i> Save changes</button>
        </div>
      </div>
    </form>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<script>
/* small helpers (plain JS so it works before jQuery finishes loading) */
document.addEventListener('click', function (e) {
  var b = e.target.closest ? e.target.closest('.eye') : null;
  if (!b) return;
  var f = document.getElementById(b.getAttribute('data-eye')), show = f.type === 'password';
  f.type = show ? 'text' : 'password';
  b.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});
function ssPreview(inputId, imgId) {
  var inp = document.getElementById(inputId);
  if (!inp) return;
  inp.addEventListener('change', function () {
    if (!this.files || !this.files[0]) return;
    var r = new FileReader();
    r.onload = function (ev) { var im = document.getElementById(imgId); im.style.visibility = 'visible'; im.src = ev.target.result; };
    r.readAsDataURL(this.files[0]);
  });
}
ssPreview('photo', 'addPhotoPreview');
ssPreview('photo1', 'currentUserPhoto');
</script>