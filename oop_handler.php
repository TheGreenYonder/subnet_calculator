<?php
### OOP ###

Class Subnet {
	
	private $ip_address;
	private $binary_ip_address;
	private $subnet_mask;
	private $binary_subnet_mask;
	private $hosts;
	
	function set_subnet_mask($mask) {
		$this->subnet_mask = $mask;
		
		$binary_subnet_mask = "";
		for ($i = 0; $i < 32; $i++) {
			if ($subnet_mask > $i) {
				$binary_subnet_mask .= "1";
			} else {
				$binary_subnet_mask .= "0";
			}
		}
	}
	
	function set_ip_address($bin_ip) {
		$this->binary_ip_address = $bin_ip;
}

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
	$input_ip_address = [intval($matches[1][0]),
				intval($matches[2][0]),
				intval($matches[3][0]),
				intval($matches[4][0])];
	
	$input_subnet_mask = intval($matches[5][0]);

	#check if any octet is not between 0 - 255
	for($i = 0, $size = count($input_ip_address) ; $i < $size; $i++) {
		if ($input_ip_address[$i] > 255 || $input_ip_address[$i] < 0) {
			$valid = 0;
		}
	}
	
	#check mask, accept 8 - 30
	if ($input_subnet_mask < 8 || $input_subnet_mask > 30) {
		$valid = 0;
	}
	
	if ($valid) {
		#explode given hosts to array
		$hosts_per_subnet = explode(";", $sub);		
		
		for($i = 0, $size = count($hosts_per_subnet); $i < $size; $i++) {
			#check for any non-digit in hosts
			if (preg_match("/\D+/", $hosts_per_subnet[$i]) || $hosts_per_subnet[$i] == "") {
				$valid = 0;
			} else if ($valid){
                $subnet_masks[$i] = 32 - intval(ceil(log(floatval($hosts[$i] + 2), 2)));
				$hosts_per_subnet[$i] = intval(pow(2, $hostanteil[$i]));			
			}
		}
				
		#check for enough space in given subnet
		if(array_sum($hosts_per_subnet) > pow(2, 32 - $input_subnet_mask)){
			$valid = 0;
		} else {
			#sort high to low
			rsort($hosts_per_subnet);
			rsort($subnet_masks);
		}
		
		#everything's klier, let's subnet
		if ($valid) {
			$next_subnet = sprintf("%08b", $input_ip_address[0]) . sprintf("%08b", $input_ip_address[1]) . sprintf("%08b", $input_ip_address[2]) . sprintf("%08b", $input_ip_address[3]);
			$input_subnet = new Subnet();
			
			
			for ($i = 0, $cnt = count($hosts_per_subnet); $i < $cnt; $i++) {
				$subnet_array[$i] = new Subnet();
				$subnet_array[$i]->set_subnet_mask($subnet_masks);
		
		}
?>
