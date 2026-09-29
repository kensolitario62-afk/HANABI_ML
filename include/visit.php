<?php
require_once(LIB_PATH.DS.'database.php');
class Visit{

	protected static $tbl_name = "tblvisits";

	public $VISIT_ID;
	public $PATIENT_ID;
	public $DOCTOR_ID;
	public $VISIT_DATE;
	public $CHIEF_COMPLAINT;
	public $DIAGNOSIS;
	public $NOTES;
	public $ENCODED_BY;
	public $DATEADDED;

	function db_fields(){
		global $mydb;
		return $mydb->getFieldsOnOneTable(self::$tbl_name);
	}

	function single_visit($id=0){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE VISIT_ID = {$id} LIMIT 1");
		return $mydb->loadSingleResult();
	}

	/* Every visit for one patient, most recent first - used by the
	   patient's View page. */
	function visits_for_patient($patient_id=0){
		global $mydb;
		$mydb->setQuery("SELECT v.*, d.FULLNAME AS DOCTOR_NAME
			FROM ".self::$tbl_name." v
			LEFT JOIN `tbldoctors` d ON d.DOCTOR_ID = v.DOCTOR_ID
			WHERE v.PATIENT_ID = '".intval($patient_id)."'
			ORDER BY v.VISIT_DATE DESC, v.VISIT_ID DESC");
		return $mydb->loadResultList();
	}

	function count_visits(){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name);
		return $mydb->num_rows();
	}

	function count_visits_today(){
		global $mydb;
		$mydb->setQuery("SELECT * FROM ".self::$tbl_name." WHERE `VISIT_DATE` = CURDATE()");
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
		$sql .= " WHERE VISIT_ID =". $id;
		return  $mydb->InsertThis($sql);
	}

	public function delete($id=0) {
		global $mydb;
		$sql = "DELETE FROM ".self::$tbl_name;
		$sql .= " WHERE VISIT_ID =". $id;
		$sql .= " LIMIT 1 ";
		return  $mydb->InsertThis($sql);
	}

}
?>