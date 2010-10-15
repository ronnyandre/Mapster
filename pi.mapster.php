<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name'        => 'Mapster',
	'pi_version'     => '1.0',
	'pi_author'      => 'Ronny-André Bendiksen',
	'pi_author_url'  => 'http://ronny-andre.no',
	'pi_description' => 'Allows you to easily add both static and dynamic Google Maps to your content',
	'pi_usage'       => Mapster::usage()
);

class Mapster {
	
	var $return_data = '';
	
	function Mapster() {
		$this->EE =& get_instance();
		
		$address     = $this->EE->TMPL->fetch_param('address');
		$width       = $this->EE->TMPL->fetch_param('width');
		$height      = $this->EE->TMPL->fetch_param('height');
		(int) $zoom  = $this->EE->TMPL->fetch_param('zoom');
		$interactive = $this->EE->TMPL->fetch_param('interactive');
		$map         = $this->EE->TMPL->fetch_param('map');
		$marker      = $this->EE->TMPL->fetch_param('marker');
		$markersize  = $this->EE->TMPL->fetch_param('markersize');
		$markercolor = $this->EE->TMPL->fetch_param('markercolor');
		$markerlabel = $this->EE->TMPL->fetch_param('markerlabel');
		
		if ($address == NULL OR $address == '') $address = '1 Infinite Loop, Cupertino, CA, United States';
		if (!preg_match('/^[0-9(.)]{1,4}(px|%|em)$/i', strtolower($width)))  $width = '350px';
		if (!preg_match('/^[0-9(.)]{1,4}(px|%|em)$/i', strtolower($height))) $height = '220px';
		if ($zoom < 1 || $zoom > 20) $zoom = 10;
		if (strtolower($map) != ('roadmap' || 'satellite' || 'hybrid' || 'terrain')) $map = 'roadmap';
		$map = strtoupper($map);
		$marker = (strtolower($marker) == 'no' ? FALSE : TRUE);
		
		switch ($interactive) {
			case 'no':
				$html = "<img src=\"http://maps.google.com/maps/api/staticmap?center=".urlencode($address)."&amp;zoom={$zoom}&amp;size=".(substr($width, -2) == 'px' ? substr($width, 0, -2) : $width)."x".(substr($height, -2) == 'px' ? substr($height, 0, -2) : $height)."&amp;maptype=".strtolower($map).($marker ? "&amp;markers=size:{$markersize}|color:{$markercolor}|label:{$markerlabel}|".urlencode($address) : NULL)."&amp;sensor=false\" alt=\"\" />";
				break;
			
			default:
				$html = <<<HTML
<div id="map_canvas" style="width: {$width}; height: {$height}"></div>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
	var geocoder;
	var map;

    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(0, 0);
	
    var myOptions = {
      zoom: {$zoom},
      center: latlng,
      mapTypeId: google.maps.MapTypeId.{$map}
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

		var address = '{$address}';
		geocoder.geocode({'address': address}, function(results, status){
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
					var marker = new google.maps.Marker({
						map: map,
						position: results[0].geometry.location
					});
			} else {
				console.log("Geocode was not successful for the following reason: " + status);
			}
		});

</script>
HTML;
				break;
		}
		
		$this->return_data = $html;
	}
	
	function usage() {
		ob_start() ?>
		This is where the documentation goes.
		<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}

?>