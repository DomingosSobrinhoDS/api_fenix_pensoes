<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Email;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\Api_functionsController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;





class PortalController extends Controller
{
    public $email;
    public $api;
    public function __construct(){
        $this->email = new EmailController();
        $this->api = new Api_functionsController();
    }

    function first_log(Request $request) {
        try {
            if(env('API_TOKEN') == $request->token ){
                $verify=$this->api->verify_user($request->email,$request->bilhete);
                if ($verify['status']==1) {
                    return response()->json([
                        "msg"=> "Autenticacao feita com sucesso.",
                    ],200);
                    //return json_encode($aux);
                }else {
                    return response()->json([
                        'msg' => 'Dados de login incorretos'
                    ],400);
                }
            }else{
                return response()->json([
                    'error' => 'Acesso negado'
                ],402);
            }
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    function enviar(Request $request)
    {   

        //chamar a verify_user
        if(env('API_TOKEN') == $request->token ){
                $aux=$this->set($request->email);
                return response()->json([
                    $aux
                ],200);
                //return json_encode($aux);
            }else{
                return response()->json([
                    'error' => 'Acesso negado'
                ],402);
            }
    }

    function teste() {

        
        

        /*$response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
          ])->get('https://jsonplaceholder.typicode.com/posts/1');
  
          //return response()->json($response->body());
  
          if ($response->successful()) {
            $data = $response->json(); // Retorna os dados como um array
            // Faça algo com os dados recebidos da API
            return response()->json($data);
          } else {
            // Lida com erros
            $errorCode = $response->status();
            return response()->json($errorCode);
  
        }*/ //requisição get comum

        /*$response = Http::post('https://jsonplaceholder.typicode.com/posts', [
                'title' => 'foo',
                'body' => 'bar',
                'userId' => 1,
            ]);
            
            if ($response->successful()) {
                $post = $response->json();
                // Faça algo com os dados do post criado, por exemplo:
                return response()->json($post);
            } else {
                echo 'Erro ao fazer a requisição: ' . $response->status();
            }
        *///requisição post comum

        /*
            $response = Http::withHeaders([
            'Authorization' => 'Bearer YourAccessToken',
            'Custom-Header' => 'CustomValue',
                ])->post('https://jsonplaceholder.typicode.com/posts', [
                    'title' => 'foo',
                    'body' => 'bar',
                    'userId' => 1,
                ]);
        */ //requisição com cabeçalho
    } 
    
    function cripto($x) {
        
            $a = mt_rand(2, 8);
        
            $b = 'dpsfbasb';
            
        
            $c = 59;
        
            $d = '';
            $xStr = strval($x);
            $cStr = strval($c);
            $bIndex = 0;
            for ($i = 0; $i < 8; $i++) {
                if ($i >= 6) {
                    $d .= $cStr[$bIndex];
                    $d .= $b[$i];
                    $bIndex++;
                }else{
                    $d .= $xStr[$i];
                    $d .= $b[$i];
                }
            }
        
        
            $f ='';
            
                for ($i = 0; $i < strlen($d); $i++) {
                    $f.=$d[($i + $a)%16];
                }
            
            $resultado = md5($f) . $a;
            return $resultado;
                    
    }
    
    function gerarOTP() {
        // Defina o comprimento do código OTP
        $length = 6;

        // Gere um código aleatório de $length dígitos
        $otp = rand(pow(10, $length-1), pow(10, $length)-1);

        // Retorne o código gerado
        return $otp;
    }

    function set($email){
        $otpCode = $this->gerarOTP();
        
        $responses=$this->email->email_portal($email,$otpCode);

        $response = $this->cripto($otpCode);
        return $response;
    }

    function login(Request $request) {
        try {
            if(env('API_TOKEN') == $request->token ){
                $get_id=$this->api->get_user($request->bilhete);
                $aux=$this->gerarJWT($get_id['id']);
                return response()->json([
                    'msg' => 'Autenticação feita com sucesso',
                    'nome' => $get_id['name'],
                    'token' => $aux
                ],200);
                
            }else{
                return response()->json([
                    'error' => 'Acesso negado'
                ],402);
                            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                 'message' => $th->getMessage()   
            ],400);        }
    }

    function get_information(Request $request) {
        try {
            if(env('API_TOKEN') == $request->token ){
                $aux=$this->decodificarJWT($request->user_token);
                $get_id=$this->api->get_information($aux);
                return response()->json($get_id);
                return response()->json([
                    'endereco' => $get_id['ADDRESS_COUNTRY'],
                    'iban' => $get_id['BANK_IBAN'],
                    'data_nascimento' => $get_id['BIRTH_DATE'],
                    'estado_civil' => $get_id['CIVIL_STATUS'],
                    'genero' => $get_id['GENDER'],
                    'telefone' => $get_id['MOBILE_PHONE'],
                    'nacionalidade' => $get_id['NACIONALITY'],
                    'nome' => $get_id['NAME'],
                    'nif' => $get_id['TAX_NUMBER']

                ],200);
                
            }else{
                return response()->json([
                    'error' => 'Acesso negado'
                ],402);
                            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                 'message' => $th->getMessage()   
            ],400);        }
    }

    function get_token(Request $request) {
        try {
            if(env('API_TOKEN') == $request->token ){
                return response()->json([
                    'token' => env('AUTH_TOKEN')
                ],200);
            }else{
                return response()->json([
                    'error' => 'Acesso negado'
                ],402);
                            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                 'message' => $th->getMessage()   
            ],400);        }
    }

    function gerarJWT($id) {

        try {
            $chaveSecreta = env('JWT_SECRET'); 
            
            $payload = [
                'id' => $id,
                'exp' => time() + (24 * 60 * 60) 
            ];

            $token = JWT::encode($payload, $chaveSecreta, 'HS256');
            return $token;
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }

    function decodificarJWT($token) {
        try {
            $chaveSecreta = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($chaveSecreta, 'HS256'));
    
            return $decoded->id;
        } catch (\Throwable $th) {
            // Tratar exceções se necessário
            return $th;
        }
    }
    

    function refreshToken($token) {

        try {
            $chaveSecreta = env('JWT_SECRET'); 
            
            $decoded = JWT::decode($token, $chaveSecreta, ['HS256']);
            $payload = (array) $decoded;

            // Verifica se o token ainda é válido
            if (isset($payload['exp']) && $payload['exp'] > time()) {
                // Token ainda é válido, então podemos gerar um novo token com uma nova expiração
                $novoToken = gerarTokenJWT($payload['id'], $payload['email'], $payload['nome'], 24 * 60);
                return $novoToken;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }
    
    /*$response= $this->save_token('SO PARA CONFIRMAR');
                return 'A API ESTA A FUNCIONAR';       
      
    */    
     
    
}
