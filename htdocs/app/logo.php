<?php
class logo {

	function getLogo( )
	{
		
		$f3=Base::instance();
			
		$eq = $f3->eq;	
		$eq->launch_db();
		
		$idtype = $f3->get( "PARAMS.type" );
		$idtype = preg_replace( '/[^-a-z0-9]/i','',$idtype );
		$id = $f3->get( "PARAMS.id" );
	
	
		$org = $eq->db->fetch_one('orgs', array('org_idscheme' => $idtype,'org_id'=>$id));
			
		if(!isset($org['org_logo']) || !strlen($org['org_logo'])){
			$f3->error(404);
		}
		
		
		$pic_sub = "data/org/{$idtype}-{$id}.logo";
		$pic_org = "{$pic_sub}.original";
		$pic_full = "{$pic_sub}.full";
		
		if(!file_exists($pic_org) || filemtime($pic_org) < strtotime("-2 Weeks") ){
			@`rm -f {$pic_sub}.*`;
			copy($org['org_logo'], $pic_org);
		}
		
		if(!file_exists($pic_full)){
			$exec = "convert ".escapeshellarg($pic_org)." ".escapeshellarg("png:{$pic_full}");
			@exec($exec);
		}
		
		$sizes = array('small'=>'90x35^>','medium'=>'150x100^>');
		
		if(isset($_REQUEST['size']) && in_array($_REQUEST['size'],array_keys($sizes))){
			$pic_size = "{$pic_sub}.{$_REQUEST['size']}";
			if(!file_exists($pic_size)){
				$exec = "convert ".escapeshellarg($pic_org)." -resize ".escapeshellarg($sizes[$_REQUEST['size']])." ".escapeshellarg("png:{$pic_size}");
				@exec($exec);
			}

			$goimage = $pic_size;
		}else{
			$goimage = $pic_org;
		}
		
		if(!isset($goimage)){
			$f3->error(404);
		}
		
		header('Content-Type: image/png');
		readfile($goimage);
		return $path;
	}
	
}
