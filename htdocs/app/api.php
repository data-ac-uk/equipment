<?php
class api {

	function search() {
        $f3=Base::instance();
		
		$params['q'] = $_REQUEST['q'];
		
		$starttime = microtime(true);
		
		if(isset($_REQUEST['page_size']) and (int)$_REQUEST['page_size'] and (int)$_REQUEST['page_size'] < 100){
			$params['page_size'] = (int)$_REQUEST['page_size'];
		}else{
			$params['page_size'] = 10;
		}
		
		
		$ret = array();
	
		
		$eq = $f3->eq;
		$eq->launch_db();
		
		$sql_sel = "";
		
		$sql_from = "FROM itemUniquips
		INNER JOIN `items` ON `itemU_id` = `item_id`
			INNER JOIN `orgs` ON `itemU_org` = `org_uri`
				LEFT OUTER JOIN `locations` ON item_location = loc_uri";	
		
		$sql_where = " WHERE (`itemU_f_name` LIKE ? OR `itemU_f_desc` LIKE ? OR `itemU_f_technique` LIKE ? )";
	
		
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
				
		$sql_params = array(1=>"%{$params['q']}%",2=>"%{$params['q']}%",3=>"%{$params['q']}%");

		
		
		$sql_limit = " LIMIT 3 ";
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
			
			$ret_item = array();
			
			foreach($eq->config->uniqupmap as $k=>$v){
				$ret_item[$v] = $item["itemU_f_{$k}"];
			}
			
			$item['loc_text'] = "{$item['loc_lat']} {$item['loc_long']}";
			
			foreach($eq->config->uniqupextramap as $k=>$v){
				$ret_item[$v] = $item["{$k}"];
			}
			
			$ret_item['_Distance'] = $item["distance"];
			
			$ret['results'][] = $ret_item;
		}
		
		
		$ret['completed_in'] = microtime(true) - $starttime;
	
		header('content-type: application/json');
		echo json_encode($ret);
				
	}
	
}
		
