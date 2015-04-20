<?php
//make connection to database
class currencyCalculator {
	
	private $db;
	
	function __construct(PDO $dbConnection){
		$this->db = $dbConnection;
	}
	/*
	** Function accepts string and returns USD amount
	*/
	public function convertToUsdString($amount) {
		$array = explode(" ", $amount);
		$currency = $array[0];
		$value = $array[1];
		$statement = $this->db->prepare("SELECT rate FROM conversion_rates WHERE currency = :currency");
		$statement->execute(array(':currency' => $currency));
		$rate = $statement->fetchColumn();
		if ($result) {
			$usd = $rate * $value;
			$usd = "USD ".$usd;
			return $usd; 
		}
	}	
	/*
	** Uses above function to convert each array value
	*/
	function convertToUsdArray($amounts) {
		$amountArray = array();
		foreach($amounts as $key => $value){
			$usd = $this->convertToUsdString($value);
			$amountArray[$key] = $usd;
		}//end foreach
		return $amountArray;
	}
	/*
	** Function updates rates in database via api
	*/
	public function updateRates() {
		//get xml from api call
		$url = "https://wikitech.wikimedia.org/wiki/Fundraising/tech/Currency_conversion_sample?ctype=text/xml&action=raw";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 4);
		$data = curl_exec($ch);
		$xml = new SimpleXMLElement($data);
		//loop through conversion rates
		foreach ($xml->conversion as $obj) {
			$currency = $obj->currency;
			$rate = $obj->rate;
			//1. Check if currency is in database
			try {
				$statement = $this->db->prepare("SELECT currency FROM conversion_rates WHERE currency = :currency");
			  $statement->execute(array(':currency' => $currency));
			  $check = $statement->fetch(PDO::FETCH_ASSOC);
			  if (empty($check)) {
			  	//2. If currency isn't in db then insert new row 
			  	$statement = $this->db->prepare("INSERT INTO conversion_rates (currency, rate) VALUES (:currency, :rate)");
			    $statement->execute(array(':currency' => $currency, ':rate' => $rate));
			  } else {
			  	//3. If currency is in database then update the rate
			  	$statement = $this->db->prepare("UPDATE conversion_rates SET rate = :rate WHERE currency = :currency");
			    $statement->execute(array(':currency' => $currency, ':rate' =>$rate));
			  }
			} catch (PDOException $e) {
				//write message to error log and send email to somebody important
				$message = "DataBase Error: ".$e->getMessage();
				error_log("Error with currency rate update: {$message}", 1, "importantperson@wikimedia.com");
			} 
		} //end foreach
	}
}//end class
?>
<?php
	$db = new PDO("mysql:host=localhost;dbname=db;charset=utf8', 'username', 'password'");
	$currencyClass = new currencyCalculator($db);
	//Not sure how we would get this amount
	$currencyClass->updateRates($amount);
?>
/*
//There will need to be something that makes sure that the values based to the converter are the correct type.
//Also the db connection should be defined in a safer place than this file.
//Should also log an php errors
*/
