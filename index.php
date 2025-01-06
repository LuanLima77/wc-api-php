<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

ini_set('max_execution_time', 0); //0=NOLIMIT

error_reporting(E_ALL);


require __DIR__ . '/vendor/autoload.php';

require_once "L2KC.php";

require_once "Usuario.php";



require_once "Subscription.php";

require_once "Mail.php";



use Automattic\WooCommerce\Client;




$url = "https://www.literatour.com.br";

$consumer_key = "ck_9e9f6e07f48147b3c6c4cf4b66225e4414a11724";

$consumer_secret ="cs_d79c90ba06f745edafebc270a27d3934682b4014";



$woocommerce = new Client($url, $consumer_key, $consumer_secret);

$mailClient = new LiteratourMail();




$ontem = gmdate("Y-m-d",strtotime("-10 days")). "T00:00:00";  //-2

$hoje = gmdate("Y-m-d"). "T00:00:00";




echo "Pedidos entre $ontem e $hoje <br> \n";




$endpoint = "orders";

$continua = 100;

$pagina = 1;



while($continua == 100){

    try{

        echo '<HR> PÁGINA: '.$pagina.'<hr>';

        echo "Iniciando coletas do dia $ontem" . "<br>\n";

        $paramLastOrders = [

            "status" => "processing",

            "after" => $ontem,

            "before" => $hoje,

            "per_page" => 100,

            "order" => "asc",

            "page" => $pagina



        ];


        $paramLastCancelledOrders = [

            "status" => "cancelled",

            "after" => $ontem,

            "per_page" => 100,

            "page" => $pagina,


        ];


    $recentOrders = $woocommerce->get($endpoint, $paramLastOrders);

    $L2KCService = new L2KC();


    echo count($recentOrders) . " PEDIDOS ENCONTRADOS </br><\n>";

    $continua = count($recentOrders);

foreach ($recentOrders as $order) {
    $descricao_cupom = $order->coupon_lines[0]->meta_data[0]->value->description ?? "";
    
    $wpCustomerId = $order->customer_id;
    $categoriaPrimaria = 0;
    $categoriaSecundaria = 0 ;
    $aceita18 = 0;
    $idSkoob = 0;
    $dataNascimento = 0;


    
     foreach($order->meta_data as $meta_data)

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



         if($meta_data->key ==  "_billing_dataNascimento")

         {

             $dataNascimento = $meta_data->value;

         }

         //Inicializa id de assinatura com o ID mandatorio de pagamento da IUGU, pois o mesmo está presente em praticamente 100% dos pedidos. 
         //O problema deste método é que vai gerar duplicações quando o assinante trocar o cartão. Porém em menor quantidade do que hoje, que é algo perto de 40% dos pedidos, já que nem todos os assinantes alteram o cartão durante a assinatura

         if($meta_data->key == "_iugu_customer_payment_method_id")
         {
            $wpIdSubscription =  $meta_data->value; 

         }

   
      //Após inicializar o Id da assinatura com esse fallback anterior, pegará o ID da assinatura de fato, que estará presente em 80% dos pedidos
      //O fato de possuir até um outro formato, facilita inclusive correções manuais futuras ou durante a jornada(trabalho manual ainda assim inferior do que hoje)
         if($meta_data->key == "_subscription_renewal")
         {
            $wpIdSubscription =  $meta_data->value; 

         }
         
         


     }
     
     
    echo '<hr>';

    
    $email = $order->billing->email;

    $plano =  $order->line_items[0]->name ? $order->line_items[0]->name : 0 ;
    
    if (stripos($plano, 'ANUAL') !== false) {
    $descricao_cupom = "[ASSINANTE ANUAL] " . $descricao_cupom;
}

    $cep = $order->billing->postcode;

    $estado = $order->billing->state ; 

    $fullName = $order->billing->first_name . " " . $order->billing->last_name;
    
    $telefone  =  $order->billing->phone; 



    $user = new Usuario($wpCustomerId,$fullName, $email,$plano,$cep, $estado,$aceita18,$categoriaPrimaria, $categoriaSecundaria , $idSkoob, $dataNascimento, $descricao_cupom, $telefone );

    $subscription = new Subscription($wpIdSubscription,$wpCustomerId,$categoriaPrimaria,$categoriaSecundaria,$plano);



//Exclui o kit avulso bimestral e semestra (sem recorrencia) e o apoiador da coleta

    if($order->created_via == "checkout" && ($order->line_items[0]->name !=  "Kit Semestral"  && $order->line_items[0]->name !=  "Kit Bimestral"  && strpos($order->line_items[0]->name, "Apoiador") === FALSE))

    {


      echo "USUARIO " . $fullName . " ASSINOU " .  $order->date_created . " PLANO -> " . $plano . "<br> <br>\n";

      $L2KCService->sendToL2KC($user);

      sleep(1);




    }elseif($order->created_via == "subscription" && ($order->line_items[0]->name !=  "Kit Semestral"  && $order->line_items[0]->name !=  "Kit Bimestral"  && $order->line_items[0]->name !=  "Kit Mensal") && strpos($order->line_items[0]->name, "Apoiador") === FALSE )

    {


     echo "<hr>Usuario " . $fullName . " renovou " .  $order->date_created . " plano " . $order->line_items[0]->name . "(index.php)<br> <br>\n";

      $L2KCService->updateOnL2KC($user, false);

      echo '<hr>';

    }



sleep(1);


}


$pagina++;

sleep(10);

}catch (Throwable $e) {

    // Handle error

    echo $e->getMessage(); // Call to undefined function undefinedFunctionCall()

    echo '<br /><b>A coleta parou na página '.$pagina.'</b><br />';

   // $mailClient->sendCollectFailure('77luanlima@gmail.com',$pagina);

    exit();

}

}


$L2KCService->verificaPedidosAnuais();



echo '<hr>Registros acabaram!';
