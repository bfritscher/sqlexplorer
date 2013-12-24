<?php
class SqlvalidatorBehavior extends ModelBehavior {
	
	var $__dbconfig;
	var $__dbconfigAdmin;
	
	function __construct(){
		$this->__dbconfig = ConnectionManager::getInstance()->config->defaultDataRead;
		$this->__dbconfigAdmin = ConnectionManager::getInstance()->config->defaultDataWrite;
	}
	
	function _getConnection($assignment){
		return @pg_connect("host=".$this->__dbconfig['host']." dbname=mpe_" . strtolower($assignment) . " user=" . $this->__dbconfig['login'] . " password=" . $this->__dbconfig['password']);
	}
	
	function _getAdminConnection($assignment){
		return @pg_connect("host=".$this->__dbconfigAdmin['host']." dbname=mpe_" . strtolower($assignment) . " user=" . $this->__dbconfigAdmin['login'] . " password=" . $this->__dbconfigAdmin['password']);
	}
	
	function listAssignments(&$Model){
		$list = array();
		$connection = $this->_getConnection('demo');
		if($connection){
			$sql ="SELECT substring(datname from 5) as name FROM pg_database WHERE datname LIKE 'mpe_%'";
			$result = pg_query($sql) or die(pg_last_error());
			while ($row = pg_fetch_row($result)) {
				$list[$row[0]] = $row[0];
			}
			pg_close($connection);
		}
		return $list;
	}
	
	function listTables(&$Model, $assignment){
		$list = array();
		$connection = $this->_getConnection($assignment);
		if($connection){
			$sql ="SELECT tablename, has_table_privilege('".$this->__dbconfig['login']."', tablename, 'select') AS access FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename";
			$result = pg_query($sql) or die(pg_last_error());
			while ($row = pg_fetch_row($result)) {
				$list[$row[0]] = $row[1];
			}
			pg_close($connection);
		}
		return $list;
	}
	
	function fixPermission(&$Model, $assignment){
		$list = array();
		$connection = $this->_getAdminConnection($assignment);
		if($connection){
			$sql ="SELECT tablename, has_table_privilege('bdmc_readonly', tablename, 'select') AS access FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename";
			$result = pg_query($sql) or die(pg_last_error());
			while ($row = pg_fetch_row($result)) {
				if($row[1] == 'f'){
					pg_query("GRANT SELECT ON TABLE ".$row[0]." TO bdmc_readonly") or die(pg_last_error());
				}
			}
			pg_close($connection);
		}
	}
	
	function createDatabase(&$Model, $assignment){
		$connection = $this->_getAdminConnection('classes');
		if($connection){
			//TODO: should escape
			$sql = "CREATE DATABASE mpe_" . strtolower($assignment) . " WITH OWNER = bdmc ENCODING = 'UTF8'";
			$result = pg_query($sql) or die(pg_last_error());
			pg_close($connection);
			//need superadmin rights to change public schema privileges.
			$conf = ConnectionManager::getInstance()->config->superAdmin;
			$connection = pg_connect("host=".$conf['host']." dbname=mpe_" . strtolower($assignment) . " user=" . $conf['login'] . " password=" . $conf['password']);
			$sql = "REVOKE ALL ON SCHEMA public FROM public;GRANT USAGE ON SCHEMA public TO public;GRANT ALL ON SCHEMA public TO bdmc;";
			pg_query($sql) or die(pg_last_error());
			pg_close($connection);
		}
	}
	
	function validateSqlForAssignment(&$Model, $sql, $assignment){
		$connection = $this->_getConnection($assignment);
		if($connection){
			$result = pg_query($sql) or die(pg_last_error());
			if($result){
				pg_free_result($result);
				return true;
			}
			pg_close($connection);
		}
		return false;
	}
		
	function queryByAssignment(&$Model, $assignment, $sql, $limit=200){
		$connection = $this->_getConnection($assignment) or $data->error = 'Erreur de connection à la base de données' . pg_last_error();		
		if($connection){
			$data = $this->_fetchQueryData($connection, $assignment, $sql, $limit);
			$data = $this->_truncateResult($data, $limit);
			pg_close($connection);
			
		}
		return $data;
	}
	
	function _truncateResult($data, $limit){		
		if(!isset($data->error) && !is_null($limit) && $data->numrows > $limit){
			if($data->content[0]){
				$error = array_fill(0, count($data->content[0]), '');		
				$error[0] = "truncated! showing $limit records of ". $data->numrows ." use csv/excel output to see all rows.";
				$data->content = array_merge(array($error), array_slice($data->content, 0, $limit));
			}
		}
		
		return $data;
	}
	
	function _cleanSql($sql){
		$sql = stripslashes($sql);
		return str_replace(array("\xE2\x80\x8B"), array(''), $sql);
	}
	
	function _fetchQueryData($connection, $assignment, $sql){
		//store result
		// data->error
		// data->header[,]
		// data->content[[],[]]
		// data->numrows
		$data = new stdClass();
			
		//sanatize query
		$sql = $this->_cleanSQL($sql);
		//test if query is valid
		$result =@ pg_query($sql) or $data->error = pg_last_error();
		if($result){
			//get headers
			$data->header = array();
			$data->content = array();
			for ($headerIndex = 0; $headerIndex < pg_num_fields($result); $headerIndex++) {
				$data->header[] = pg_field_name($result, $headerIndex);
			}
            if(count($data->header) == 0){
                $data->header[0] = 'OK';
                $data->content[] = array('');
            }
			while ($row = pg_fetch_row($result)) {
				$row_data = array();
				foreach ($row as $value) {
					$row_data[]=$value;
				}
				$data->content[] = $row_data; 
			}
			$data->numrows = pg_num_rows($result);

			// Free resultset of the user query
			pg_free_result($result);
			
		}
		return $data;
	}
	
	function evaluate(&$Model, $assignment, $sqlToEvaluate, $sqlAnswer, $limit=200, $joindetection=true){
		$answer = null;
		$error = "Erreur: ";
		$answer->result = false;
		$connection =@ $this->_getConnection($assignment)
					  or $error = 'Could not connect: ' . pg_last_error();	
					  	
		$answer->data = $this->_fetchQueryData($connection, $assignment, $sqlToEvaluate);
		
		if(!isset($answer->data->error)){
			$sqlToEvaluate = $sql = $this->_cleanSQL($sqlToEvaluate);
			//get the number of rows from the correct answer (the view)
			$result = pg_query($sqlAnswer);
			if($result){
				$solution_query_rows = pg_num_rows($result);
				//test if the row numbers are the same before testings set to catch DISTINCT
				if($answer->data->numrows == $solution_query_rows){
					//make sure the query we build might be valid
					$sqlToEvaluate = str_replace(';','',$sqlToEvaluate);
					//evaluate if query = view (set A-B Union set B-A ? empty)
					$new_sql = <<<SQL
(($sqlAnswer)
EXCEPT
($sqlToEvaluate))
UNION
(($sqlToEvaluate)
EXCEPT
($sqlAnswer))
SQL;
			
					$result2 =@ pg_query($new_sql);
					//if query did not return a result the user query was wrong
					//probably not the same number of fields
					if($result2){
						$rows = pg_num_rows($result2);
						//if the returned result is empty the user's query was certainly correct
						if($rows == 0){
							$answer->result = true;
							//if there is an order by we have to compare lines
							if(preg_match('/ORDER .*BY/ims', $sqlAnswer) > 0){
								$r=0;
								while ($row = pg_fetch_row($result)) {
									$v=0;
									foreach ($row as $value) {
										if($value != $answer->data->content[$r][$v]){
											$answer->result = false;
											$error .= 'ordre incorrect';
											break 2;
										}
										$v++;
									}
									$r++;
								}
							}
							//TODO improve join detection and error messages
							if($joindetection){
								$nb_join_sqlAnswer = preg_match('/JOIN/ims', $sqlAnswer);
								$nb_join_sqlToEvaluate = preg_match('/JOIN/ims', $sqlToEvaluate);
								if($nb_join_sqlAnswer == 0 && $nb_join_sqlToEvaluate > 0){
									//$answer->result = false;
									//$error .= 'faire la requête sans JOIN';
								} elseif ($nb_join_sqlAnswer != $nb_join_sqlToEvaluate){
									$answer->result = false;
									$error .= 'pas le même nombre de JOIN (vous: ' . $nb_join_sqlToEvaluate . ', réponse: '. $nb_join_sqlAnswer .')';
								}
							}
						}else{
							$error .= 'pas la bonne réponse';
						}
						// Free resultset
						pg_free_result($result2);
					}else{
						//TODO: move/translate
						$error .= 'pas la bonne réponse (vérifiez le SELECT)';
					}//end of number of rows matched
					
				}else{
					//TODO: move/translate
					$error .= 'pas la bonne réponse';
				}//end of distinct
			}//end if solution query did return a result
			$answer->data = $this->_truncateResult($answer->data, $limit);
		}//end if no data error
		pg_close($connection);
		$answer->result_bool = $answer->result; 
		if($answer->result){
			$answer->result = "correct";
			$answer->sql_answer = $sqlAnswer;
		}else{
			$answer->result = "wrong";
		}
		if($error != "Erreur: "){
			$answer->data->error = $error;
		}
		return $answer;
	}	
}