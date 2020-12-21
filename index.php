<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once "L2KC.php";
require_once "Usuario.php";




use Automattic\WooCommerce\Client;


$url = "https://www.literatour.com.br";
$consumer_key = "ck_9e9f6e07f48147b3c6c4cf4b66225e4414a11724";
$consumer_secret ="cs_d79c90ba06f745edafebc270a27d3934682b4014";

$woocommerce = new Client($url, $consumer_key, $consumer_secret);


$ontem = gmdate("o-m-d",strtotime("-2 days")). "T00:00:00";
$hoje = gmdate("o-m-d"). "T00:00:00";

//$ontem = "2020-05-31T18:30:00";

$endpoint = "orders";

echo "Iniciando coletas do dia $ontem" . "<br>\n";
$parameters = [
    "status" => "processing",
    "after" => $ontem,
    "before" => $hoje,
    "per_page" => 100,
    "order" => "asc"
   
];

$parametersCancelled = [
    "status" => "cancelled",
    "after" => $ontem,
    "per_page" => 100
   
];

$recentCostumers = $woocommerce->get($endpoint, $parameters);

$recentCancelled = $woocommerce->get($endpoint, $parametersCancelled);
$L2KCService = new L2KC();

foreach ($recentCostumers as $customer) {



    if($customer->created_via == "checkout" && ($customer->line_items[0]->name !=  "Kit Semestral"  && $customer->line_items[0]->name !=  "Kit Trimestral"  
                                            && $customer->line_items[0]->name !=  "Kit Mensal" && strpos("Apoiador",$customer->line_items[0]->name) === FALSE
                                               ) 
      )
    {
        $fullName = $customer->billing->first_name . " " . $customer->billing->last_name;

        $user = new Usuario($customer->customer_id,$fullName, $customer->billing->email,$customer->line_items[0]->name,$customer->billing->postcode, 
                          $customer->billing->state,$customer->meta_data[5]->value,$customer->meta_data[3]->value,
                          $customer->meta_data[4]->value, $customer->meta_data[6]->value );
    
    
      $L2KCService->sendToL2KC($user);
    }
      

}

foreach ($recentCostumers as $customer) {

    $categoriaPrimaria = 0;
    $categoriaSecundaria = 0 ;
    $aceita18 = "nao-aceito";
    $idSkoob = null;
 
     foreach($customer->meta_data as $meta_data)
     {
         if($meta_data->key ==  "_billing_Categoria_de_livro")
         {
             $categoriaPrimaria = $meta_data->value;
         }
 
         if($meta_data->key ==  "_billing_Categoria_extra")
         {
             $categoriaSecundaria = $meta_data->value;
         }
 
         if($meta_data->key ==  "_billing_+18")
         {
             $aceita18 = $meta_data->value;
         }
 
         if($meta_data->key ==  "_billing_skookid")
         {
             $idSkoob = $meta_data->value;
         }
     }
     

    if($customer->created_via == "subscription" && ($customer->line_items[0]->name !=  "Kit Semestral"  && $customer->line_items[0]->name !=  "Kit Trimestral"  && $customer->line_items[0]->name !=  "Kit Mensal") )
    {
        $fullName = $customer->billing->first_name . " " . $customer->billing->last_name;

        $user = new Usuario($customer->customer_id,$fullName, $customer->billing->email,$customer->line_items[0]->name,$customer->billing->postcode, 
                          $customer->billing->state,$aceita18,$categoriaPrimaria,
                          $categoriaSecundaria, $idSkoob );

    
    
                          echo "USUARIO " . $fullName . " RENOVOU " .  $customer->date_created . " PLANO " . $customer->line_items[0]->name . "<br> <br>\n";
      $L2KCService->updateOnL2KC($user);
    }
      

}

foreach ($recentCancelled as $customer) {
    

    if($customer->created_via == "checkout" && ($customer->line_items[0]->name !=  "Kit Semestral"  && $customer->line_items[0]->name !=  "Kit Trimestral"  && $customer->line_items[0]->name !=  "Kit Mensal") )
    {
        $fullName = $customer->billing->first_name . " " . $customer->billing->last_name;

        $user = new Usuario($customer->customer_id,$fullName, $customer->billing->email,$customer->line_items[0]->name,$customer->billing->postcode, 
                          $customer->billing->state,$customer->meta_data[5]->value,$customer->meta_data[3]->value,
                          $customer->meta_data[4]->value, $customer->meta_data[6]->value );
    
    
     // $L2KCService->cancelOnL2KC($user);
    }


}



