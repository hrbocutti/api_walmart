<?php

namespace App\MVC\Model;
use App\Client\IntelipostClient;

class CotacaoModel
{
    public function cotacao($produto, $postalCode, $postingday)
    {
        //Fazer Cotação
        $cepOrigin = "13480620";
        $infos = array("origin_zip_code" => $cepOrigin,
            "destination_zip_code" => $postalCode,
            "products" => array ($produto),
            "quoting_mode" => "DYNAMIC_BOX_BY_SKU",
            "additional_information" => array("free_shipping" => false,
                "extra_cost_absolute" => 2.00,
                "lead_time_business_days" => $postingday,
                "sales_channel" => "Walmart",
            ),
        );

        $response = $this->cotar(json_encode($infos));
        $resposta = $this->tratarResposta($produto, $response);
        return $resposta;
    }

    private function cotar($infos)
    {
        $intelipost = new IntelipostClient('https://api.intelipost.com.br/api/v1/quote_by_product', 'POST');
        return $intelipost->call($infos);
    }

    private function tratarResposta($products, $response)
    {
        //$quotations = json_decode($response);

        $quotations = json_decode($response);

        $options = array();

        foreach ($quotations->content->delivery_options as $quote) {
            $options[] = array(
                                "id" => $quote->delivery_method_name,
                                "name" => $quote->delivery_method_type,
                                "shippingEstimate" => $quote->delivery_estimate_business_days." dias",
                                "price" => $quote->final_shipping_cost,
                                "scheduledDeliveries" => null
                              );
        }
        return $options;
    }
}