<?php
	class ModelOnlusOnlus extends Model{
		public function getOnlus($id){
			$query = $this->db->query("SELECT * FROM ".DB_PREFIX."onlus WHERE onlus_id=$id");
			
			if($query->num_rows){
				return array(
					'onlus_id'         => $query->row['onlus_id'],
					'name'             => $query->row['name'],
					'paypal_id'        => $query->row['paypal_id']
				);
			}
			else{
				return false;
			}
		}
		
		public function getAllOnlus(){
			$query = $this->db->query("SELECT * FROM ".DB_PREFIX."onlus");
			if($query->num_rows){
				return $query->rows;
			}
			else{
				return false;
			}
		}
		
		public function validateOnlus($data){
			foreach($data as $field=>$value){
				if(empty($value))
					return false;
			}
			return true;
		}
		
		public function addOnlus($data){
			if(!$this->validateOnlus($data))
				return;
			foreach($data as $field=>$value){
				$fields[] = $field;
				$values[] = $value;
			}
			$fields = implode('`,`',$fields);
			$values = implode("','",$values);
			$query = "INSERT INTO ".DB_PREFIX."onlus (`$fields`) VALUES ('$values')";
			$this->db->query($query);
		}
	}
?>