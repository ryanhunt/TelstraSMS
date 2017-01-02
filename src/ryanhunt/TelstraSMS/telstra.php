<?php 

namespace ryanhunt\TelstraSMS;

class TelstraSMS {
   
    private $baseURL = "https://api.telstra.com/v1";
    private $userAgent = "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0";
    
    private $key;
    private $secret;
    private $token;
    
    private $quota;
        
    public function __construct($key, $secret) {
        $this->token = $this->getToken($key, $secret);
        $this->quota = new Quota();
    }
    
    private function getToken ($key, $secret) {
    
    	/*
    	Returns: 
    	Array
    	(
    	    [access_token] => d1GBOeQpPQYyhF2AxhhKB5CGPAjf
    	    [expires_in] => 3599
    	)
    	*/
    
    	$url = $this->baseURL . "/oauth/token";
    
    	$headers = [
    	    'Content-Type: application/x-www-form-urlencoded',
    	    $this->userAgent
    	];
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=" . $key . "&client_secret=" . $secret . "&grant_type=client_credentials&scope=SMS");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	
    	$token = json_decode(curl_exec ($ch), true);
    	curl_close($ch);
    	
    	return $token['access_token'];
    }
    
    private function updateQuota() {
    
        // potentially will be deprecated one day
        
        /*
        this returns:
        
        Array
        (
            [used] => 1
            [available] => 999
            [expiry] => 2016-11-29T11:00:00+11:00
        )
        
        */
        $url = $this->baseURL . "/sms/quota";
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
            $this->userAgent
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = json_decode(curl_exec ($ch), true);
        curl_close($ch);
        
        $this->quota->used = $response['used'];
        $this->quota->available = $response['available'];
        $this->quota->expiry = $response['expiry'];        
        $this->quota->total = $this->quota->used + $this->quota->available;
        
        return $response;
    }
    
    public function getQuota() {
        $this->updateQuota();
        return $this->quota;
    }
    
    public function sendMessage($recipient, $message) {
    
    	/*
    	Returns:
    	
    	Array
    	(
    	    [messageId] => 2204DB8CB23CBA57C706A4C29FCB7E1F
    	)
    	
    	*/
    
    	$url = $this->baseURL . "/sms/messages";
    	
    	$headers = [
    	    'Content-Type: application/json',
    	    'Authorization: Bearer ' . $this->token,
    	    $this->userAgent
    	];
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"to\":\"" . $recipient . "\", \"body\":\"" . $message . "\"}");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	
    	$response = json_decode(curl_exec ($ch), true);
    	curl_close($ch);
    	
    	return $response['messageId'];
    }

    public function messageStatus($id) {
    
        /*
        response:
        
        (
            [to] => 614XXXXXXXX
            [receivedTimestamp] => 
            [sentTimestamp] => 2016-10-31T00:41:51+11:00
            [status] => SENT
        )
        
        */
    
    	$url = $this->baseURL . "/sms/messages" . "/" . $id;
    	
    	$headers = [
    	    'Content-Type: application/json',
    	    'Authorization: Bearer ' . $this->token,
    	    $this->userAgent
    	];
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	
    	$response = json_decode(curl_exec ($ch), true);
    	curl_close($ch);
    	
    	return $response;
    }
    

}
       
class Quota {
    public $used;
    public $total;
    public $available;
    public $expiry;
}

?>
