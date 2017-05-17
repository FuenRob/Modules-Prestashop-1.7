<?php

class AdminExportProductController extends ModuleAdminController
{    

    public $available_fields;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->meta_title = $this->l('Export Product');
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function renderView()
    {

        return $this->renderConfigurationForm();

    }

    public function renderConfigurationForm()
    {
        $helper = new HelperForm();
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int)$this->context->shop->id;
        
        $inputs = array(
            array(
                'type' => 'text',
                'label' => $this->l('Delimiter'),
                'name' => 'export_delimiter',
                'value' => ';',
                'desc' => $this->l('Escoge el delimitador de campos que va a separar las columnas del CSV.'),
            ),
        );
        
        $fields_form_price = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Exportar listado de precios'),
                    'icon' => 'icon-cogs'
                ),
                'desc' => $this->l('Descarga un CSV con los precios de toda la tienda'),
                'input' => $inputs,
                'submit' => array(
                    'name' => $helper->submit_action = 'submitExportPrice',
                    'title' => $this->l('Export')
                )
            ),
        );
        
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        //$helper->submit_action = 'submitExport';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminExportProduct');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form_price));
    }


    public function getConfigFieldsValues()
    {
        return array(
            'export_delimiter' => ';'
        );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExportPrice')) {
            $delimiter = Tools::getValue('export_delimiter');
            $idLang = (int)$this->context->language->id;
            
            set_time_limit(0);
            $fileName = 'export_product_price.csv';
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$fileName}");
            header("Expires: 0");
            header("Pragma: public");
            echo "\xEF\xBB\xBF";
            
            $link = new Link();
            
            $f = fopen(
                'php://output',
                'w'
            );


            fputcsv(
                $f,
                array('ID','REF', 'MARCA', 'NOMBRE', 'PRECIO COSTE (SIN IVA)', 'PRECIO COSTE (CON IVA)', 'PVP SIN DESCUENTO', 'PVP CON DESCUENTO'),
                $delimiter,
                '"'
            );

            
            $products = Db::getInstance()->executeS("SELECT `id_product`, `reference`, `id_manufacturer` FROM `"._DB_PREFIX_."product` WHERE `active` = 1");
            
            foreach ($products as $product) {
                $line = array();
                
                $idProduct = $product['id_product'];
                $sku = $product['reference'];
                $name = AdminExportProductController::getNameProduct($idProduct);
                $manufacturer = AdminExportProductController::getManufacturer($product['id_manufacturer']);
                $price = AdminExportProductController::getPriceRef($idProduct);
                $priceWithout = AdminExportProductController::getWithout($idProduct);
                $priceCost = AdminExportProductController::getPriceCost($idProduct);
                $priceCostSinIva = AdminExportProductController::getPriceCostSinIva($idProduct);
                $finalPrice = AdminExportProductController::getFinalPrice($idProduct);
                
                // Linea del CSV
                $line = array($idProduct, $sku, $manufacturer, $name, $priceCostSinIva, $priceCost, $priceWithout, $finalPrice);

                fputcsv(
                    $f,
                    $line,
                    $delimiter,
                    '"'
                );
            }
            fclose($f);
            die();
        }
    }

    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }
    
    public function getNameProduct($idProduct)
    {
        return Db::getInstance()->getValue("SELECT `name` FROM `"._DB_PREFIX_."product_lang` where `id_shop` = 1 AND `id_lang` = 1 AND `id_product` = ".(int)$idProduct);
    }
    
    public function getLocalWahrehouse($idProduct,$idCombination)
    {
        $manager = StockManagerFactory::getManager();
        $StockReal = $manager->getProductRealQuantities($idProduct,$idCombination,1,true);
        if ($StockReal == null){
            return 0;
        }else{
            return $StockReal;
        }
    }
    
    public function getVirtualWahrehouse($idProduct,$idCombination)
    {
        $manager = StockManagerFactory::getManager();
        $StockReal = $manager->getProductRealQuantities($idProduct,$idCombination,3,true);
        
        return $StockReal;
    }
    
    public function getPriceRef($idProduct)
    {
        $priceRef = Db::getInstance()->getValue("SELECT `price` FROM `"._DB_PREFIX_."product` where `id_product` = ".(int)$idProduct);
        $price = str_replace ( "." , "," , ROUND($priceRef,2));
        return $price;
    }
    
    public function getDiscount($idProduct)
    {
        
        $product = new Product($idProduct);
        return $product->reduction;       
        
    }
    
    public function getFinalPrice($idProduct)
    {
        $price = Product::getPriceStatic($idProduct);
        $finalPrice = str_replace ( "." , "," , ROUND($price,2));
        return $finalPrice;
    }
    
    public function getManufacturer($idManufacturer)
    {
        $manufacturer = new Manufacturer($idManufacturer, 1);
        return $manufacturer->name;
    }
    
    public function getCategory($idCategory)
    {
        $category = new Category($idCategory, 1);
        return $category->name;
    }
    
    public function getPriceCost($idProduct)
    {
        $wholesale_price = Db::getInstance()->getValue("SELECT `wholesale_price` FROM `"._DB_PREFIX_."product` where `id_product` = ".(int)$idProduct);
        $priceCost = $wholesale_price+($wholesale_price*0.21);
        $price = str_replace ( "." , "," , ROUND($priceCost,2));
        return $price;
    }
    
    public function getPriceCostSinIva($idProduct)
    {
        $wholesale_price = Db::getInstance()->getValue("SELECT `wholesale_price` FROM `"._DB_PREFIX_."product` where `id_product` = ".(int)$idProduct);
        $price = str_replace ( "." , "," , ROUND($wholesale_price,2));
        return $price;
    }
    
    public function getWithout($idProduct)
    {
        $price = Product::getPriceStatic(
                (int)$idProduct,
                true,
                0,
                6,
                null,
                false,
                false
            );
        
        $priceWithout = str_replace ( "." , "," , ROUND($price,2));
        
        return $priceWithout;
    }
    
    public function getGender($id_product, $idLang, $id_gender)
    {
        $id_feature_value = Db::getInstance()->getValue("SELECT `id_feature_value` FROM `"._DB_PREFIX_."feature_product WHERE `id_product` = ".$id_product." AND `id_feature` = ".$id_gender);
        return Db::getInstance()->getValue("SELECT `value` FROM `ps_feature_value_lang` WHERE `id_feature_value` = ".$id_feature_value);
    }

}
