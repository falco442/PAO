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
	}
?>