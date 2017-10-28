<?php
if (isset($_POST)) {
    $ip = $_POST['ip'];
    $sub = $_POST['sub'];

    $re = '/(^\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2}$)/';

    #if regex fails to match, $valid is 0 = false
    $valid = preg_match_all($re, $ip, $matches);
} else {
    $valid = 0;
}

if ($valid){
	#array to store every octet as int - preg_match_all returns 2D array to $matches
	$address = [intval($matches[1][0]),
				intval($matches[2][0]),
				intval($matches[3][0]),
				intval($matches[4][0]),
		"mask" => intval($matches[5][0]),
    ];

	#check if any octet is not between 0 - 255
	for($i = 0, $size = count($address) -1; $i < $size; ++$i) {
		if ($address[$i] > 255 || $address[$i] < 0) {
			$valid = 0;
		}
	}
	
	#check mask, accept 1 - 30
	if ($address["mask"] < 1 || $address["mask"] > 30) {
		$valid = 0;
	}
	
	if ($valid) {
		#explode given hosts to array
		$hosts = explode(";", $sub);		
		
		for($i = 0, $size = count($hosts); $i < $size; $i++) {
			#check for any non-digit in hosts
			if (preg_match("/\D+/", $hosts[$i]) || $hosts[$i] == "") {
				$valid = 0;
			} else if ($valid){
                $hostanteil[$i] = intval(ceil(log(floatval($hosts[$i] + 2), 2)));
				$hosts[$i] = intval(pow(2, $hostanteil[$i]));			
			}
		}
		
		#check for enough space in given subnet
		if(array_sum($hosts) > pow(2, 32 - $address["mask"])){
					$valid = 0;
		} else {
			#sort high to low
			rsort($hosts);
			rsort($hostanteil);
		}
		
		#everything's klier, let's subnet
		if ($valid) {			
			
			#create array for subnet masks
			$tmp = "";
			
			#format octett to one complete binary address string
			for ($i = 0; $i < 4; $i++) {
				$tmp .= sprintf("%08b", $address[$i]);
			}
			
			#array for storing network address and broadcast address 
			$bin_ip[0] = [$tmp, $tmp];
			
			for ($i = 0, $cnt = count($hostanteil); $i < $cnt; $i++) {								
				if ($i == 0) {
					$tmp1 = "";
					$tmp2 = "";
					
					for ($k = 0; $k < 32; $k++) {
						if ($address["mask"] > $k) {
							$tmp1 .= "1";
							$tmp2 .= "0";
						} else {
							$tmp1 .= "0";
							$tmp2 .= "1";
						}
					}
					$bin_mask[$i] = [$tmp1, $tmp2];
				}
				
				$tmp1 = "";
				$tmp2 = "";
								
				for ($k = 0; $k < 32; $k++) {					
					if (32 - $hostanteil[$i] > $k) {
						$tmp1 .= "1";
						$tmp2 .= "0";
					} else {
						$tmp1 .= "0";
						$tmp2 .= "1";
					}
				}
				
				$bin_mask[$i+1] = [$tmp1, $tmp2];					
			}
			
			$bin_ip[0][0] = $bin_ip[0][0] & $bin_mask[0][0];
			$bin_ip[0][1] = $bin_ip[0][1] | $bin_mask[0][1];
			$tmp = $bin_ip[0][0];
			
			for($i = 1, $cnt = count($hostanteil)+1; $i < $cnt; $i++) {
				$z = 0;
				$bin_ip[$i][0] = $tmp & $bin_mask[$i][0];
				$bin_ip[$i][1] = $tmp | $bin_mask[$i][1];
				
				for ($k = 0; $k < 32; $k++) {
					if ($tmp[31 - $z - $hostanteil[$i-1]] == '1') {
						$tmp[31- $z - $hostanteil[$i-1]] = '0';
						$z++;
					} else {
						$tmp[31- $z - $hostanteil[$i-1]] = '1';
						$k = 32;
					}
				}
			}
			
			 for($i = 0, $cnt = count($hostanteil); $i < $cnt; $i++) {
				$offset = 0;

				for ($k = 0; $k < 4; $k++) {
					$arr[$i][$k] = bindec(substr($bin_ip[$i+1][0], $offset, 8));
					$broad[$i][$k] = bindec(substr($bin_ip[$i+1][1], $offset, 8));
					$offset = $offset + 8;
				}
	
				$nmbrs[$i] = implode(".", $arr[$i]) . "/" . strval(32-$hostanteil[$i]);
				$brc_n[$i] = implode(".", $broad[$i]) . "/" . strval(32-$hostanteil[$i]);
			}

			echo "<table width = \"800\" border = 1>
					<tr>	<th>Subnetze</th>
							<th>Hosts</th>
							<th>Anzahl</th>
							<th>Broadcast</th>
							<th>Binär</th>	</tr>";
			
			for ($i = 0, $cnt = count($nmbrs); $i < $cnt; $i++) {
				$anzahl = $hosts[$i] - 2;
				$host[0] = $arr[$i][3] + 1;
				$host[1] = bindec(substr($bin_ip[$i+1][1], -8, 8)) - 1;

				echo "<tr>	<td align = \"center\">{$nmbrs[$i]}</td>
							<td align = \"center\">{$host[0]} - {$host[1]}</td>
							<td align = \"center\">{$anzahl}</td>
							<td align = \"center\">{$brc_n[$i]}</td>
							<td align = \"center\">{$bin_ip[$i+1][0]}</td>";
			}
	
			echo "</table>";
		}
	} 
}

if (!$valid){
	echo "wrong";
}

?>
