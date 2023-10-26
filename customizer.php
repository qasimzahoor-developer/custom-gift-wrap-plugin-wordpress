<?php 
if(isset($_GET['preview'])){
	  creatPrduct('preview', $postid=NULL); 
}
 if(isset($_GET['upload'])){
	  upload_temp(); 
}  
function upload_temp(){  
	//Test Upload
	if($_FILES['printImage']['size'] > (3*1000000)) return json_encode(['error'=>'true', 'msg'=>'Error: file size must be under 3MB!']);
	if(!in_array($_FILES['printImage']['type'], ['image/png', 'image/jpeg', 'image/jpg'] )) return json_encode(['error'=>'true', 'msg'=>'Error: Only PNG or JPG image allowed']);
	list( $width, $height, $type, $attr ) = getimagesize( $_FILES["printImage"]["tmp_name"] );
	if($width < 1000 AND $height < 1000) return json_encode(['error'=>'true', 'msg'=>'Error: Either image height or image width should be greater then 1000px!']);
		
	$upload_dir = wp_upload_dir();
	$temp_dir = $upload_dir['basedir'].'/giftwrap_temp/'.session_id().'/'.$_POST['cID'].'/';     
	if(!file_exists($temp_dir))	 wp_mkdir_p($temp_dir);  
	$file_url = $upload_dir['baseurl'].'/giftwrap_temp/'.session_id().'/'.$_POST['cID'].'/'.$_FILES['printImage']['name'];
	$file_path = $temp_dir.$_FILES['printImage']['name']; 
	//Upload
	if(isset($_SESSION['printImage'][$_POST['cID']][1]) AND file_exists($_SESSION['printImage'][$_POST['cID']][1])) unlink($_SESSION['printImage'][$_POST['cID']][1]);    
	if(move_uploaded_file( $_FILES['printImage']['tmp_name'], $file_path)){ 
		$_SESSION['printImage'][$_POST['cID']] = array($file_url, $file_path);  
		return json_encode(['file'=>$file_url]);         
	}else{
		return json_encode(['error'=>'true', 'msg'=>'Error: file not uploaded!']);
	}
}   

function creatPrduct($type, $postid){
	global $wpdb;
	$upload_dir = wp_upload_dir();
	if($type == 'save'){ 
		$currentPosts[] = $postid;  
		$savePath = $upload_dir['basedir'].'/giftwrap/products/'.$postid.'/';
	}else{
		$args = ['posts_per_page' => -1,'fields' => 'ids', 'post_type' => 'products', 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => $_POST['cID'] ]]];  
		$currentPosts = get_posts( $args );
		$savePath = $upload_dir['basedir'].'/giftwrap_temp/'.session_id().'/'.$_POST['cID'].'/';     
	} 
	if (!file_exists($savePath)) { mkdir($savePath, 0777, true); }  
	foreach($currentPosts as $post){
		$pimage = $pText1 = $pText2 = NULL;
		$pimage = json_decode(str_replace('\\"', '"', get_post_meta( $post, 'giftwrap_designer_image', true ))); 
		$pText1 = json_decode(str_replace('\\"', '"', get_post_meta( $post, 'giftwrap_designer_text1', true )));
		$pText2 = json_decode(str_replace('\\"', '"', get_post_meta( $post, 'giftwrap_designer_text2', true ))); 

		//print image Path & Text Fonts from Post Save or Preview
		$pimage_url = get_attached_file($pimage->attachement); 
		//Intrup if preview
		if(isset($_POST) AND $type !== 'save'){          
			$postaData = wc_clean($_POST); 
			if(!is_null($pText1)) $pText1->text = sanitize_text_field(esc_sql($_POST['dText1']));
			if(!is_null($pText1)) $pText1->css->fontColor = sanitize_text_field(esc_sql($_POST['dColor1']));
			if(!is_null($pText1)) $pText1->css->fontfamily = sanitize_text_field(esc_sql($_POST['dFamily1'])); 
			if(!is_null($pText2)) $pText2->text = sanitize_text_field(esc_sql($_POST['dText2']));
			if(!is_null($pText2)) $pText2->css->fontColor = sanitize_text_field(esc_sql($_POST['dColor2']));
			if(!is_null($pText2)) $pText2->css->fontfamily = sanitize_text_field(esc_sql($_POST['dFamily2'])); 	 	
			$pimage_url = $_SESSION['printImage'][$_POST['cID']][1];      
			$_SESSION['design'][$_POST['cID']] = array('text1'=>$pText1, 'text2'=>$pText2, 'image'=>$pimage_url); 
			global $wpdb;
			$table_name = $wpdb->prefix . "gftw_cart"; 
			$wpdb->query("UPDATE ".$table_name." SET data= '".json_encode($_SESSION['design'][$_POST['cID']])."' WHERE bundle=".$_POST['cID']."");     
		}
		$font_1 = $wpdb->get_results("select `file` from ".$wpdb->prefix."gftw_fonts WHERE `name` = '".$pText1->css->fontfamily."';", OBJECT);
		$font_2 = $wpdb->get_results("select `file` from ".$wpdb->prefix."gftw_fonts WHERE `name` = '".$pText2->css->fontfamily."';", OBJECT);
		$baseImages = array(); 
		$baseImages[] = get_the_post_thumbnail_url($post);
		$varaints = get_post_meta($post, 'giftwrap_varaints', true);
	 	$varaints =  json_decode($varaints);
		         
		foreach($varaints as $varaint){
			$baseImages[] = $varaint->url;  
		}  
		foreach($baseImages as $image_url){
			$imagePath = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
			list($imageW, $imageH) = getimagesize($imagePath);
			$pImage = gftw_imageCreate($imagePath); 
			imageAlphaBlending($pImage, true);
			imageSaveAlpha($pImage, true);  
			 
			if(!is_null($pimage)) printImage($pImage, $pimage_url, array($imageW, $imageH), $pimage); 
			if(!is_null($pText1)) printText($font_1[0]->file, $pImage, array($imageW, $imageH), $pText1);
			if(!is_null($pText2)) printText($font_2[0]->file, $pImage, array($imageW, $imageH), $pText2);
			
			gftw_imageSave($imagePath, $savePath, $pImage);
			imagedestroy($pImage);
		}  

	} 
}

function printImage($Source, $scPath, $SourceWH=array(), $imageObject){
	$_width = ceil(abs($SourceWH[0]/$imageObject->css->imageWidth)*$imageObject->css->width);
	$_left = ceil(abs($SourceWH[0]/$imageObject->css->imageWidth)*($imageObject->css->left+3)); 
	$_top = ceil(abs($SourceWH[1]/$imageObject->css->imageHeight)*($imageObject->css->top+3));
	$printImage = gftw_imageCreate($scPath);
	imageAlphaBlending($printImage, true);
    imageSaveAlpha($printImage, true);
	$printImage_width = imagesx($printImage);
	$printImage_height = imagesy($printImage);
	$_height = ceil(($printImage_height * $_width) / $printImage_width);
	$_x = ($printImage_width/2) - ($water_width/2);
	$_y = ($printImage_height/2) - ($water_height/2);
	$newprintImage = imagecreatetruecolor($_width, $_height);
	imageAlphaBlending($newprintImage, false); 
    imageSaveAlpha($newprintImage, true);
	imagecopyresampled($newprintImage, $printImage, 0, 0, 0, 0, $_width, $_height, $printImage_width, $printImage_height);
	imagecopy($Source, $newprintImage, $_left, $_top, 0, 0, $_width, $_height);
	imagedestroy($printImage); imagedestroy($newprintImage);    
}

function gftw_imageCreate($source){
	$ext = pathinfo($source, PATHINFO_EXTENSION);
	switch($ext) {
      case 'gif': 
       $image = imagecreatefromgif($source);
       break;
      case 'jpg':
       $image = imagecreatefromjpeg($source);
       break;
      case 'png':
        $image = imagecreatefrompng($source);
         break;
      }
	return $image;
}
function gftw_imageSave($source, $destination, $objects){
	$ext = pathinfo($source, PATHINFO_EXTENSION);
	$name = pathinfo($source, PATHINFO_FILENAME);
	switch($ext) {
      case 'gif': 
       		imagegif($objects, $destination.$name.'.'.$ext);
       break;
      case 'jpg':
      		imagejpeg($objects, $destination.$name.'.'.$ext);
       break;
      case 'png':
      		imagepng($objects, $destination.$name.'.'.$ext); 
         break;
      }
}
function printText($fontFile, $Source, $SourceWH=array(), $textObject){
	
	$d_text_color = hex2rgb($textObject->css->fontColor); 
	$d_text_font = ($fontFile); 
	//Text position
	$_text1_font_size = ceil(abs($SourceWH[0]/$textObject->css->imageWidth)*(($textObject->css->fontSize*3)/4)); 
	$_text1_left = ceil(abs($SourceWH[0]/$textObject->css->imageWidth)*($textObject->css->left)); 
	$_text1_top = ceil(abs($SourceWH[1]/$textObject->css->imageHeight)*($textObject->css->top)+10);
	$color = imagecolorallocate($Source, $d_text_color['red'], $d_text_color['green'], $d_text_color['blue']);
	$box = imagettfbbox($_text1_font_size, 0, $d_text_font, $textObject->text);
	$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
	$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );  
	$left   = abs( $min_x ) + $_text1_left;
  	$top    = abs( $min_y ) + $_text1_top;
	imagettftext($Source, $_text1_font_size, 0, $left, $top, $color, $d_text_font, $textObject->text);
}

function hex2rgb( $colour ) {
        if ( $colour[0] == '#' ) {
                $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
                return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

?>