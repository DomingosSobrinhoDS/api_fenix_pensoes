<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;



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
                                $post['result']['entities'][0]['fields'][4]['value']="Masculino";
                            }
                            else {
                                $post['result']['entities'][0]['fields'][4]['value']="Femenino";
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

        function get_history_geral($id) {

            $count_ano=-1;
            $total=0;
            $total_q=0;
            $verify_mes=00;
            $verify=00;
            // Obter a data e hora atuais
            $currentDateTime = Carbon::now();

            // Formatar a data e hora atuais no formato ISO 8601
            $formattedDateTime = $currentDateTime->toISOString();

            $data = array(
                "entityIdentifierList" => array(
                    array(
                        "identifierType" => "ENTITY_ID",
                        "identifier" => strval($id)
                    )
                ),
                "startDate"=> "1900-04-01",
                "endDate"=> $formattedDateTime,
                "portfolioGroup"=> null,
                "onlyLockedValuations"=> false,
                "page"=> null,
                "pageSize"=> null,
                "currency"=> null
            );
            //pegar os dados do user com base ao nif
                $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                    ])->post(env('URL_API') .'/Entity/History', $data);
                $result = [];
                $data=[];    
                if ($response->successful()) {
                    $post = $response->json();
                    // Faça algo com os dados do post criado, por exemplo:
                    if (sizeof($post['result']) > 0) {

                        for ($i=0; $i < sizeof($post['result']); $i++) { 

                            if ($post['result'][$i]["transactionType"] == "Subscription") {
                            $ano= explode("-", $post['result'][$i]["transactionDate"]) [0];
                            $mes= explode("-", $post['result'][$i]["transactionDate"]) [1];

                            if ($i == 0 ) {
                                $total_ano = $post['result'][$i]["price"] +  $post['result'][$i]["totalValue"];

                                $count_ano +=1;
                                $verify = $ano;

                            }elseif ($ano != $verify) {

                                $data[] = [
                                    'ano' => $ano,
                                    'total_empresa' => $this->get_up_id($id,$ano)[1],
                                    'total_ano' => $total_ano,
                                    'mes' => $result[intval($count_ano)]['mes'], 
                                ];

                                $count_ano +=1;
                                $verify = $ano;
                                $total_ano =0;                            
                            }
                            

                            if ($mes === $verify_mes) {
                                $result[intval($count_ano)]['mes'][intval($mes)] += $post['result'][$i]["price"] +  $post['result'][$i]["totalValue"];
                            }else {
                                $verify_mes=$mes;
                                $result[intval($count_ano)]['mes'][intval($mes)] = $post['result'][$i]["price"] +  $post['result'][$i]["totalValue"];
                            }
                            $total_ano += $post['result'][$i]["price"] +  $post['result'][$i]["totalValue"];
                            $total+= $total_ano;
                            $total_q += $post['result'][$i]["quantity"];

                            /*$result[$count_ano] = [
                                'ano' => $ano,
                                'months' => array_fill(1, 12, null),
                                'total' => 0,
                            ];*/
                        }

                        }
                    }else {
                        return [
                            'email'=>0,
                            'status'=>$response->status(),
                        ];
                    }

                    return [$data,$total,$total_q];

                } else {
                    if ($response->status() == 403) {
                        $this->login();
                        $get=$this->get_history_geral($id);
                        return $get;
                    }else {
                        return [
                            'email'=>0,
                            'status'=>$response->status(),
                        ];
                    }
                }
        }

        function get_up_id($id,$year=null) {
            $currentDateTime = Carbon::now();

            // Formatar a data e hora atuais no formato ISO 8601
            if ($year) {
                $formattedDateTime =$year. "-12-31";
            }
            else {
                $formattedDateTime = $currentDateTime->toISOString();
            }

            $data = array(
                "entityIdentifierList" => array(
                    array(
                        "identifierType" => "ENTITY_ID",
                        "identifier" => strval($id)
                    )
                ),
                "date" => $formattedDateTime,
                "portfolioGroup" => null,
                "onlyLockedValuations" => false
            );
            //pegar os dados do user com base ao nif
                $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                    ])->post(env('URL_API') .'/Entity/Holdings', $data);
   
                if ($response->successful()) {
                    $post = $response->json();

                    if (sizeof($post['result']) > 0) {

                        return [$post['result'][0]['fundId'],$post['result'][0]['totalValue']];                        ;
                    }else {
                        return [
                            'error'=>404,
                            'status'=>$response->status(),
                        ];
                    }

                } else {
                    if ($response->status() == 403) {
                        $this->login();
                        $get=$this->get_up_id($id);
                        return $get;
                    }else {
                        return [
                            'error'=>404,
                            'status'=>$response->status(),
                        ];
                    }
                }
        }

        function get_up($id) {

            $id1 = $this->get_up_id($id)[0];
            $currentDateTime = Carbon::now();

            // Formatar a data e hora atuais no formato ISO 8601
            $formattedDateTime = $currentDateTime->toISOString();

            $data = array(
                "fundIdentifierList" => array(
                    array(
                        "identifierType" => "FUND_ID",
                        "identifier" => strval($id1)
                    )
                ),
                "priceDate" => $formattedDateTime,
                "portfolioGroup" => null,
                "onlyLockedValuations" => false
            );
            //pegar os dados do user com base ao nif
                $response = Http::withHeaders([
                'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                    ])->post(env('URL_API') .'/Portfolio/Fund/Price', $data);
   
                if ($response->successful()) {
                    $post = $response->json();

                    if (sizeof($post['result']) > 0) {

                        return $post['result'][0]['price'];
                    }else {
                        return [
                            'error'=>404,
                            'status'=>$response->status(),
                        ];
                    }

                } else {
                    if ($response->status() == 403) {
                        $this->login();
                        $get=$this->get_up($id);
                        return $get;
                    }else {
                        return [
                            'error'=>404,
                            'status'=>$response->status(),
                        ];
                    }
                }
        }

    
    //funções auxiliares
        function verify_user($email,$bi){
            //verifica se o email passado pelo usuario bate com o que esta na BD
            
                $get=$this->get_user($bi);

                if($get['email']!=0 && $get['email'] == $email ){
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

        function loading_data($id,$page) {

            $data = $this->get_history_geral($id);
            $up = $this->get_up($id);
            return [
                'data' => $data[0],
                'values' => ['contribuition' => $data[1],
                             'up' => $up,
                             'sale' => $data[2]*$up],
                'pageSize' => $page,
                'filters' => [], 
            ];
        }



}
