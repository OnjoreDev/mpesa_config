<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaController extends Controller
{
    //function that authorizes the process

    public function generateToken(){
        $consumer_key = "l587aO4ly3GwqlOnxA4mXZQAjFTCMDAHCNAuXNlPa6j71buj";
        $consumer_secret = "OEiE9cBQHGBBsN6J2uXJyt3xImIpX1GyGGpX92nQYpdvNO6TbuaoGokWCnYM09yW";

        $accesss_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        # header for access token
        $headers = ['Content-Type:application/json; charset=utf8'];

        #variable to hold the access token sandbox url
        $curl = curl_init($accesss_token_url);
        
        #curl setup
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $consumer_key.':'.$consumer_secret);

        $response = curl_exec($curl);

        #convert json object to php object
        $result = json_decode($response);
        $access_token  = $result->access_token;
        curl_close($curl);
        //return the access token
        return $access_token;
    }

    //
    public function stk_push(Request $req){
        $input = $req->validate(
            [
                "amount"=>'required',
                "phone_number"=>"required",
            ]
        );
          //get authorization 
         $access_token = $this->generateToken();

         //define a constant value for the callback url
         define('CALLBACK_URL', 'https://willy.itn.co.ke/callback.php');

         $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
         #Definition and assignment of variable values
         $BusinessShortCode = '174379';//
         $Passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
         $phone = preg_replace('/^0/', '254', str_replace("+", "", $input["phone_number"]));
         $PartyA = $phone; // This is your phone number,
         $PartyB = '174379'; //Till number or paybill number
         $TransactionDesc = 'Pay Order'; //Insert your own description
         # Get the timestamp, format YYYYmmddhms -> 20181004151020
         $Timestamp = date('YmdHis');  
         # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
         $Password = base64_encode($BusinessShortCode.$Passkey.$Timestamp);

         # header for stk push
         $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

         curl_setopt($ch, CURLOPT_HTTPHEADER, $stkheader);

         //post data that will be sent as payload
         $curl_post_data = [
            "BusinessShortCode"=>$BusinessShortCode,
            "Password"=> $Password,
            "Timestamp"=> $Timestamp,
            "TransactionType"=> "CustomerPayBillOnline",
            "Amount"=> 1,
            "PartyA"=>$PartyA,
            "PartyB"=>$PartyB,
            "PhoneNumber"=>$phone,
            "CallBackURL"=> CALLBACK_URL,
            "AccountReference"=> "Account 1",
            "TransactionDesc"=> $TransactionDesc  
         ];

         //convert the paybload to json
         $data_string = json_encode($curl_post_data);
         
         curl_setopt($ch, CURLOPT_POST, true);
         
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
         
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
         $response     = curl_exec($ch);

         echo $response;
         //convert curl json response to php array
         $res = (array) json_decode($response);

         curl_close($ch);
         
         //return the response code from safaricom
         $ResponseCode = $res['ResponseCode'];
         return $ResponseCode;
    }

    public function registerUrls(){
        //function to get the access token 
        $access_token = $this->generateToken();
        
        //url to register urls on safaricom side
        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v2/registerurl';
        $shortCode = '174379'; // provide the short code obtained from your test credentials
        $confirmationUrl = 'https://willy.itn.co.ke/confirmation.php'; // path to your confirmation url. must be IP address that is publicly accessible or a url
        $validationUrl = 'https://willy.itn.co.ke/validation.php'; // path to your validation url. must be IP address that is publicly accessible or a url
        
        //authorization header for registering the url
        $register_url_header = ['Content-Type:application/json','Authorization:Bearer '.$access_token];
        //initialize the curl request

        $curl = curl_init($url);
	    // curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $register_url_header); //setting custom header

        $curl_post_data = array(
            //Fill in the request parameters with valid values
            'ShortCode' => $shortCode,
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl
          );

          $data_string = json_encode($curl_post_data);

	      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	      curl_setopt($curl, CURLOPT_POST, true);
	      curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

	      $curl_response = curl_exec($curl);

          //convert code into php array
          $result = json_decode($curl_response);
         
          curl_close($curl);

	      return $result;
    }

    //code to simulate c2b transaction
    public function c2b(Request $req){
        //call the register urls
        //$this->registerUrls();
       //validate the input
       $input = $req->validate([
        "amount"=>'required',
        // "account"=>"required",
       ]);

       //function to get the access token 
       $access_token = $this->generateToken();

       #c2b simulate url
       $url = "https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate";
       # initiate curl request
       $curl = curl_init($url);

       # c2b for stk push
       $c2bheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

       curl_setopt($curl, CURLOPT_HTTPHEADER, $c2bheader);

       curl_setopt($curl, CURLOPT_POST, true);

       $c2b_payload = [
        "ShortCode"=>600426,
        "CommandID"=>"CustomerPayBillOnline",
        "Amount"=>$req->amount,
        "BillRefNumber"=>"174379",
        "Msisdn"=>"254705912645",
    ];
      
       //convert the payload to json
       $data_string = json_encode($c2b_payload);

       //values sent when post request is sent
       curl_setopt($curl,CURLOPT_POSTFIELDS,$data_string);

       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

       $response = curl_exec($curl);

       curl_close($curl);

       return $response;

    }
}
