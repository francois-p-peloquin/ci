<?php class CSS extends Model {

	function sample_use($cert,$schools) {
		$product = '';
		foreach ($cert as $k => $v) {
			$c = $v['color'];

			$c_25 = $this->css->hex_to_rgb($c);
			$c_25 = $this->css->rgb_color_blend($c_25,0.75);
			$c_25 = $this->css->rgb_to_hex($c_25);
			
			$c_rgb = $this->css->hex_to_rgb($c);
			$product .= '
			.ws_schedule.cert_'.$k.' th {
				background-color:#'.$c.';
			}
			.ws_schedule.cert_'.$k.' .break, .ws_schedule.cert_'.$k.' .time {
				background-color:#'.$c_50.';
			}
			.ws_schedule.cert_'.$k.' .time.light {
				background-color:#'.$c_25.';
			}';
		}
		// echo $product;
		return $product;
	}
	
	function hex_to_rgb($hex) {
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		return array($r, $g, $b);
	}
	
	function rgb_to_hex($in) {
		$r = $in[0];
		$g = $in[1];
		$b = $in[2];
		
		$r = dechex($r);
		if (strlen($r) < 2) $r = '0'.$r;

		$g = dechex($g);
		if (strlen($g) < 2)	$g = '0'.$g;

		$b = dechex($b);
		if (strlen($b) < 2)	$b = '0'.$b;

		return $r.$g.$b;
	}
	
	function rgb_color_blend($rgb1 = array(0,0,0),$p = 0.1,$rgb2 = array(255,255,255)) { //can be for opacity
		return array(
			round((1 - $p) * $rgb1[0] + $p * $rgb2[0]), 
			round((1 - $p) * $rgb1[1] + $p * $rgb2[1]), 
			round((1 - $p) * $rgb1[2] + $p * $rgb2[2]),
		);
	}
}