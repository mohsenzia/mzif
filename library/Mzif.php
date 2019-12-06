<?php

class Mzif {

    static function createUrl($url="",$gets=array()){
        $url=ltrim($url,'/');

        if(count($gets)>0) {
            $url .= "?";
            foreach ($gets as $name=>$value) {
                $url.=$name.'='.$value.'&';
            }
            $url=rtrim($url,'&');
        }

        return BASE_PATH.'/'.$url;
    }
	
	static function redirect($url) {
		header('location:'.$url);
		die;
	}

    static function exception($code,$msg){
        throw new Exception("#$code:".$msg,$code);
        die();
    }
}
?>