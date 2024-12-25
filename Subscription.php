<?php

require_once  "utils.php";


class Subscription



{


    public $wpIdSubscription;

    public $wpCustomerId;

    public $PrimaryCategory;

    public $SecondaryCategory;

    public $SubscriptionType;

    public $quinzena;



    function __construct($wpIdSubscription,$wpCustomerId,$PrimaryCategory,$SecondaryCategory = null,$SubscriptionType) 

    {

        $this->wpIdSubscription = $wpIdSubscription;

        $this->wpCustomerId = $wpCustomerId;

        $this->PrimaryCategory = getCategoria($PrimaryCategory);

        $this->SecondaryCategory = getCategoria($SecondaryCategory);

        $this->SubscriptionType = getPlano($SubscriptionType);

        $this->quinzena = getQuinzenaAtual();

       

    }




}