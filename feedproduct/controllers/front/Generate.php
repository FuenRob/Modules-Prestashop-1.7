<?php
class feedproductGenerateModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
            parent::initContent();

            $list_products = Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."product` WHERE `id_shop_default` = 1 AND `active` = 1");

            $products = array();
            
            
            //Create XML
            $xml = new SimpleXMLElement('<xml/>');
            $productsXml = $xml->addChild('products');

            foreach ($list_products as $p){
               $product = array();
               $idProduct = $p['id_product'];
               $idCategory = $p['id_category_default'];
               $idManufacturer = $p['id_manufacturer'];
               $idGender = 8;
               $idLang = 1;
               
               $gender = feedproductGenerateModuleFrontController::getGender($idProduct, $idLang, $idGender);
               $name = feedproductGenerateModuleFrontController::getNameProduct($idProduct);
               $description = feedproductGenerateModuleFrontController::getDescriptionProduct($idProduct);
               $category = feedproductGenerateModuleFrontController::getCategory($idCategory);
               $brand = feedproductGenerateModuleFrontController::getBrand($idManufacturer);
               $quantity = feedproductGenerateModuleFrontController::getQuantity($idProduct);
               $oldPrice = feedproductGenerateModuleFrontController::getPriceRef($idProduct);
               $discount = feedproductGenerateModuleFrontController::getDiscount($idProduct);
               $finalPrice = feedproductGenerateModuleFrontController::getFinalPrice($idProduct);
               $url = feedproductGenerateModuleFrontController::getURL($idProduct);
               
               $list_features = feedproductGenerateModuleFrontController::getFeatures($idProduct);
               $features = array();
               
               $productXml = $productsXml->addChild('product');
               $productXml->addChild('genero', $gender);
               $productXml->addChild('cat', $category);
               $productXml->addChild('brand', $brand);
               $productXml->addChild('store', 'Brandeee');
               $productXml->addChild('id', $idProduct);
               $productXml->addChild('description', '<![CDATA['.htmlspecialchars($description).']]>');
               $productXml->addChild('name', $name);
               $imagesXml = $productXml->addChild('images');

               
                $list_images = feedproductGenerateModuleFrontController::getImages($idProduct);
                $images = array();
                $cont = 1;
                foreach ($list_images as $i){
                     $idImage = $i['id_image'];
                     $link = new Link;
                     $pro = new product($idProduct, false, $idLang);
                     $imagePath = $link->getImageLink($pro->link_rewrite, $idImage, 'home_default');

                     $imagesXml->addChild('img'.$cont, $imagePath);
                     
                     $cont = $cont + 1;
                }
                $productXml->addChild('url', htmlspecialchars($url));
                $featuresXml = $productXml->addChild('features');
               foreach ($list_features as $f){
                    
                    $idFeature = $f['id_feature'];
                    $idFeatureValue = $f['id_feature_value'];
                    
                    $featureName = feedproductGenerateModuleFrontController::getFeatureName($idFeature, $idLang);
                    $featureValue = feedproductGenerateModuleFrontController::getFeatureValue($idFeatureValue, $idLang);
                           
                    $featureXml = $featuresXml->addChild('feature');
                    $featureXml->addChild('name', $featureName);
                    $featureXml->addChild('value', $featureValue);
                    
               }
               
               $productXml->addChild('temporada', '');
               $productXml->addChild('attributes', '');
               $sizesXml = $productXml->addChild('sizes');
               $sizeXml = $sizesXml->addChild('size');
               $sizeXml->addChild('number', $quantity);
               $sizeXml->addChild('oldprice', $oldprice);
               $sizeXml->addChild('discount', $discount);
               $sizeXml->addChild('price', $finalPrice);
               $sizeXml->addChild('offer', '');

            }
            //$Header('Content-type: text/xml');
            print($xml->asXML());
            exit();
            
    }
    
    /** Functions for data **/

    public function getNameProduct($idProduct)
    {
        return Db::getInstance()->getValue("SELECT `name` FROM `"._DB_PREFIX_."product_lang` where `id_shop` = 1 AND `id_lang` = 1 AND `id_product` = ".(int)$idProduct);
    }
    
    public function getDescriptionProduct($idProduct)
    {
        return Db::getInstance()->getValue("SELECT `description` FROM `"._DB_PREFIX_."product_lang` where `id_shop` = 1 AND `id_lang` = 1 AND `id_product` = ".(int)$idProduct);
    }
    
    public function getCategory($idCategory)
    {
        $category = new Category($idCategory, 1);
        return $category->name;
    }
    
    public function getBrand($idManufacturer)
    {
        return Db::getInstance()->getValue("SELECT `name` FROM `"._DB_PREFIX_."manufacturer` where `id_manufacturer` = ".(int)$idManufacturer);
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
        $StockVirtual = $manager->getProductRealQuantities($idProduct,$idCombination,3,true);
        
        return $StockVirtual;
    }
    
    public function getQuantity($idProduct)
    {
        return Db::getInstance()->getValue("SELECT `quantity` FROM `"._DB_PREFIX_."product_sale` where `id_product` = ".(int)$idProduct);
    }

    public function getPriceRef($idProduct)
    {
        $priceRef = Db::getInstance()->getValue("SELECT `price` FROM `"._DB_PREFIX_."product` where `id_product` = ".(int)$idProduct);
        $price = str_replace( "." , "," , ROUND($priceRef,2));
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
        $finalPrice = str_replace( "." , "," , ROUND($price,2));
        return $finalPrice;
    }
    
    public function getManufacturer($idManufacturer)
    {
        $manufacturer = new Manufacturer($idManufacturer, 1);
        return $manufacturer->name;
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
        $price = Product::getPriceStatic((int)$idProduct,true,0,6,null,false,false);
        
        $priceWithout = str_replace ( "." , "," , ROUND($price,2));
        
        return $priceWithout;
    }
    
    public function getGender($idProduct, $idLang, $idGender)
    {
        $id_feature_value = Db::getInstance()->getValue("SELECT `id_feature_value` FROM `"._DB_PREFIX_."feature_product` WHERE `id_product` = ".$idProduct." AND `id_feature` = ".$idGender);
        return Db::getInstance()->getValue("SELECT `value` FROM `"._DB_PREFIX_."feature_value_lang` WHERE `id_feature_value` = ".$id_feature_value." AND `id_lang` = ".$idLang);
    }
    
    public function getFeatures($idProduct)
    {
        return Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."feature_product` WHERE `id_product` = ".$idProduct);
    }
    
    public function getFeatureName($idFeature, $idLang)
    {
        return Db::getInstance()->getValue("SELECT `value` FROM `"._DB_PREFIX_."feature_lang` WHERE `id_feature` = ".$idFeature." AND `id_lang` = ".$idLang);
    }
    
    public function getFeatureValue($idFeatureValue, $idLang)
    {
        return Db::getInstance()->getValue("SELECT `value` FROM `"._DB_PREFIX_."feature_value_lang` WHERE `id_feature_value` = ".$idFeatureValue." AND `id_lang` = ".$idLang);
    }
    
    public function getURL($idProduct)
    {
        
        return _PS_BASE_URL_.'/index.php?controller=product&id_product=' . $idProduct;
    }
    
    public function getImages($idProduct)
    {
        return Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_."image` WHERE `id_product` = ".$idProduct);
    }
    
}