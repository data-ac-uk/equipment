<?php
class api {

	function search() {
		
        $f3=Base::instance();
		
		$params['q'] = $_REQUEST['q'];
		//forwarded gets
		foreach(array('instsearch') as $i){
			if(isset($_REQUEST[$i])){
				$params[$i] = $_REQUEST[$i];
			}
		}
		
		
		$starttime = microtime(true);
		
		if(isset($_REQUEST['page_size']) and (int)$_REQUEST['page_size'] and (int)$_REQUEST['page_size'] < 100){
			$params['page_size'] = (int)$_REQUEST['page_size'];
		}else{
			$params['page_size'] = 10;
		}
		
		$sql_where = array();
		$sql_params = array();
		$sql_params_i = 1;
				
		$ret = array();
	
		
		$eq = $f3->eq;
		$eq->launch_db();
		
		$sql_sel = "";
		
		$sql_from = "FROM itemUniquips
		INNER JOIN `items` ON `itemU_id` = `item_id`
			INNER JOIN `orgs` ON `itemU_org` = `org_uri`
				LEFT OUTER JOIN `locations` ON item_location = loc_uri";	
		
		if(strlen($params['q'])){
		
			$sql_where[] = "(`itemU_f_name` LIKE ? OR `itemU_f_desc` LIKE ? OR `itemU_f_technique` LIKE ? )";
				
		
			$sql_params[$sql_params_i++] = "%{$params['q']}%";
			$sql_params[$sql_params_i++] = "%{$params['q']}%";
			$sql_params[$sql_params_i++] = "%{$params['q']}%";
	
		}
		
		if(isset($_REQUEST['filter'])){
			$filters = json_decode($_REQUEST['filter'],true);
			foreach($filters as $fk=>$fv){
				$paramfilters = array();
				switch($fk){
					case "consortia":
						$sql_from .= "\nINNER JOIN `groupLinks` ON `link_org` = `org_uri`";
						$sql_where[] = "`link_group` = ?";
						$sql_params[$sql_params_i++] = "$fv";
						$paramfilters['consortia'] = $fv;
					break;
					case "org":
						$sql_where[] = "`org_uri` = ?";
						$sql_params[$sql_params_i++] = "$fv";
						$paramfilters['org'] = $fv;					
					break;
				}

			}
			$params['filter'] = json_encode($paramfilters);
		}
	
		
		if(isset($_REQUEST['geocode'])){
			$parts = explode(",",$_REQUEST['geocode']);
			$params['geocode'] = $_REQUEST['geocode'];
			if(!(float)$parts[0] || !(float)$parts[1]){
				die("Please form geocode properly");
			}
			
			require_once($eq->config->pwd."/lib/phpLocation/phpLocation.php");
			$pos = new phpLocation();
			$pos->lat = (float)$parts[0];
			$pos->lon = (float)$parts[1];
			$pos->toGrid();
			
			$sql_where .= " and `item_location` NOT LIKE '' ";
			$sql_sel .= ", ROUND(SQRT( POW({$pos->east} - `loc_easting`, 2) + POW({$pos->north} - `loc_northing`, 2) )/1000,2) as distance ";
			$sql_order = "ORDER BY `distance` ASC";
			
			if(isset($parts[2]) && $dist = (float)$parts[2]){
				$sql_where .= " and `distance` <= $dist";
			}
			
		}
				
		
		
		$sql_where = "WHERE ".join(" AND ",$sql_where);
		$count = $eq->db->exec("SELECT count(`item_id`) as tcount $sql_sel  {$sql_from} {$sql_where}", $sql_params);
		
		
		
		if(isset($_REQUEST['page'])){
			if( (int)$_REQUEST['page'] >= ceil($count[0]['tcount']/$params['page_size'])){
				die("Page no too high!");
			}else{
				$params['page'] = (int)$_REQUEST['page'];
			}
		}else{
			$params['page'] = 0;
		}
		
		$sql_limit = " LIMIT ".($params['page_size']*$params['page']).", {$params['page_size']}";
		
		
		if($_REQUEST['showsql']){
			echo "<pre>";
			echo "SELECT * {$sql_sel} {$sql_from} {$sql_where} {$sql_order} {$sql_limit}\n";
			print_r($sql_params);
		}
		
		$ret['query'] = $params['q'];
		$ret['page'] = $params['page'];
		$ret['page_size'] = $params['page_size'];
		$ret['total_pages'] = ceil($count[0]['tcount']/$params['page_size']);
		$ret['this_request'] = "?".http_build_query($params);
		if($params['page'] != 0){
			$tparams = $params;
			$tparams['page'] -= 1;
			$ret['previous_page'] = "?".http_build_query($tparams);
		}
		if($params['page'] < ($ret['total_pages']-1)){
			$tparams = $params;
			$tparams['page'] += 1;
			$ret['next_page'] = "?".http_build_query($tparams);
		}
		$res = $eq->db->exec("SELECT * {$sql_sel} {$sql_from} {$sql_where} {$sql_order} {$sql_limit}", $sql_params);
		$ret['count'] = count($res);
		$ret['total'] = $count[0]['tcount'];
		
	
		
		
		
		foreach($res as $item){
			
			$ret_item = array("uniquip"=>array());
			
			$ret_item['eqID'] = $item["itemU_id"];
			
			foreach($eq->config->uniqupmap as $k=>$v){
				$ret_item['uniquip'][$v] = $item["itemU_f_{$k}"];
			}
			
			$item['loc_text'] = "{$item['loc_lat']} {$item['loc_long']}";
			
			foreach($eq->config->uniqupextramap as $k=>$v){
				$ret_item['uniquip'][$v] = $item["{$k}"];
			}
			
			$ret_item['org'] = "{$item['item_org']}";
			$ret_item['orgID'] = "{$item['org_idscheme']}/{$item['org_id']}";
			$ret_item['orgIDHash'] = md5("{$item['item_org']}");
				
			if(isset($params['geocode'])){
				$ret_item['_Distance'] = $item["distance"];
			}
			
			$ret['results'][] = $ret_item;
		}
		
	
		
		$ret['completed_in'] = microtime(true) - $starttime;
		if($_REQUEST['dev']){
			header('content-type: text/plain');
			print_r($ret);
		}else{
			header('content-type: application/json');
			echo json_encode($ret);	
		}
				
	}
	
}
		
