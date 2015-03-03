<?php
class logo {

	function getLogo( )
	{
		
		$f3=Base::instance();
		
		$cacheage = 24*3600; // 1Day
		
		$eq = $f3->eq;	
		$eq->launch_db();
		
		$idtype = $f3->get( "PARAMS.type" );
		$idtype = preg_replace( '/[^-a-z0-9]/i','',$idtype );
		$id = $f3->get( "PARAMS.id" );
	
		if($id != 'none'){
			$org = $eq->db->fetch_one('orgs', array('org_idscheme' => $idtype,'org_id'=>$id));
		}else{
			$org = array('org_logo'=>"{$eq->config->pwd}/htdocs/resources/images/institution.png");
		}
		
		if(!isset($org['org_logo']) || !strlen($org['org_logo'])){
			$f3->error(404);
		}
		
		
		$pic_sub = "data/org/{$idtype}-{$id}.logo";
		$pic_org = "{$pic_sub}.original";
		$pic_full = "{$pic_sub}.full";
		
		if(isset($_REQUEST['nocache']) || !file_exists($pic_org) || filemtime($pic_org) < (time()-$cacheage) ){
			@`rm -f {$pic_sub}.*`;
			copy($org['org_logo'], $pic_org);
		}
		
		if(!file_exists($pic_full)){
			$exec = $eq->config->imagemagick->convert_path." ".escapeshellarg($pic_org)." ".escapeshellarg("png:{$pic_full}");
			@exec($exec);
		}
		
		if(!file_exists($pic_full)){
			$pic_org = "{$eq->config->pwd}/htdocs/resources/images/institution.png";
			$pic_full = "{$eq->config->pwd}/htdocs/resources/images/institution.png";
		}
		
		$sizes = array('small'=>'90x35^>','medium'=>'150x100^>');
		
		if(isset($_REQUEST['size']) && in_array($_REQUEST['size'],array_keys($sizes))){
			$pic_size = "{$pic_sub}.{$_REQUEST['size']}";
			if(!file_exists($pic_size)){
				$exec = $eq->config->imagemagick->convert_path." ".escapeshellarg($pic_org)." -resize ".escapeshellarg($sizes[$_REQUEST['size']])." ".escapeshellarg("png:{$pic_size}");
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
	
		header('max-age: '.$cacheage);
		header('Cache-Control: public');
		header('Pragma: cache');

		header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', filemtime($goimage)));
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheage));
		
		readfile($goimage);
		return $path;
	}
	
}
