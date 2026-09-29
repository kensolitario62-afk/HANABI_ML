<?php
// Solitario Solutions

require_once ("../../include/initialize.php");
	  if (!isset($_SESSION['ACCOUNT_ID'])){
     // redirect(web_root."admin/index.php");
     }

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
	case 'add' :
	doInsert();
	break;
	
	case 'edit' :
	doEdit();
	break;
	
	case 'editpass' :
	dochangepass();
	break;

	case 'updatephoto' :
	doUpdatePhoto();
	break;

	case 'delete' :
	doDelete();
	break;
	 
	}
    
	function doInsert(){
  //`UID`, `USERNAME`, `PASSWORD`, `TYPE`, `FULLNAME`, `DOB`, `AGE`,
  // `SEX`, `ADDRESS`, `PICTURE`, `ADDEDBY`, `DATEADDED`, `MODIFIEDBY`, `DATEMODIFIED`  
		
	 
		$user = new User();
		$DISPLAYNAME   = $_POST['DISPLAYNAME'];
		$USERNAME	= $_POST['USERNAME'];
		$PASSWORD 	= $_POST['PASSWORD'];
		$TYPE 		= $_POST['TYPE'];
		$DATEADDED  = date("Y-m-d H:i:s");
		$ADDEDBY	= $_SESSION['UID'];
		$DATEMODIFIED = date("Y-m-d H:i:s");
		$MODIFIEDBY	 = $_SESSION['UID'];
		$res = $user->find_all_user($USERNAME);

		// Only the filename is stored - header.php already knows the
		// folder (module/user/images/) and prepends it itself.
		$PHOTO = '';
		if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
			$uploadDir = "images/";
			if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
			$filename = time() . '_' . basename($_FILES['photo']['name']);
			move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
			$PHOTO = $filename;
		}
		
		
			if ($res >=1) {
				message("Username already exist!", "error");
				redirect('index.php');
			}else{
				
				$user->DISPLAYNAME = $DISPLAYNAME;
				$user->USERNAME = $USERNAME;
				$user->PASSWORD = sha1($PASSWORD);
				$user->TYPE 	= $TYPE;
				$user->PHOTO    = $PHOTO;
				$user->DATEADDED = $DATEADDED;
				$user->ADDEDBY 	= $ADDEDBY;
				$user->DATEMODIFIED = $DATEMODIFIED;
				$user->MODIFIEDBY 	= $MODIFIEDBY;
				
				 
				 $istrue = $user->create(); 
				 
				 		if ($istrue == true) {

					 		// Doctor accounts get their own linked profile automatically -
					 		// no manual "link this account" step needed. The admin can fill
					 		// in the rest (specialization, license, schedule) later.
					 		if ($TYPE == 'Doctor') {
					 			global $mydb;
					 			$newUID = intval($mydb->insert_id());

					 			require_once(LIB_PATH.DS."doctorprofile.php");
					 			$doctor = new DoctorProfile();
					 			$doctor->UID        = $newUID;
					 			$doctor->FULLNAME   = $DISPLAYNAME;
					 			$doctor->STATUS     = 'Active';
					 			$doctor->ADDEDBY    = $ADDEDBY;
					 			$doctor->DATEADDED  = date('Y-m-d');
					 			$doctor->create();
					 		}

					 		message("New User [". $USERNAME ."] has been created successfully!", "success");
					 		redirect('index.php');
					 	}else{
					 		message("No user has been created successfully!", "error");
					 		redirect('index.php');
					 	}
			}	 

	}
	function doEdit(){
		if (isset($_POST['edit'])) {
			$user = new User();
			$UID	= $_POST['UID'];
			$USERNAME	= $_POST['USERNAME'];
			//$PASSWORD 	= $_POST['PASSWORD'];
			$TYPE 		= $_POST['TYPE'];
			if (isset($_POST['status']) =='on') {
				$STATUSACTIVE = 1;
			}else{
				$STATUSACTIVE = 0;
			}

			$DATEMODIFIED = date("Y-m-d H:i:s");
			$MODIFIEDBY	 = $_SESSION['UID'];
			$res = $user->find_all_user_notthis($USERNAME, $UID);
		
		
				if ($res >=1) {
					message("Username already exist!", "error");
					redirect('index.php');
				}else{		
						
				$user->USERNAME = $USERNAME;
				//$user->PASSWORD = sha1($PASSWORD);
				$user->TYPE 	= $TYPE;
				$user->STATUSACTIVE = $STATUSACTIVE;
				$user->DATEMODIFIED = $DATEMODIFIED;
				$user->MODIFIEDBY 	= $MODIFIEDBY;

				// Only touches the photo if a new file was actually chosen -
				// leaving it blank keeps whatever photo the account already has.
				if (isset($_FILES['photo1']) && $_FILES['photo1']['error'] == 0) {
					$uploadDir = "images/";
					if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
					$filename = time() . '_' . basename($_FILES['photo1']['name']);
					move_uploaded_file($_FILES['photo1']['tmp_name'], $uploadDir . $filename);
					$user->PHOTO = $filename;
				}
				
				 
				 $istrue = $user->update($UID); 
				 if ($istrue == true){

				 	/* Same auto-linking doInsert() does for a brand-new Doctor
				 	   account, applied here too: if this edit just made (or kept)
				 	   the account a Doctor and it doesn't already have a linked
				 	   tbldoctors row, create one now. Without this, an account
				 	   that was promoted to Doctor via Edit - rather than created
				 	   as one from the start - would show up on the clinic side
				 	   with no profile at all. */
				 	if ($TYPE == 'Doctor') {
				 		global $mydb;
				 		require_once(LIB_PATH.DS."doctorprofile.php");

				 		$doctorProfileModel = new DoctorProfile();
				 		$existingProfile = $doctorProfileModel->profile_for_uid(intval($UID));

				 		if (!$existingProfile) {
				 			$mydb->setQuery("SELECT DISPLAYNAME FROM `tblusers` WHERE UID = '".intval($UID)."' LIMIT 1");
				 			$acct = $mydb->loadSingleResult();

				 			$doctor = new DoctorProfile();
				 			$doctor->UID        = intval($UID);
				 			$doctor->FULLNAME   = ($acct && $acct->DISPLAYNAME) ? $acct->DISPLAYNAME : $USERNAME;
				 			$doctor->STATUS     = 'Active';
				 			$doctor->ADDEDBY    = intval($_SESSION['UID']);
				 			$doctor->DATEADDED  = date('Y-m-d');
				 			$doctor->create();
				 		}
				 	}

				 	message("User account has been Updated successfully!", "success");
				 	redirect('index.php');
				 	
				 }else{
				 	message("No user account has been updated successfully!", "error");
				 	redirect('index.php');
				 }
			}
					 
		}
		
	
	}
	function dochangepass(){
		if (isset($_POST['editpass'])) {
			global $mydb;
			$UID	= $_POST['UID'];
			$PASSWORD 	= $_POST['PASSWORD'];
			$DATEMODIFIED = date("Y-m-d H:i:s");
			$MODIFIEDBY	 = $_SESSION['UID'];

			// Only require the CURRENT password when someone is changing
			// their OWN account's password (My Profile). An admin
			// resetting a different account's password from the User
			// Module doesn't know - and shouldn't need - that account's
			// current password.
			$isSelfService = (intval($UID) === intval($_SESSION['UID']));

			if ($isSelfService) {
				$CURRENT_PASSWORD = isset($_POST['CURRENT_PASSWORD']) ? $_POST['CURRENT_PASSWORD'] : '';

				$mydb->setQuery("SELECT `PASSWORD` FROM `tblusers` WHERE `UID` = '".intval($UID)."' LIMIT 1");
				$row = $mydb->loadSingleResult();

				if (!$row || sha1($CURRENT_PASSWORD) !== $row->PASSWORD) {
					message("Your current password is incorrect.", "error");
					redirect('myprofile.php');
					return;
				}
			}

			$user = new User();
			$user->PASSWORD = sha1($PASSWORD);
			$user->DATEMODIFIED = $DATEMODIFIED;
			$user->MODIFIEDBY 	= $MODIFIEDBY;

			 $istrue = $user->update($UID); 
			 if ($istrue == true){
			 	
			 	message("User password has been Updated successfully!", "success");
			 	redirect($isSelfService ? 'myprofile.php' : 'index.php');
			 	
			 }else{
			 	message("No Password has been updated successfully!", "error");
			 	redirect($isSelfService ? 'myprofile.php' : 'index.php');
			 }
			
					 
		}
	}

	/* -------------------------------------------------------------------
	   My Profile - photo upload. Saved the same way as the Add/Edit
	   forms: only the filename goes into tblusers.PHOTO, the images/
	   folder is prepended wherever it's displayed.
	   ------------------------------------------------------------------- */
	function doUpdatePhoto(){

		if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != 0) {
			message("Please choose a photo to upload.", "error");
			redirect('myprofile.php');
			return;
		}

		$allowedExt = array('jpg', 'jpeg', 'png', 'gif');
		$ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowedExt)) {
			message("Only image files are accepted for the profile photo.", "error");
			redirect('myprofile.php');
			return;
		}

		$uploadDir = "images/";
		if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
		$filename = time() . '_' . basename($_FILES['photo']['name']);
		move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);

		$user = new User();
		$user->PHOTO = $filename;
		$istrue = $user->update($_SESSION['UID']);

		if ($istrue == true) {
			message("Profile photo updated.", "success");
		} else {
			message("The photo could not be updated.", "error");
		}
		redirect('myprofile.php');
	}

	function doDelete(){
		
				$id = 	$_GET['id'];

				$user = New User();
	 		 	$user->delete($id);
			 
			message("User already Deleted!","success");
			redirect('index.php');
		
	}
 
?>