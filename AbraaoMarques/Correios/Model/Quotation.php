<?php

namespace AbraaoMarques\Correios\Model;

use AbraaoMarques\Correios\Helper\Data;
use AbraaoMarques\Correios\Api\SearchQuotationServiceInterface;

class Quotation
{
    const METHOD_NAME_SEDEX = 'Sedex';
    const METHOD_NAME_PAC = 'Pac';

    public $pac = [41106,4669];

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SearchQuotationServiceInterface
     */
    private $quotationService;

    /**
     * @param Data $helper
     * @param SearchQuotationServiceInterface $quotationService
     */
    public function __construct(
        Data $helper,
        SearchQuotationServiceInterface $quotationService
    ) {
        $this->helper = $helper;
        $this->quotationService = $quotationService;
    }

    /**
     * Método responsável por consumir a Service SearchQuotationService e retornar o resultado
     * das consultas nos Correios
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @return array
     */
    public function search($zipcode, $weight, $height, $width, $length)
    {
        $helper = $this->helper;
        $methods = explode(",", $helper->getPostingMethods());
        $result = [];

        foreach ($methods as $method) {
            $url = $helper->createEndPoint($zipcode, $weight, $height, $width, $length, $method);
            $result[] = $this->quotationService->search($url);
        }

        return $result;
    }

    /**
     * Método responsável por montar o resultado de exibição (de quotação) na página do produto
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @return string|null
     */
    public function inProductPage($zipcode, $weight, $height, $width, $length)
    {
        $data = $this->search($zipcode, $weight, $height, $width, $length);
        $count = count($data);
        $methodName = self::METHOD_NAME_SEDEX;
        $result = null;

        for ($i = 0; $i < $count; $i++) {
            if (in_array($data[$i]->Codigo, $this->pac))
                $methodName = self::METHOD_NAME_PAC;

            $days = $data[$i]->PrazoEntrega;
            $addDays = $this->helper->getIncreaseDeliveryDays();

            if ($addDays)
                $days = $days + $addDays;

            $result .= "<span>{$methodName} - Em média {$days} dia(s) <strong>R$ {$data[$i]->Valor}</strong></span>";
        }

        return $result;
    }
}
