<?php
require_once(LIB_PATH.DS.'database.php');
class DoctorProfile{

	protected static $tbl_name = "tbldoctors";

	public $DOCTOR_ID;
	public $UID;
	public $FULLNAME;
	public $SPECIALIZATION;
	public $LICENSE_NO;
	public $CONTACT_NO;
	public $SCHEDULE_DAYS;
	public $SCHEDULE_TIME;
	public $STATUS;
	public $ADDEDBY;
	public $DATEADDED;

	function db_fields(){
		global $mydb;
		return $mydb->getFieldsOnOneTable(self::$tbl_name);
	}

	function listOfDoctors(){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." ORDER BY `FULLNAME` ASC");
		return $mydb->loadResultList();
	}

	function single_doctor($id=0){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE DOCTOR_ID = {$id} LIMIT 1");
		return $mydb->loadSingleResult();
	}

	/* Looks up the doctor profile linked to a given login account (UID),
	   if any - used to attribute a consultation to the logged-in doctor. */
	function profile_for_uid($uid=0){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE UID = '".intval($uid)."' LIMIT 1");
		return $mydb->loadSingleResult();
	}

	function count_doctors($whereActive = false){
		global $mydb;
		$sql = "SELECT * FROM ".self::$tbl_name;
		if ($whereActive) { $sql .= " WHERE `STATUS` = 'Active'"; }
		$mydb->setQuery($sql);
		return $mydb->num_rows();
	}

	/*---Instantiation of Object dynamically---*/
	static function instantiate($record) {
		$object = new self;
		foreach($record as $attribute=>$value){
		  if($object->has_attribute($attribute)) {
		    $object->$attribute = $value;
		  }
		}
		return $object;
	}

	private function has_attribute($attribute) {
	  return array_key_exists($attribute, $this->attributes());
	}

	protected function attributes() {
	  global $mydb;
	  $attributes = array();
	  foreach($this->db_fields() as $field) {
	    if(property_exists($this, $field)) {
			if($this->$field === null){
				continue;
			}
			$attributes[$field] = $this->$field;
		}
	  }
	  return $attributes;
	}

	protected function sanitized_attributes() {
	  global $mydb;
	  $clean_attributes = array();
	  foreach($this->attributes() as $key => $value){
	    $clean_attributes[$key] = $mydb->escape_value($value);
	  }
	  return $clean_attributes;
	}

	public function save() {
	  return isset($this->id) ? $this->update() : $this->create();
	}

	public function create() {
		global $mydb;
		$attributes = $this->sanitized_attributes();
		$sql = "INSERT INTO ".self::$tbl_name." (";
		$sql .= join(", ", array_keys($attributes));
		$sql .= ") VALUES ('";
		$sql .= join("', '", array_values($attributes));
		$sql .= "')";
		return	$mydb->InsertThis($sql);
	}

	public function update($id=0) {
		global $mydb;
		$attributes = $this->sanitized_attributes();
		$attribute_pairs = array();
		foreach($attributes as $key => $value) {
		  $attribute_pairs[] = "{$key}='{$value}'";
		}
		$sql = "UPDATE ".self::$tbl_name." SET ";
		$sql .= join(", ", $attribute_pairs);
		$sql .= " WHERE DOCTOR_ID =". $id;
		return  $mydb->InsertThis($sql);
	}

	public function delete($id=0) {
		global $mydb;
		$sql = "DELETE FROM ".self::$tbl_name;
		$sql .= " WHERE DOCTOR_ID =". $id;
		$sql .= " LIMIT 1 ";
		return  $mydb->InsertThis($sql);
	}

}
?>