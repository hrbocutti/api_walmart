<?php

namespace App\MVC\Model;
use App\Entity\TbPostingdaysNs;
use App\Factory\DbFactory;

class FulfillmentPreviewModel
{
    public function cotacao($body)
    {
        $potalCode = $body["postalCode"];
        $items = $body["items"];

        $dbSelect = new DbFactory();
        $dbSelect->fatoryLocal();

        foreach ($items as $item) {
            $sku = "101900UNICA";
            $itemInfos = TbPostingdaysNs::where('sku', $sku)->first();
            $dados = array();
            $this->cotacaoIntelipost($dados);

        }
        echo json_encode($itemInfos);
    }

    public function cotacaoIntelipost($dados)
    {
    }

}