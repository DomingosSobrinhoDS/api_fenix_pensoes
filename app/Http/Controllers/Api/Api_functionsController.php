<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Http\Controllers\Api\FiltrosarraysController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;





class Api_functionsController extends Controller
{
    //contrutor

        public $filtro;
        public function __construct(){
            $this->filtro = new FiltrosarraysController();
        }
    //funções de requisição
    
        function verify_static($id){
            $get_user = DB::select('select u.user_id, u.saldo_final, u.n_empregado from users as u where u.n_ams = ?', [$id]);
            return $get_user;
        }

        function static_data($id){
            //$get_user = DB::select('select u.user_id from users as u where u.n_ams = ?', [$id]);
            
            $get_contribuicoes = DB::select('select * from contribuicao_users as cs where cs.user_id= ? order by cs.ano desc,cs.mes desc', [$id]);
            $ano=0;
            $verify=false;
            $mes=0;
            $result_array_mes = [];
            $result_array_mes_empresa = [];
            $data=[];


            foreach ($get_contribuicoes as $key => $value) {
               
               $result_array_mes[$mes]['mes'][$value->mes] = $value->contribuicao;
               $result_array_mes_empresa[$mes]['mes'][$value->mes] = DB::select('select ce.contribuicao as cont from contribuicao_empresas as ce where ce.user_id = ? and ce.ano = ? and ce.mes = ?', [$id, $value->ano,$value->mes])[0]->cont;
               //dd($result_array_mes_empresa[$mes]);
               if(!Arr::has($get_contribuicoes, ($key + 1))){
                   $verify = true;
               }else{
                   if ($get_contribuicoes[$key + 1]->ano != $value->ano) {
                     $verify = true;
               }
               }
               
               if ($verify) {
                    if (sizeof($result_array_mes) > 0) {
                        $data[] = [
                            'ano' => $value->ano,
                            'total_empresa' => DB::select('select te.total as cont from total_empresas as te where te.ano = ?', [$value->ano])[0]->cont,
                            'mes_empresa' => $result_array_mes_empresa[$mes]['mes'],
                            'nome_empresa' => DB::select('select te.nome as cont from total_empresas as te order by te.ano = ?', [$value->ano])[0]->cont,
                            'total_ano' => DB::select('select total as cont from total_users as tu where tu.user_id = ? and tu.ano = ?', [$id,$value->ano])[0]->cont,
                            'mes' => $result_array_mes[$mes]['mes'], 
                        ];
                    }
                    $result_array_mes = [];
                    $result_array_mes_empresa = [];
                    $mes=$mes+1;
                    $verify=false;
                }
            }

            
            return[$data, DB::select('select sum(tu.total) as cont from total_users as tu where tu.user_id = ?', [$id])[0]->cont, 0,DB::select('select sum(te.total) as cont from total_empresas as te where te.user_id = ?', [$id])[0]->cont];
            
        }

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

        function get_history_geral2($id) {

            //$count_ano=-1;
            //$total=0;
            //$total_q=0;
            //$verify_mes=00;
            //$verify=00;
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
                    $totaldados = $response->json();
                    // Faça algo com os dados do post criado, por exemplo:
                    if (sizeof($totaldados['result']) > 0) {

                        //$totaldados = json_decode($totaldados, true);
                        $total = 0;
                        $total_q = 0;

                        foreach ($totaldados['result'] as $key => $value) {
                            $totaldados['result'][$key]['ano'] = explode("-", $totaldados['result'][$key]["transactionDate"])[0];
                            $totaldados['result'][$key]['mes'] = explode("-", $totaldados['result'][$key]["transactionDate"])[1];
                        }

                        $groupedarray = $this->filtro->array_group_by($totaldados['result'], "ano", "mes");
                        $groupedbymonth = [];
                        $groupedbyyear = [];

                        foreach ($groupedarray as $anoloop => $meses) {
                            $mes_empresa = [];
                            $empresa = $this->get_up_id($id, $anoloop);
                            $groupedbyyear[] = [
                                'ano' => $anoloop,
                                'total_empresa' => $empresa[1],
                                'mes_empresa' => [],
                                'nome_empresa' => $empresa[2],
                            ];
                            end($groupedbyyear);
                            $new_index = key($groupedbyyear);
                            foreach ($meses as $key2 => $cadames) {
                                $key2 = $this->ajustarFormato($key2);
                                $groupedbymonth[$new_index][$key2] = 0;
                                $mes_empresa[$key2] = 0;
                                foreach ($cadames as $key3 => $cadalinha) {
                                    $groupedbymonth[$new_index][$key2] += $cadalinha['price'] + $cadalinha['totalValue'];
                                    $mes_empresa[$key2] += $cadalinha['totalValue'];
                                }
                                $groupedbyyear[$new_index]['total_ano'] = array_sum($groupedbymonth[$new_index]);
                                $total += $groupedbyyear[$new_index]['total_ano'];
                                $total_q += $cadalinha["quantity"];
                                $groupedbyyear[$new_index]['mes'] = $groupedbymonth[$new_index];
                            }
                    
                            $groupedbyyear[$new_index]['mes_empresa'] = $mes_empresa;
                        }
                        return[$groupedbyyear, $total, $total_q];
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
                        $get=$this->get_history_geral2($id);
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
                    $resultempresa = [];
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
                                $empresa=$this->get_up_id($id,$ano);
                                $data[] = [
                                    'ano' => $ano,
                                    'total_empresa' => $empresa[1],
                                    'mes_empresa' => $resultempresa[intval($count_ano)]['mes'],
                                    'nome_empresa' => $empresa[2],
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
                                $resultempresa[intval($count_ano)]['mes'][intval($mes)]= $this->get_up_id($id,$ano,$mes)[1];
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

        function data_formate(){
            return $data[] = [
                'ano' => 0,
                'total_empresa' => 0,
                'mes_empresa' => [
                    '1' => 0,
                    '2' => 0,
                    '3' => 0,
                    '4' => 0,
                    '5' => 0,
                    '6' => 0,
                    '7' => 0,
                    '8' => 0,
                    '9' => 0,
                    '10' => 0,
                    '11' => 0,
                    '12' => 0,
                ],
                'nome_empresa' => "",
                'total_ano' => 0,
                'mes' => [
                    '1' => 0,
                    '2' => 0,
                    '3' => 0,
                    '4' => 0,
                    '5' => 0,
                    '6' => 0,
                    '7' => 0,
                    '8' => 0,
                    '9' => 0,
                    '10' => 0,
                    '11' => 0,
                    '12' => 0,
                ],
                'total_individual' => 0,
                'mes_individual' => [
                    '1' => 0,
                    '2' => 0,
                    '3' => 0,
                    '4' => 0,
                    '5' => 0,
                    '6' => 0,
                    '7' => 0,
                    '8' => 0,
                    '9' => 0,
                    '10' => 0,
                    '11' => 0,
                    '12' => 0,
                ], 
            ];

        }

        function somatory($array,$mes,$ano,$nome_empresa,$total,$total_empresa,$total_individual,$valor,$tipo){
            $array['ano']=$ano;
            $array['nome_empresa']=$nome_empresa;
            
            switch ($tipo) {
                case 'ASS':
                    $array['total_empresa'] += $valor;
                    $array['mes_empresa'][$mes] += $valor;
                    $total_empresa += $valor;
                    break;
                case 'PAR':
                    $array['total_ano'] += $valor;
                    $array['mes'][$mes] += $valor;
                    $total += $valor;
                    break;
                case 'IND':
                    $array['total_individual'] += $valor;
                    $array['mes_individual'][$mes] += $valor;
                    $total_individual += $valor;
                    break;
            }

            return [$array,$total,$total_empresa,$total_individual];
        }

        function get_history_principal($id) {
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
            
            //pegar os dados do user com base ao id
            $response = Http::withHeaders([
            'Authorization' => 'Bearer '. env('AUTH_TOKEN'),
                ])->post(env('URL_API') .'/Entity/History', $data);
                $index=0;
                $verify=FALSE;
                $data=[];
                $total=0;
                $total_empresa=0;
                $total_individual=0;    
            if ($response->successful()) {
                $post = $response->json();
                $data[$index]= $this->data_formate();
                // Tratamento dos dados
                if (sizeof($post['result']) > 0) {
                    for ($i=0; $i < sizeof($post['result']); $i++) {

                        if ($verify) {
                            $index++;
                            $data[$index]= $this->data_formate();
                            $verify=FALSE;
                        }

                        if ($post['result'][$i]["transactionType"] == "Subscription") {
                            $ano= explode("-", $post['result'][$i]["transactionDate"]) [0];
                            $mes= explode("-", $post['result'][$i]["transactionDate"]) [1];
                            $tipo= explode("_", $post['result'][$i]["transactionAccount"]);

                            $result=$this->somatory($data[$index],intval($mes),intval($ano),$post['result'][$i]["transactionPortfolio"],$total,$total_empresa,$total_individual,$post['result'][$i]["totalValue"], $tipo[sizeof($tipo) - 1]);
                            
                            $data[$index]=$result[0];
                            $total=$result[1];
                            $total_empresa=$result[2];
                            $total_individual=$result[3]; 
                        }

                        if(!Arr::has($post['result'], ($i + 1))){
                            $verify = true;
                        }elseif (explode("-", $post['result'][$i + 1]["transactionDate"])[0] != $ano) {
                            $verify=TRUE;
                        }
                    }
                }else {
                    return [
                        'email'=>0,
                        'status'=>$response->status(),
                    ];
                }

                return [$data,$total,0,$total_empresa,$total_individual];

            } else {
                if ($response->status() == 403) {
                    $this->login();
                    $get=$this->get_history_principal($id);
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

                        return [$post['result'][0]['price'],$post['result'][0]['totalValue'],$post['result'][0]['unrealizedGains']];         
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
        function ajustarFormato($numero) {
            // Converte o número para string para verificar o número de dígitos
            $numeroStr = strval($numero);
            
            // Verifica se o número tem exatamente dois dígitos
            if (strlen($numeroStr) == 2) {
                // Remove o zero à esquerda
                return ltrim($numeroStr, '0');
            }
            
            // Retorna o número original se não tiver dois dígitos ou já estiver no formato correto
            return $numero;
        }
        
        function verify_user($email,$bi){
            //verifica se o email passado pelo usuario bate com o que esta na BD
            
                $get=$this->get_user($bi);

                //if($get['email']!=0 && $get['email'] == $email ){
                    return [
                        'id' => $get['id'],
                        'status'=>1,
                        'name'=>$get['name'],
                        'email'=>$get['email']
                    ];
                /*}
                return [
                    'status'=>0
                ];*/
            
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
            $data = $this->get_history_principal($id);
            $up = $this->get_up_id($id);
            return [
                'data' => $data[0],
                'values' => ['contribuition' => $data[1] + $data[3],
                                'up' => $up[0],
                                'sale' => $data[2]*$up[0],
                                'n_func' =>$id,
                                'final_sale' =>$up[1],
                                'total_individual'=>$data[4],
                                'total_company'=>$data[3],
                                'total_worker' =>$data[1],
                                'income'=> $up[2]
                                ],
                'pageSize' => $page,
                'filters' => [], 
            ];
        }



}
