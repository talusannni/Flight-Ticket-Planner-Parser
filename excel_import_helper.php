<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* Extract Data from Excel files/Ticket planner */
if ( ! function_exists('tcket_planner_extract')){
	function tcket_planner_extract($excel){
		require_once __DIR__ . '/simplexlsx.class.php';
		if (isset($excel)) {
			if ( $xlsx = SimpleXLSX::parse($excel['tmp_name'])) {
				// output worsheet 1
				list( $num_cols, $num_rows ) = $xlsx->dimension();
				$output = Array();
				$rs = 0;
				$itenary = true;
				foreach ( $xlsx->rows( 1 ) as $r ) {
					$rs++;
					/* 7th row will have Itenary details */
					$r = array_filter($r);
					$r = array_values($r);
					if($rs>6 AND $itenary){
						/* Get Basefare */
						if($r[0]=='Fare'){
							$output['BasePrice'] = $r[$key4+1];
							$itenary=false;
							continue;
						}
						$flight = explode("-",$r[0]);
						$output['Flight'][] = $flight[0]." ".$r[1];
						$output['Departure'][] = str_replace("-", " ",$r[11])." ".substr_replace($r[9],":", 2, -strlen($r[9]));
						$output['Arrival'][] = str_replace("-", " ",$r[3])." ".substr_replace($r[8],":", 2, -strlen($r[8]));
					}
					/* Get Tax */
					if($r[0]=='Tax'){
						$output['Taxes'] = $r[$key1+1];
						continue;
					}
					/* Get Total Price */
					if($r[0]=='Total'){
						$output['TotalPrice'] = $r[$key2+1];
						continue;
					}
					/* Get Baggage */
					if($r[0]=='Baggage'){
						$bags = explode(",", $r[$key3+1]);
						$bag = explode("-", $bags[0]);
						$output['Baggage'] = array_fill(0, count($output['Pax']), $bag[1]);
						continue;
					}
					for ( $i = 0; $i < $num_cols; $i ++ ) {
						$r[$i] = trim($r[$i]);
						if(empty($r[$i]))continue;
						/* get Pax names */
						if (strpos($r[$i], 'Itinerary Details For') === 0) {
							$salutatons = Array("MSTR", "MR", "MRS");
						   $r[$i] = str_replace("Itinerary Details For", "", $r[$i]);
						   $pax = explode(",", $r[$i]);
						   foreach($pax as $key => $val){
							   $val = trim($val);
							   if (strposa($val, $salutatons, 1) === false) {
								   $output['Pax'][$key] = $val;
							   }else{
								   $paxn = explode(" ", $val);
								   $output['Pax'][$key] = $paxn[count($paxn)-1];
								   array_pop($paxn);
								   $output['Pax'][$key] .= " ".implode(" ", $paxn);
							   }
						   }
						   continue;
						}
						/* get PNR */
						if (strpos($r[$i], 'PNRNO') === 0) {
							$pnr = explode(" ", $r[$i]);
							$output['PNR'] = $pnr[1];
							continue;
						}
					}
				}
			} else {
				$output['PNR'] = "500";
			}
		}
		return $output;
	}
	
	function strposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);
	}
?>
