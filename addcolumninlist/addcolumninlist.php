<?php
/*
*
*  @author FuenRob <fuenrob@gmail.com>
*  @copyright  2018 FuenRob
*  International Registered Trademark & Property of FuenRob
*
*/

if (!defined('_PS_VERSION_'))
{
  exit;
}

class AddColumnInList extends Module {
    
    public function __construct() {
        $this->name = 'addcolumninlist';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'FuenRob';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Add column in list product');
        $this->description = $this->l('');
        $this->confirmUninstall = $this->l('¿Are you sure?');
        if (!Configuration::get('MYMODULE_NAME'))
          $this->warning = $this->l('No name provided');
    }

    public function install() {
        if (!parent::install()
            || !$this->registerHook('displayAdminCatalogTwigProductHeader')
            || !$this->registerHook('displayAdminCatalogTwigProductFilter')
            || !$this->registerHook('displayAdminCatalogTwigListingProductFields')
            || !$this->registerHook('actionAdminProductsListingFieldsModifier')
        ) {
        return false;
        }
        
    return true;
    }
        
    public function uninstall() {
        return parent::uninstall();
    }
    
    public function hookDisplayAdminCatalogTwigProductHeader($params)
    {
    return $this->display(__FILE__,'views/templates/hook/displayAdminCatalogTwigProductHeader.tpl');
    }
    
    public function hookDisplayAdminCatalogTwigProductFilter($params)
    {
        $manufacturers = Manufacturer::getManufacturers();
        $this->context->smarty->assign([
            'filter_column_name_manufacturer' => Tools::getValue('filter_column_name_manufacturer', »),
            'manufacturers' => $manufacturers,
        ]);
        return $this->display(__FILE__,'views/templates/hook/displayAdminCatalogTwigProductFilter.tpl');
    }
    
    public function hookDisplayAdminCatalogTwigListingProductFields($params)
    {
        $this->context->smarty->assign('product',$params['product']);
        return $this->display(__FILE__,'views/templates/hook/displayAdminCatalogTwigListingProductFields.tpl');
    }
    
    public function hookActionAdminProductsListingFieldsModifier($params)
    {
    
        $params['sql_select']['manufacturer'] = [
            'table' => 'm',
            'field' => 'name',
            'filtering' => \PrestaShop\PrestaShop\Adapter\Admin\AbstractAdminQueryBuilder::FILTERING_LIKE_BOTH
            ];
        
        $params['sql_table']['m'] = [
            'table' => 'manufacturer',
            'join' => 'LEFT JOIN',
            'on' => 'p.`id_manufacturer` = m.`id_manufacturer`',
        ];
        
        $manufacturer_filter = Tools::getValue('filter_column_name_manufacturer',false);
        if ( $manufacturer_filter && $manufacturer_filter !=  ») {
            $params['sql_where'][] .= " p.id_manufacturer = ".$manufacturer_filter;
        }
    }
        
}