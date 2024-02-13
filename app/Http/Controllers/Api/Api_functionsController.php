<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;



class Api_functionsController extends Controller
{

    //funções de requisição
        function login(){
            // fazer o login para o uso da api
                $response = Http::post(env('URL_API') . '/Authentication/RequestToken', [
                    'username' => env('API_USER_NAME'),
                    'password' => env('API_PASS'),
                ]);
                
                if ($response->successful()) {
                    $post = $response->json();
                    // Faça algo com os dados do post criado, por exemplo:
                    return $this->save_token($post['token']);
                } else {
                    return 'Erro ao fazer a requisição: ' . $response->status();
                }
        }

        function get_user($bi) {
            $data = array(
                "identifierList" => array(
                    array(
                        "identifierType" => "TAX_NUMBER",
                        "identifier" => $bi
                    )
                ),
                "fields" => array("EMAIL", "NAME"),
                "functions" => array(0)
            );
            //pegar os dados do user com base ao nif
                $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                    ])->post(env('URL_API') .'/Entity/Get', $data);
                    
                if ($response->successful()) {
                    $post = $response->json();

                    // Faça algo com os dados do post criado, por exemplo:
                    if (sizeof($post['result']['entities']) > 0) {
                        return [
                            'email'=>$post['result']['entities'][0]['fields'][0]['value'],
                            'name'=>$post['result']['entities'][0]['fields'][1]['value'],
                            'id'=>$post['result']['entities'][0]['entityID'],
                        ];
                    }else {
                        return [
                            'email'=>0,
                            'status'=>$response->status(),
                        ];
                    }
                } else {
                    if ($response->status() == 403) {
                        $this->login();
                        $get=$this->get_user($bi);
                        return $get;
                    }else {
                        return [
                            'email'=>0,
                            'status'=>$response->status(),
                        ];
                    }
                }    
        }

        function get_information($id) {
            $data = array(
                "identifierList" => array(
                    array(
                        "identifierType" => "ENTITY_ID",
                        "identifier" => strval($id)
                    )
                ),
                "fields" => array("ADDRESS_COUNTRY",
                "BANK_IBAN",
                "BIRTH_DATE",
                "CIVIL_STATUS_DES", 
                "GENDER",
                "MOBILE_PHONE", 
                "NACIONALITY",
                "NAME",
                "TAX_NUMBER"),
                "functions" => array(0)
            );
            //pegar os dados do user com base ao nif
                $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                    ])->post(env('URL_API') .'/Entity/Get', $data);
                    
                if ($response->successful()) {
                    $post = $response->json();

                    // Faça algo com os dados do post criado, por exemplo:
                    if (sizeof($post['result']['entities']) > 0) {
                        if ($post['result']['entities'][0]['fields'][4]['value'] != '' && $post['result']['entities'][0]['fields'][4]['value'] != null) {
                            if ($post['result']['entities'][0]['fields'][4]['value'] == "Male") {
                                # code...
                            }
                            else {
                                # code...
                            }
                        }
                        return [
                            'control'=>0,
                            'ADDRESS_COUNTRY'=>$post['result']['entities'][0]['fields'][0]['value'],
                            'BANK_IBAN'=>$post['result']['entities'][0]['fields'][1]['value'],
                            'BIRTH_DATE'=>$post['result']['entities'][0]['fields'][2]['value'],
                            'CIVIL_STATUS'=>$post['result']['entities'][0]['fields'][3]['value'],
                            'GENDER'=>$post['result']['entities'][0]['fields'][4]['value'],
                            'MOBILE_PHONE'=>$post['result']['entities'][0]['fields'][5]['value'],
                            'NACIONALITY'=>$post['result']['entities'][0]['fields'][6]['value'],
                            'NAME'=>$post['result']['entities'][0]['fields'][7]['value'],
                            'TAX_NUMBER'=>$post['result']['entities'][0]['fields'][8]['value'],
                        ];
                    }else {
                        return [
                            'control'=>0,
                            'status'=>$response->status(),
                        ];
                    }
                } else {
                    if ($response->status() == 403) {
                        $this->login();
                        $get=$this->get_user($bi);
                        return $get;
                    }else {
                        return [
                            'email'=>0,
                            'status'=>$response->status(),
                        ];
                    }
                }    
        }
    
    //funções auxiliares
        function verify_user($email,$bi){
            //verifica se o email passado pelo usuario bate com o que esta na BD
            
                $get=$this->get_user($bi);

                if($get['email']!=0){
                    return [
                        'id' => $get['id'],
                        'status'=>1,
                        'name'=>$get['name'],
                        'email'=>$get['email']
                    ];
                }
                return [
                    'status'=>0
                ];
            
        }

        function save_token($token){
            // Caminho para o arquivo .env
                $envFilePath = base_path('.env');
                
                // Ler o conteúdo do arquivo .env
                $envContentArray = file($envFilePath);
    
                // Alterar a variável NOME_DA_VARIAVEL
                foreach ($envContentArray as $key => $line) {
                    if (strpos($line, 'AUTH_TOKEN=') !== false) {
                        $envContentArray[$key] = "AUTH_TOKEN=".$token."\n";
                        break;
                    }
                }
                
                File::put($envFilePath, implode('', $envContentArray));
                
                return 1;
                
        }

        /*
            if($get['email']!=0){
                        return [
                            'id' => $get['id'],
                            'status'=>1,
                            'name'=>$get['name'],
                            'email'=>$get['email']
                        ];
                }
        */



}
