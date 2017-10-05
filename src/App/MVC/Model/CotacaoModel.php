<?php

namespace App\MVC\Model;


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
        /*echo $infos;
        die();*/
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.intelipost.com.br/api/v1/quote_by_product",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $infos,
            CURLOPT_HTTPHEADER => array(
                "api_key: c11d12528468214a1962fa46751f5de4b8f2ad3ea9e99917fbec1a5f2f645af5",
                "platform: Walmart",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            http_response_code(400);
            echo json_encode(array("type"=>"ERROR", "Message"=>$err));
        } else {
            return $response;
        }
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