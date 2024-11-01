<?php
if(isset($_POST["submit"]))
{ 
    $address1	=  sanitize_text_field($_POST['address1']);
    $address2	=  sanitize_text_field($_POST['address2']);
	$city		=  sanitize_text_field($_POST['city']);
    $state		=  sanitize_text_field($_POST['state']);
    $country	=  sanitize_text_field($_POST['country']);
    $pincode	=  sanitize_text_field($_POST['pincode']);
	$width		=  sanitize_text_field($_POST['map_width']);
	$height		=  sanitize_text_field($_POST['map_height']);
	$type		=  sanitize_text_field($_POST['type']);
	$zoom		=  sanitize_text_field($_POST['zoom']);
	
	//Validations
	$msg	=	null;
	$arr_type	=	array('m','k','h','p');
	$arr_zoom	=	array(10,11,12,13,14,15,16,17,18,19,20);
	
	
    if(empty($address1 ) || empty($address2) || empty($city) || empty($state) || empty($country) || empty($pincode) || empty($width) || empty($height))
	{
        $msg	= '<div id="error" align="center" class="error">All fields are mandatory</div>';
    }
	else if( preg_match('/[^a-zA-Z]/', $city) ||  preg_match('/[^a-zA-Z]/', $state) || preg_match('/[^a-zA-Z]/', $country))
	{
		$msg	= '<div id="error" align="center" class="error">Enter Only Alphabets for (city,state,country) <b>Ex: Chennai</b></div>';
	}
	else if( preg_match('/[^0-9]/', $width) || preg_match('/[^0-9]/', $height) || preg_match('/[^0-9]/', $pincode))
	{
		$msg	= '<div id="error" align="center" class="error">Enter Only Numeric values for Width & Height & Pincode</div>';
	}
	else if(! in_array($type, $arr_type))
	{
		$msg	= '<div id="error" align="center" class="error">Invalid Map Type Selected</div>';
	}
	else if(! in_array($zoom, $arr_zoom))
	{
		$msg	= '<div id="error" align="center" class="error">Invalid Zoom Selected</div>';
	}
    else
	{
		$geo_loc		=	sgcm_generateLatLong($address1, $address2, $city, $state, $country, $pincode);
		$map_options	=	array(
							'address1'			=>  $address1,
							'address2'			=>  $address2,
							'city'				=>  $city,
							'state'				=>  $state,
							'country'			=>  $country,
							'pincode'			=>  $pincode,
							'map_width'			=>  $width,
							'map_height'		=>  $height,
							'map_type'			=>  $type,
							'map_zoom'			=>  $zoom,
							'mapinfo_text'		=>  $info,
							'map_lat'			=>	$geo_loc['lat'],
							'map_long'			=>	$geo_loc['long'],
							'formated_address'	=>	$geo_loc['address']
						);
	
		update_option('sgcm_options', serialize($map_options));
	
		$msg	= '<div id="success" align="center" class="update-nag">Map Options Updated</div>';
    }
}


function sgcm_generateLatLong($address1, $address2, $city, $state, $country, $pincode)
{
	if($_SERVER['SERVER_PROTOCOL']=='https')
	 {
		$protocal	=	'https://';
	 }
	 else
	 {
		$protocal	=	'http://';
	 }
	
	$format_address		=	$address1.' '.$address2.' '.$city.' '.$state.' '.$country.' '.$pincode;
	
	$address			=	urlencode($format_address); 
	 
	$jsonURL			=	$protocal."maps.googleapis.com/maps/api/geocode/json?address=".$address."&sensor=false";   
	
	$geocurl			=	curl_init();  
  
	curl_setopt($geocurl, CURLOPT_URL, $jsonURL);  
	curl_setopt($geocurl, CURLOPT_HEADER, 0);  
	curl_setopt($geocurl, CURLOPT_FOLLOWLOCATION, 1);  
	curl_setopt($geocurl, CURLOPT_RETURNTRANSFER, 1);  
  
	$exec				= curl_exec($geocurl);  
	$data				= json_decode($exec,true);  
	
	$info = curl_getinfo($geocurl);
	
	if($data['status']	== 'OK')
	{
		$latitude			= $data['results'][0]['geometry']['location']['lat'];  
		$longitude			= $data['results'][0]['geometry']['location']['lng'];
		$formated_adddress	= $data['results'][0]['formatted_address'];	
	}
	
	$geo_options		=	array(
							'lat'		=>	$latitude,
							'long'		=>	$longitude,
							'address'	=>	$formated_adddress
							);
	return $geo_options;
}

$map_info				= unserialize(get_option('sgcm_options'));

?>
<div class="sgcmwrap">
    <?php screen_icon(); ?>
    <h2>Simple Contact Map options Page</h2>
    <br />
   
    <div>
    
        <fieldset>
            
            <form id="sgcmform" method="post" action=""> 
            <?php echo $msg; ?>
                <h3>Fill exact Address Correctly</h3>  
				
				<label>Address Line 1 <b>(Street Address)</b>&nbsp;&nbsp;<b>Ex: Fourth main road cit nagar</b></label>
                <input type="text" name="address1" value="<?php echo $map_info['address1'];?>">
                <br /><br />
				<label>Address Line 2 <b>(Area)</b>&nbsp;&nbsp;Ex: Nandhanam </label>
                <input type="text" name="address2" value="<?php echo $map_info['address2']; ?>">
                <br /><br />
				<label>City</label>
                <input type="text" name="city" value="<?php echo $map_info['city'];?>">
                <br /><br />
				<label>State</label>
                <input type="text" name="state" value="<?php echo $map_info['state'];?>">
                <br /><br />
				<label>Country</label>
                <input type="text" name="country" value="<?php echo $map_info['country'];?>">
                <br /><br />
				<label>Pincode</label>
                <input type="text" name="pincode" value="<?php echo $map_info['pincode'] ;?>">
                <br /><br />
				<label>Map Width</label>
                <input type="text" name="map_width" value="<?php echo $map_info['map_width'];?>">
                <br /><br />
				<label>Map Height</label>
                <input type="text" name="map_height" value="<?php echo $map_info['map_height'];?>">
                <br /><br />
				<label>Map Type</label>
                <select name="type">
					<option value="m" <?php if($map_info['map_type']=='m'){ ?> selected="selected" <?php } ?>>ROADMAP</option>
					<option value="k" <?php if($map_info['map_type']=='k'){ ?> selected="selected" <?php } ?>>SATELLITE</option>
					<option value="h" <?php if($map_info['map_type']=='h'){ ?> selected="selected" <?php } ?>>HYBRID</option>
					<option value="p" <?php if($map_info['map_type']=='p'){ ?> selected="selected" <?php } ?>>TERRAIN</option>
				</select>
                <br /><br />
				<label>Zoom Level</label>
                <select name="zoom">
					<?php
					for($z=10; $z<=20; $z++)
					{
					?>
					<option value="<?php echo $z; ?>" <?php if($z==$map_info['map_zoom']){ ?> selected="selected" <?php } ?>><?php echo $z; ?></option>
					<?php
					}
					?>
				</select>
                <br /><br />
				
				<p>
                    <input type="submit" value="Save" class="button button-primary" name="submit" />
                </p>
            </form>
			
			<div id="instruction">
			
			<h2>Instruction For Contact Map</h2>
            <p>
			Fill Form Fields And Save.<br>
			Add Follwing Shortcode For Your Page, Post<br><br>
			<span>[sgcm_map src='sgcm']</span>
            
            <a href="http://www.phpboys.in/simple-google-contact-map-plugin-wordpress.html" target="_blank">View Doc</a>
            
            <h2>Widget</h2>
            Go to Appearence -> Widgets <br />
            
            Find <b>Googlemap-SGCM</b> and drag widget to your sidebar
            <a href="http://www.phpboys.in/simple-google-contact-map-plugin-wordpress.html" target="_blank">View Doc</a>
			</p>
		</div>	
			
        </fieldset>   
	 </div>

   
    
