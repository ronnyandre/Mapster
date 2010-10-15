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
		$static      = $this->EE->TMPL->fetch_param('static');
		$maptype     = $this->EE->TMPL->fetch_param('maptype');
		$marker      = $this->EE->TMPL->fetch_param('marker');
		$markersize  = $this->EE->TMPL->fetch_param('markersize');
		$markercolor = $this->EE->TMPL->fetch_param('markercolor');
		$markerlabel = $this->EE->TMPL->fetch_param('markerlabel');
		
		if ($address == NULL OR $address == '') $address = '1 Infinite Loop, Cupertino, CA, United States';
		if (!preg_match('/^[0-9(.)]{1,4}(px|%|em)$/i', strtolower($width)))  $width = '350px';
		if (!preg_match('/^[0-9(.)]{1,4}(px|%|em)$/i', strtolower($height))) $height = '220px';
		if ($zoom < 1 || $zoom > 20) $zoom = 10;
		if (strtolower($maptype) != ('roadmap' || 'satellite' || 'hybrid' || 'terrain')) $maptype = 'roadmap';
		$maptype = strtoupper($maptype);
		$marker = (strtolower($marker) == 'no' ? FALSE : TRUE);
		
		switch ($static) {
			case 'yes':
				$html = "<img src=\"http://maps.google.com/maps/api/staticmap?center=".urlencode($address)."&amp;zoom={$zoom}&amp;size=".(substr($width, -2) == 'px' ? substr($width, 0, -2) : $width)."x".(substr($height, -2) == 'px' ? substr($height, 0, -2) : $height)."&amp;maptype=".strtolower($maptype).($marker ? "&amp;markers=size:{$markersize}|color:{$markercolor}|label:{$markerlabel}|".urlencode($address) : NULL)."&amp;sensor=false\" alt=\"\" />";
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
      mapTypeId: google.maps.MapTypeId.{$maptype}
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
A simple but very configurable Google Map plugin for ExpressionEngine. The plugin supports both interactive Javascript maps as well as static maps.

=====================================================
Basic Usage
=====================================================

It's very simple to use Mapster. Just use the following code to show an interactive Javascript map:
{exp:mapster address="1 Infinite Loop, Cupertino, CA, United States"}

To show a static map (image) without interaction, just provide the interactive parameter and set the value to no.
{exp:mapster address="1 Infinite Loop, Cupertino, CA, United States" static="yes"}

By default, the map size is set to 350 pixels by 220 pixels, but you can amongst lots of other arguments pass the width and height parameters.


=====================================================
Advanced Usage
=====================================================

Text.


=====================================================
Parameters
=====================================================

address
Default: 1 Infinite Loop, Cupertino, CA, United States
The address of your maps center. Accepts both addresses and coordinates.

width
Default: 350px
Width of your map suffixed with either 'px', '%' or 'em'. Value has to be pixel and maximum size is 640 pixels if static parameter is set to no.

height
Default: 220px
Height of your map suffixed with either 'px', '%', 'em'. Value has to be pixel and maximum size is 640 pixels if static parameter is set to no.

zoom
Default: 10
Zoom level of your map from 1 - 20.

static
Default: no
A yes/no value which defines whether you want the map as an interactive Javascript or static image.

maptype
Default: roadmap
Defines what kind of map you want. Can be either 'roadmap', 'satellite', 'hybrid' or 'terrain'.

marker
Default: yes
Set this to 'no' if you don't want a marker showing the location. Currently only for static maps (interactive maps will show the default marker).

markersize
Default: normal
Size of the marker. Value can be 'tiny', 'small', 'mid' or 'normal'. Currently only for static maps.

markercolor
Default: red
Color of the label. Accepts a set of predefined color: 'black', 'brown', 'green', 'purple', 'yellow', 'blue', 'gray', 'orange', 'red', 'white'. Parameter will also accepts a 24-bit color (ie. '0xFFFFCC'). Currently only for static maps.

markerlabel
Default: dot
Markers label. Value has to be a number from 0-9 or a uppercase letter from A-Z. Label will only show if the size is set to normal or mid. Currently only for static maps.
		<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}

?>