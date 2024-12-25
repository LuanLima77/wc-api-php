<?php

require_once  "utils.php";

class Order


{

    public $id;

    public $email;
    public $tipoCaixa;
    public $atendido;

    public $data_expiracao;


    



    function __construct($email, $tipoCaixa,$atendido=null,$data_expiracao=null) 

    {
        $this->email = $email;
        $this->tipoCaixa = $tipoCaixa;
        $this->atendido = $atendido;
        $this->data_expiracao = $data_expiracao;



       
       

    }





}