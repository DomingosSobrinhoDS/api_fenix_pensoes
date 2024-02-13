<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Email;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\EmailController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;


class SimuladorController extends Controller
{
    public $email;
    public function __construct(){
        $this->email = new EmailController();
    }

    function enviar(Request $request)
    {   
        if(env('API_TOKEN') == $request->token ){
            $response=$this->email->email_simulador($request->email,$request->link);
            return json_encode($response);
        }else{
          return json_encode('Acesso Negado');  
        }
    }

    function teste() {
        

        $numero = 123456; // Seu número de 6 dígitos
        $chave = 256;
        
        $numeroCriptografado = Crypt::encryptString($numero, $chave);
        
        return $numeroCriptografado;
                    
        } 
        

    
    
     
    
}
