<?php

namespace App\MVC\Model;
use App\Entity\TbPostingdaysNs;
use App\Factory\DbFactory;
use App\MVC\Model\CotacaoModel;
use App\MVC\View\FullfillmentPreviewView;

class FulfillmentPreviewModel
{
    public function cotacao($body)
    {
        $postalCode = $body["postalCode"];
        $items = $body["items"];
        $itemsWithPrice = array();
        $view = new FullfillmentPreviewView();

        $dbSelect = new DbFactory();
        $dbSelect->factoryLocal();

        if(empty($items)){
            header("Error", null, 400);
            echo json_encode(array("summary" => "Invalid body"));
            die();
        }

        $produtos = array();
        foreach ($items as $item) {
            $sku = $item['id'];
            $qty = $item['quantity'];

            if($qty == 0){
                header("Error", null, 400);
                echo json_encode(array("summary" => "Quantity cannot be 0 or null"));
                die();
            }

            $itemInfos = TbPostingdaysNs::where('sku', $sku)->first();
            if(!empty($itemInfos)){
                $itemInfos['sku'] = $sku;
                $itemInfos['postalCode'] = $postalCode;
                $cotacaoByItem = $this->cotacaoIntelipost($itemInfos);

                $produtos = $cotacaoByItem;
                $itemsWithPrice[] = array(
                    "id" => $sku,
                    "quantity" => ($qty > $itemInfos['qty'])? $itemInfos['qty']:$qty,
                    "price" => $itemInfos['preco_netshoes'],
                    "listPrice" => $itemInfos['preco_netshoes']
                );

                $itemsWithStock[] = array(
                    "itemId" => $sku,
                    "inventoryAvailable" => $itemInfos['qty'],
                    "quantity" => ($qty > $itemInfos['qty'])? $itemInfos['qty']:$qty,
                    "categories" => ($itemInfos['qty'] <= 0)? array():$produtos
                );
            }

        }

        $quotation = array(
                    "items" => (!empty($itemsWithPrice))? $itemsWithPrice:array(),
                    "country" => "BRA",
                    "postalCode" => $postalCode,
                    "shipmentInfos" => (!empty($itemsWithStock))? $itemsWithStock:array()
        );
        return $quotation;
    }

    public function cotacaoIntelipost($itemInfos)
    {
        $dados = array (
            "weight"        => (float) $itemInfos['weight'],
            "cost_of_goods" => $itemInfos['preco_netshoes'],
            "width"         => (float) $itemInfos['volume_largura'],
            "height"        => (float) $itemInfos['volume_altura'],
            "length"        => (float) $itemInfos['volume_comprimento'],
            "quantity"      => $itemInfos['qty'],
            "sku_id"        => $itemInfos['sku']
        );

        $intelipost = new CotacaoModel();
        $cotacao  = $intelipost->cotacao($dados, $itemInfos['postalCode'], $itemInfos['posting_days']);
        return $cotacao;
    }

}