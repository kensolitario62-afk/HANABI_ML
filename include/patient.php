<?php
require_once(LIB_PATH.DS.'database.php');
class Patient{

	protected static $tbl_name = "tblpatients";

	public $PATIENT_ID;
	public $S_ID;
	public $FNAME;
	public $MNAME;
	public $LNAME;
	public $SEX;
	public $BDAY;
	public $AGE;
	public $CONTACT_NO;
	public $ADDRESS;
	public $STATUS;
	public $ADDEDBY;
	public $DATEADDED;

	function db_fields(){
		global $mydb;
		return $mydb->getFieldsOnOneTable(self::$tbl_name);
	}

	function listOfPatients(){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." ORDER BY `LNAME` ASC, `FNAME` ASC");
		return $mydb->loadResultList();
	}

	function single_patient($id=0){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE PATIENT_ID = {$id} LIMIT 1");
		return $mydb->loadSingleResult();
	}

	/* Used by doConsult() to check whether this student already has a
	   patient record before creating a new one. */
	function patient_for_student($s_id=0){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE S_ID = '".intval($s_id)."' LIMIT 1");
		return $mydb->loadSingleResult();
	}

	function count_patients(){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name);
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
		$sql .= " WHERE PATIENT_ID =". $id;
		return  $mydb->InsertThis($sql);
	}

	public function delete($id=0) {
		global $mydb;
		$sql = "DELETE FROM ".self::$tbl_name;
		$sql .= " WHERE PATIENT_ID =". $id;
		$sql .= " LIMIT 1 ";
		return  $mydb->InsertThis($sql);
	}

}
?>