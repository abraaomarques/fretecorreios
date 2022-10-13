<?php

namespace AbraaoMarques\Correios\Controller\Search;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use AbraaoMarques\Correios\Model\Quotation as ModelQuotation;
use Magento\Framework\Controller\Result\JsonFactory;

class Quotation implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ModelQuotation
     */
    private $modelQuotation;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param RequestInterface $request
     * @param ModelQuotation $modelQuotation
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        RequestInterface $request,
        ModelQuotation $modelQuotation,
        JsonFactory $jsonFactory
    ) {
        $this->request = $request;
        $this->modelQuotation = $modelQuotation;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->request->getParams();
        $zipcode = $data['zipcode'];
        $weight = $data['weight'] ?? null;
        $width = $data['width'] ?? null;
        $height = $data['height'] ?? null;
        $length = $data['length'] ?? null;
        $result = $this->modelQuotation->inProductPage($zipcode, $weight, $height, $width, $length);
        $json = $this->jsonFactory->create();

        return $json->setData($result);
    }
}
