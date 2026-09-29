<?php
require_once("../../include/initialize.php");
global $mydb;

$act = isset($_POST['act']) ? $_POST['act'] : '';

/* -----------------------------------------------------------------
   STAGE 1 (Register): details for the Register modal.
   ----------------------------------------------------------------- */
if ($act === 'register_info') {

	$sid = intval($_POST['UID']);
	$output = array();

	$mydb->setQuery("SELECT * FROM `tblstudent` WHERE S_ID = '".$sid."' LIMIT 1");
	foreach ($mydb->loadResultList() as $row) {
		$output['S_ID']      = $row->S_ID;
		$output['IDNO']      = $row->IDNO;
		$output['FULLNAME']  = trim($row->LNAME.', '.$row->FNAME.' '.$row->MNAME);
		$output['COURSE_ID'] = $row->COURSE_ID;
	}

	$output['ACTIVE_SY']  = '';
	$output['ACTIVE_AY']  = '';
	$mydb->setQuery("SELECT SY_ID, SCHOOL_YEAR FROM `tblschoolyear` WHERE STATUS = 'Active' ORDER BY SY_ID DESC LIMIT 1");
	foreach ($mydb->loadResultList() as $row) {
		$output['ACTIVE_SY'] = $row->SY_ID;
		$output['ACTIVE_AY'] = $row->SCHOOL_YEAR;
	}

	$output['SUGGEST_CATEGORY'] = 'New';
	$mydb->setQuery("SELECT ENROLLMENT_ID FROM `tblenrollment` WHERE S_ID = '".$sid."' LIMIT 1");
	if ($mydb->num_rows() > 0) {
		$output['SUGGEST_CATEGORY'] = 'Old';
	}

	echo json_encode($output);
	exit;
}

if (isset($_POST['UID'])) {
	$output = array();
	$sid = intval($_POST["UID"]);
	$query =	"SELECT * FROM `tblstudent` 
		WHERE S_ID = '".$sid."' 
		LIMIT 1";
	$mydb->setQuery($query);
	$result = $mydb->loadResultList();

	foreach($result as $row)
	{ 
		$output["UID"]   = $row->S_ID;
		$output["IDNO"]  = $row->IDNO;
		$output["FNAME"] = $row->FNAME;
		$output["MNAME"] = $row->MNAME;
		$output["LNAME"] = $row->LNAME;
		$output["SEX"]   = $row->SEX;

		$bday = $row->BDAY;
		if ($bday === null || $bday == '0000-00-00' || $bday == '') {
			$output["BDAY"] = '';
		} else {
			$output["BDAY"] = substr($bday, 0, 10);
		}

		$output["BPLACE"] = isset($row->BPLACE) ? $row->BPLACE : '';
		$output["AGE"] = isset($row->AGE) ? $row->AGE : '';
		$output["NATIONALITY"] = isset($row->NATIONALITY) ? $row->NATIONALITY : '';
		$output["RELIGION"] = isset($row->RELIGION) ? $row->RELIGION : '';
		$output["CONTACT_NO"] = isset($row->CONTACT_NO) ? $row->CONTACT_NO : '';
		$output["HOME_ADD"] = isset($row->HOME_ADD) ? $row->HOME_ADD : '';
		$output["EMAIL"] = isset($row->EMAIL) ? $row->EMAIL : '';
		$output["photo"] = isset($row->photo) ? $row->photo : '';
	}
	echo json_encode($output);
}else{
	$output = array();
	$query = "SELECT `S_ID`, `LNAME`, `FNAME`, `MNAME`, `SEX`, `BDAY` FROM `tblstudent`";

	if(isset($_POST["search"]["value"]))
	{
	$query .= " where `LNAME` LIKE '%".$_POST["search"]["value"]."%' ";
	}
	if(isset($_POST["order"]))
	{
		$query .= 'ORDER BY '.$_POST['order']['0']['column'].' '.$_POST['order']['0']['dir'].' ';
	}
	else
	{
		$query .= 'ORDER BY `S_ID` DESC ';
	}
	if($_POST["length"] != -1)
	{
		$query .= " LIMIT " . $_POST['start'] . ", " . $_POST['length'] . "";
	}
	$mydb->setQuery($query);
	$cur = $mydb->loadResultList();
	$data = array();
	$filtered_rows = $mydb->num_rows();
	$i = 1;	
	foreach ($cur as $result) {
	$sub_array = array();
		
		$sub_array[] =$i;
	
		$sub_array[] = $result->LNAME;
		$sub_array[] = $result->FNAME;
		$sub_array[] = $result->MNAME;
		$sub_array[] = $result->SEX;
		$sub_array[] = $result->BDAY;
		
	$isDoctorView = (isset($_SESSION['TYPE']) && $_SESSION['TYPE'] == 'Doctor');

	/* Doctors don't register students for enrollment, so the Reg
	   button is swapped out for the Consult button instead - this is
	   what opens the "Record Consultation" modal wired up in
	   index.php's .consultEntry click handler. It went missing before
	   because nothing here was actually generating it. */
	$fullName = trim($result->FNAME.' '.$result->MNAME.' '.$result->LNAME);

	$regButton = $isDoctorView ? '
		<button type="button" S_ID="'.$result->S_ID.'" NAME="'.htmlspecialchars($fullName, ENT_QUOTES).'" class="btn btn-primary btn-xs consultEntry" title="Record Consultation"><span class="fa fa-stethoscope fw-fa"></span> Consult</button>
	' : '
		<button type="button" UID="'.$result->S_ID.'" class="btn btn-success btn-xs registerEntry" title="Register for Enrollment"><span class="fa fa-clipboard-check fw-fa"></span> Reg</button>
	';

		$sub_array[] = '

		<button type="button" name="update" UID="'.$result->S_ID.'" class="btn btn-warning btn-xs editEntry" title="Edit"><span class="fa fa-edit fw-fa"></span></button> 
		
		<a href="index.php?view=view&id='.$result->S_ID.'"><button type="button" class="btn btn-info btn-xs" title="View"><span class="fa fa-eye"></span></button></a>

		'.$regButton.'

		<a href="controller.php?action=delete&id='.$result->S_ID .'"><button type="button" class="btn btn-danger btn-xs SaveReg" title="Delete"><span class="fa fa-trash fw-fa"></span> Del</button></a>


		';
		$data[] = $sub_array;
	$i = $i + 1;		
	}
	function get_total_all_records()
	{
		global $mydb;
		$statement = "SELECT `S_ID`, `LNAME`, `FNAME`, `MNAME`, `SEX`, `BDAY` FROM `tblstudent`";
		$mydb->setQuery($statement);
		return $mydb->num_rows();
	}

	$output = array('data' 			   => $data, 
					"recordsTotal"	   => $filtered_rows,
					"recordsFiltered"	=>	get_total_all_records() );
	echo json_encode($output);
}
?>