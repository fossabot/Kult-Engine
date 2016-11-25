<?php
namespace kult_engine;
	class sqlHelper{

		private function table_quoted($table)
		{
			return strpos($table, "`") === false ? "`" . $table . "`" : $table;
		}

		public function select_int($table,$cat,$id='id')
		{
			return "SELECT * FROM " . $this->table_quoted($table) . " WHERE " . $cat . " = :" . $id;
		}

		public function select_string($table,$cat,$id='id')
		{
			return "SELECT * FROM " . $this->table_quoted($table) . " WHERE " . $cat . " like :" . $id;
		}

		public function insert($table,$cat,$id=null)
		{
			$cat = is_array($cat) ? $cat : [$cat];
			$id = is_null($id) ? $cat : $id;
			$id = is_array($id) ? $id : [$id];

			$r = "INSERT INTO " . $this->table_quoted($table) . " ( `";

			for ($i=0; $i < count($cat)-1 ; $i++) { 
				$r .= $cat[$i] . "` , `" ;
			}
			$r .= $cat[count($cat)-1] . "` ) " ;

			$r .= "VALUES ( ";

			for ($i=0; $i < count($id)-1 ; $i++) { 
				$r .= ':'. $id[$i] . " , " ;
			}
			$r .= ':'. $id[count($id)-1] . " ) " ;
			return $r;
		}

		public function update_int($table,$haystack,$needle='id',$cat,$id=null)
		{

			$cat = is_array($cat) ? $cat : [$cat];
			$id = is_null($id) ? $cat : $id;
			$id = is_array($id) ? $id : [$id];

			$r = "UPDATE " . $this->table_quoted($table);
			$r .= " SET `";
			for( $i= 0 ; $i < count($cat)-1 ; $i++)
			{
				$r .= $cat[$i] . "` = :" . $id[$i] . " , `";
			}
			$r .= $cat[count($cat)-1] . "` = :" . $id[count($cat)-1];
			$r .= " WHERE " . $this->table_quoted($haystack) . " = :" .$needle;
			return $r;
		}

		public function delete($table,$cat,$id='id')
		{
			return "DELETE FROM " . $this->table_quoted($table) . " WHERE " . $this->table_quoted($cat) . " = :".$id;
		}
	}