<?php

namespace AbraaoMarques\Correios\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const BASE_CONFIG_PATH = 'carriers/abraaomarques_correios/';
    const BASE_VALUE_WEIGHT = 1;
    const BASE_VALUE_HEIGHT = 2;
    const BASE_VALUE_WIDTH = 16;
    const BASE_VALUE_LENGTH = 16;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context, ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @param $value
     * @return string|null
     */
    private function getValue($value): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_CONFIG_PATH.$value, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getWebService(): ?string
    {
        return $this->getValue('webservice_url');
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->getValue('login');
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->getValue('password');
    }

    /**
     * @return string|null
     */
    public function getPostingMethods(): ?string
    {
        return $this->getValue('posting_methods');
    }

    /**
     * @return string|null
     */
    public function getMaxWeight(): ?string
    {
        return $this->getValue('max_weight');
    }

    /**
     * @return int|null
     */
    public function getIncreaseDeliveryDays(): ?int
    {
        return $this->getValue('increase_delivery_days');
    }

    /**
     * @return int|null
     */
    public function getEnableLog(): ?int
    {
        return $this->getValue('enabled_log');
    }

    /**
     * @return float|null
     */
    private function getDefaultWeight(): ?float
    {
        return $this->getValue('default_weight');
    }

    /**
     * @return float|null
     */
    private function getDefaultWidth(): ?float
    {
        return $this->getValue('default_width');
    }

    /**
     * @return float|null
     */
    private function getDefaultHeight(): ?float
    {
        return $this->getValue('default_height');
    }

    /**
     * @return float|null
     */
    private function getDefaultLength(): ?float
    {
        return $this->getValue('default_length');
    }

    /**
     * @param $weight
     * @return float
     */
    public function getWeight($weight): float
    {
        if ($weight)
            return $weight;

        $defaultWeight = $this->getDefaultWeight();
        if ($defaultWeight)
            return $defaultWeight;

        return self::BASE_VALUE_WEIGHT;
    }

    /**
     * @param $width
     * @return float
     */
    public function getWidth($width): float
    {
        $defaultWidth = $this->getDefaultWidth();
        if (!$width && !$defaultWidth)
            return self::BASE_VALUE_WIDTH;

        if (!$width || $width < $defaultWidth)
            return $defaultWidth;

        return $width;
    }

    /**
     * @param $height
     * @return float
     */
    public function getHeight($height): float
    {
        $defaultHeight = $this->getDefaultHeight();
        if (!$height && !$defaultHeight)
            return self::BASE_VALUE_HEIGHT;

        if (!$height || $height < $defaultHeight)
            return $defaultHeight;

        return $height;
    }

    /**
     * @param $length
     * @return float
     */
    public function getLength($length): float
    {
        $defaultLength = $this->getDefaultLength();
        if (!$length && !$defaultLength)
            return self::BASE_VALUE_LENGTH;

        if (!$length || $length < $defaultLength)
            return $defaultLength;

        return $length;
    }

    /**
     * @return string
     */
    private function getOriginPostCode(): string
    {
        return $this->scopeConfig->getValue('shipping/origin/postcode', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @param $method
     * @return string
     */
    public function createEndPoint($zipcode, $weight, $height, $width, $length, $method): string
    {
        $webservice = $this->getWebService();
        $origin = $this->getOriginPostCode();
        $login = $this->getLogin();
        $password = $this->getPassword();

        $weight = $this->getWeight($weight);
        $height = $this->getHeight($height);
        $width = $this->getWidth($width);
        $length = $this->getLength($length);

        $hasContract = null;

        if ($login && $password)
            $hasContract = '&nCdEmpresa='.$login.'&sDsSenha='.$password;

        return $webservice.$hasContract.'&nCdFormato=1&nCdServico='.$method.'&nVlComprimento='.$length.'&nVlAltura='.$height.'&nVlLargura='.$width.'&sCepOrigem='.$origin.'&sCdMaoPropria=N&sCdAvisoRecebimento=N&nVlPeso='.$weight.'&sCepDestino='.$zipcode;
    }
}
