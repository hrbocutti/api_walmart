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
        $dbSelect->fatoryLocal();

        $produtos = array();
        foreach ($items as $item) {
            $sku = $item['id'];
            $qty = $item['quantity'];

            $itemInfos = TbPostingdaysNs::where('sku', $sku)->first();
            if(empty($itemInfos)){
                return null;
            }
            $itemInfos['sku'] = $sku;
            $itemInfos['qty'] = $qty;
            $itemInfos['postalCode'] = $postalCode;
            $cotacaoByItem = $this->cotacaoIntelipost($itemInfos);

            $produtos = $cotacaoByItem;
            $itemsWithPrice[] = array(
                    "id" => $sku,
                    "quantity" => $qty,
                    "price" => $itemInfos['preco_netshoes'],
                    "listPrice" => $itemInfos['preco_netshoes']
            );

            $itemsWithStock[] = array(
                "itemId" => "ABC3534411",
                "inventoryAvailable" => $qty,
                "quantity" => $qty,
                "categories" => $produtos
            );
        }

        $quotation = array(
                    "items" => $itemsWithPrice,
                    "country" => "BRA",
                    "postalCode" => $postalCode,
                    "shipmentInfos" => $itemsWithStock
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
        return $intelipost->cotacao($dados, $itemInfos['postalCode'], $itemInfos['posting_days']);
    }

}