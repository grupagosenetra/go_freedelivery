<?php

class Go_freedeliveryWidgetModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {

        parent::initContent();


    }

    public function displayAjaxUpdateCarousel()
    {
        $freeDelivery = new Go_freedelivery();

        $this->ajaxRender($freeDelivery->hookDisplayShoppingCartFooter());
    }
}
