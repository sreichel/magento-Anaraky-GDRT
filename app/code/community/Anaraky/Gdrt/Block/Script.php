<?php
class Anaraky_Gdrt_Block_Script extends Mage_Core_Block_Abstract
{
    private $storeId = 0;
    private $pid = false;
    private $pid_prefix = "";
    private $pid_prefix_ofcp = 0;
    private $pid_ending = "";
    private $pid_ending_ofcp = 0;

    private function getEcommProdid($product)
    {
        $ecomm_prodid = (string)($this->pid ? $product->getId() : $product->getSku());

        $ofcp = in_array($product->getTypeId(), array(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, Mage_Catalog_Model_Product_Type::TYPE_GROUPED));

        if (!empty($this->pid_prefix) && (($this->pid_prefix_ofcp === 1 && $ofcp) || $this->pid_prefix_ofcp === 0)) {
            $ecomm_prodid = $this->pid_prefix . $ecomm_prodid;
        }

        if (!empty($this->pid_ending) && (($this->pid_ending_ofcp === 1 && $ofcp) || $this->pid_ending_ofcp === 0)) {
            $ecomm_prodid .= $this->pid_ending;
        }

        return $ecomm_prodid;
    }

    private function getParams()
    {
        if ((int)Mage::getStoreConfig('google/gdrt_general/gdrt_product_id', $this->storeId) === 0) {
            $this->pid = true;
        }

        $this->pid_prefix = Mage::getStoreConfig('google/gdrt_general/gdrt_product_id_prefix', $this->storeId);
        $this->pid_prefix_ofcp = (int)Mage::getStoreConfig('google/gdrt_general/gdrt_product_id_prefix_ofcp', $this->storeId);
        $this->pid_ending = Mage::getStoreConfig('google/gdrt_general/gdrt_product_id_ending', $this->storeId);
        $this->pid_ending_ofcp = (int)Mage::getStoreConfig('google/gdrt_general/gdrt_product_id_ending_ofcp', $this->storeId);

        $inclTax = Mage::getStoreConfigFlag('google/gdrt_general/gdrt_tax', $this->storeId);

        $type = $this->getData('pageType');
        $params = array('ecomm_pagetype' => 'other');
        switch ($type) {
            case 'home':
                $params = array('ecomm_pagetype' => 'home');
                break;

            case 'searchresults':
                $params = array('ecomm_pagetype' => 'searchresults');
                break;

           case 'category':
                $category = Mage::registry('current_category');
                $productCollections = $category->getProductCollection()
                    ->addAttributeToSelect('sku');
                $data = array();
                foreach ($productCollections as $product) {
                    $data[] = $this->getEcommProdid($product);
                }

                $params = array(
                    'ecomm_prodid' => $data,
                    'ecomm_pagetype' => 'category',
                    'ecomm_category' => (string)$category->getName()
                );

                unset($category, $data, $productCollections, $product);
                break;

            case 'product':
                $product = Mage::registry('current_product');
                $totalvalue = $this->_getProductPrice($product, $inclTax);

                $params = array(
                    'isSaleItem' => (int)($product->getFinalPrice() < $product->getPrice()),
                    'ecomm_prodid' => $this->getEcommProdid($product),
                    'ecomm_pagetype' => 'product',
                    'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                );

                if ($category = Mage::registry('current_category')) {
                    $params['ecomm_category'] = (string)$category->getName();
                }

                unset($category, $product);
                break;

            case 'cart':
                $cart = Mage::getSingleton('checkout/session')->getQuote();
                $items = $cart->getAllVisibleItems();
                if (count($items) > 0) {
                    $data = array();
                    $totalvalue = 0;
                    foreach ($items as $item) {
                        $product = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToFilter('entity_id', $item->getProductId())
                            ->addAttributeToSelect('sku')->getFirstItem();

                        $data[0][] = $this->getEcommProdid($product);
                        $data[1][] = (int)$item->getQty();
                        $totalvalue += $inclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
                    }
                    $params = array(
                        'ecomm_prodid' => $data[0],
                        'ecomm_pagetype' => 'cart',
                        'ecomm_quantity' => $data[1],
                        'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                    );
                } else {
                    $params = array('ecomm_pagetype' => 'other');
                }
                unset($cart, $items, $item, $data, $product);
                break;

            case 'purchase':
                $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
                $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);

                $product = Mage::getModel('catalog/product');

                $data  = array();
                $totalvalue = 0;
                $items = $order->getAllItems();

                foreach ($items as $item) {
                    $productId = $product->getIdBySku($item->getSku());
                    $data[0][] = $this->getEcommProdid($product->load($productId));
                    $data[1][] = (int)$item->getQtyToInvoice();
                    $totalvalue += $inclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
                }

                $params = array(
                    'ecomm_prodid' => $data[0],
                    'ecomm_pagetype' => 'purchase',
                    'ecomm_quantity' => $data[1],
                    'ecomm_totalvalue' => (float)number_format($totalvalue, '2', '.', '')
                );
                unset($order, $items, $item);
                break;

            default:
                break;
        }

        return $params;
    }

    private function paramsToJS($params)
    {
        $result = array();

        foreach ($params as $key => $value) {
            if (is_array($value) && count($value) == 1) {
                $value = $value[0];
            }

            if (is_array($value)) {
                if (is_string($value[0])) {
                    $value = '["' . implode('","', $value) . '"]';
                } else {
                    $value = '[' . implode(',', $value) . ']';
                }
            } elseif (is_string($value)) {
                $value = '"' . $value . '"';
            }

            $result[] = $key . ': ' . $value;
        }

        return PHP_EOL . "\t" . implode(',' . PHP_EOL . "\t", $result) . PHP_EOL;
    }

    private function paramsToURL($params)
    {
        $result = array();

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $result[] = $key . '=' . $value;
        }

        return urlencode(implode(';', $result));
    }

    private function paramsToDebug($params)
    {
        $result = '';

        foreach ($params as $key => $value) {
            if (is_array($value) && count($value) == 1) {
                $value = $value[0];
            }

            if (is_array($value)) {
                if (is_string($value[0])) {
                    $value = '["' . implode('","', $value) . '"]';
                } else {
                    $value = '[' . implode(',', $value) . ']';
                }
            } elseif (is_string($value)) {
                $value = '"' . $value . '"';
            }

            $result .= '<tr>' .
                '           <td style="text-align:right;font-weight:bold;">' . $key . ': &nbsp;</td>' .
                '           <td style="text-align:left;"> ' . $value . '</td>' .
                '        </tr>';
        }

        return $result;
    }

    protected function _toHtml()
    {
        $this->storeId = Mage::app()->getStore()->getId();
        $gcId = (int)Mage::getStoreConfig('google/gdrt_general/gc_id', $this->storeId);
        $gcLabel = trim(Mage::getStoreConfig('google/gdrt_general/gc_label', $this->storeId));
        $gcParams = $this->getParams();

        $version = (string)Mage::getConfig()->getNode()->modules->Anaraky_Gdrt->version;

        $s = PHP_EOL .
            '<!-- Anaraky GDRT v.' . $version . ' script begin -->' . PHP_EOL .
            '<script type="text/javascript">' . PHP_EOL .
            '/* <![CDATA[ */' . PHP_EOL .
            'var google_tag_params = {' . $this->paramsToJS($gcParams) . '};' . PHP_EOL .
            'var google_conversion_id = ' . $gcId . ';' . PHP_EOL .
            (!empty($gcLabel) ? 'var google_conversion_label = "' . $gcLabel . '";' . PHP_EOL : '') .
            'var google_custom_params = window.google_tag_params;' . PHP_EOL .
            'var google_remarketing_only = true;' . PHP_EOL .
            '/* ]]> */' . PHP_EOL .
            '</script>' . PHP_EOL .
            '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . PHP_EOL .
            '</script>' . PHP_EOL .
            '<noscript>' . PHP_EOL .
            '<div style="display:inline;">' . PHP_EOL .
            '<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/' . $gcId . '/?value=0' . (!empty($gcLabel) ? '&amp;label=' . $gcLabel : '') . '&amp;guid=ON&amp;script=0&amp;data=' . $this->paramsToURL($gcParams) . '"/>' . PHP_EOL .
            '</div>' . PHP_EOL .
            '</noscript>' . PHP_EOL .
            '<!-- Anaraky GDRT script end -->' . PHP_EOL;

        if ((int)Mage::getStoreConfig('google/gdrt_debug/show_info', $this->storeId) === 1) {
            $lk = str_replace(' ', '', Mage::getStoreConfig('dev/restrict/allow_ips', $this->storeId));
            $ips = explode(',', $lk);
            if (empty($ips[0]) || in_array(Mage::helper('core/http')->getRemoteAddr(), $ips)) {
                $s .= PHP_EOL .
                    '<div style="position:fixed; left:0; right:0; bottom:0; padding:5px 0; background:rgba(255, 208, 202, 0.8); border:1px solid #f92104;">' . PHP_EOL .
                    '    <table style="margin:0 auto;font-size:13px;color:#222;">' .
                    '        <tr>' .
                    '           <td rowspan="' . (count($gcParams) + 1) . '" style="vertical-align:middle;padding-right:40px;"><h3 style="margin:0;">Anaraky GDRT debug v.' . $version . '</h3></td>' .
                    '           <td style="text-align:right;font-weight:bold;">Model/Controller/Action: &nbsp;</td>' .
                    '           <td style="text-align:left;"> ' . $this->getData('pagePath') . '</td>' .
                    '        </tr>' .
                    $this->paramsToDebug($gcParams) .
                    '    </table>' .
                    '</div>';
            }
        }

        return $s;
    }

    private function _getProductPrice($product, $inclTax)
    {
         $totalvalue = 0;
         
        // check if we are handling grouped products
        if ($product->getTypeId() == 'grouped') {
            $groupedSimpleProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $groupedPrices = array();
         
            foreach ($groupedSimpleProducts as $gSimpleProduct) {
                $groupedPrices[] = $this->_getProductPrice($gSimpleProduct);
            }
            $totalvalue = min($groupedPrices);
        } else { // handle other product types
            $_price = Mage::helper('tax')->getPrice($product, $product->getPrice(), $inclTax);
            $_specialPrice = Mage::helper('tax')->getPrice($product, $product->getSpecialPrice(), $inclTax);
            $_finalPrice = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);
         
            if ($_price == $_finalPrice) { // no special price
                $totalvalue = (float)$_price;
            } else { // get special price
                if ((float)$_finalPrice > 0 && (float)$_finalPrice <= (float)$_price) {
                    $totalvalue = (float)$_finalPrice;
                } else {
                    $totalvalue = (float)$_price;
                }
            }
        }
        return $totalvalue;
    }
}
