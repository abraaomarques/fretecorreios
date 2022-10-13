<?php

namespace AbraaoMarques\Correios\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use AbraaoMarques\Correios\Helper\Data;
use AbraaoMarques\Correios\Model\Quotation;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'abraaomarques_correios';

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Quotation
     */
    private $quotation;

    /**
     * @var ProductRepositoryInterface
     */
    private $productInterface;

    /**
     * @param ProductRepositoryInterface $productInterface
     * @param Quotation $quotation
     * @param Data $helper
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        ProductRepositoryInterface $productInterface,
        Quotation $quotation,
        Data $helper,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->productInterface = $productInterface;
        $this->quotation = $quotation;
        $this->helper = $helper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active'))
            return false;

        $helper = $this->helper;
        $totalWeight = $request->getPackageWeight();

        if ($totalWeight > $helper->getMaxWeight()) {
            $this->_logger->warning('Total Weight is not allowed');
            return false;
        }

        $destinationPostCode = $request->getDestPostcode();
        $data = $request->getAllItems();
        $products = $this->getProductInformation($data);
        $totalHeight = null;
        $totalWidth = null;
        $totalLength = null;

        foreach ($products as $product) {
            $totalHeight += $helper->getHeight($product['height']);
            $totalWidth += $helper->getHeight($product['width']);
            $totalLength += $helper->getHeight($product['length']);
        }

        $searchResult = $this->quotation->search($destinationPostCode, $totalWeight, $totalHeight, $totalWidth, $totalLength);

        if ($helper->getEnableLog())
            $this->_logger->info(json_encode($searchResult));

        $result = $this->_rateResultFactory->create();
        $methodName = Quotation::METHOD_NAME_SEDEX;

        foreach ($searchResult as $item) {
            if (in_array($item->Codigo, $this->quotation->pac))
                $methodName = Quotation::METHOD_NAME_PAC;

            $method = $this->_rateMethodFactory->create();
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setCarrier($this->_code);
            $method->setMethodTitle($methodName);
            $method->setMethod($methodName);

            $amount = $item->Valor;
            $method->setPrice($amount);
            $method->setCost($amount);
            $result->append($method);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductInformation($data)
    {
        $product = [];
        foreach ($data as $value) {
            $productRepository = $this->productInterface->get($value->getSku());

            $product[] = [
                'height' => $productRepository->getData('height'),
                'width' => $productRepository->getData('width'),
                'length' => $productRepository->getData('length'),
            ];
        }

        return $product;
    }
}
