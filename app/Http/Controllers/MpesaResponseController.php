<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MpesaResponseController extends Controller
{
    //obtain responses from safaricom 
    //obtain response from confirmation url
     public function confirmation(){
        //retrieve response from the created json file
        $confirmation_data = file_get_contents("https://willy.itn.co.ke/confirmationresponse.json");
        return $confirmation_data;
     }

    //obtain response from validation url
    public function validation(){
        //retrieve the response from the created json file
        $validation_data = file_get_contents("https://willy.itn.co.ke/validationresponse.json");
        return $validation_data;

    }

    //obtain response from stkpush 
    public function stkPush(){
        //retrieve the response from the created json file 
        $stkdata = file_get_contents("https://willy.itn.co.ke/callbackresponse.json");
        return $stkdata;
    }

}
