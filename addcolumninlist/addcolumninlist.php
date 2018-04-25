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

    public function install()
    {
        if (parent::install() && $this->registerHook('actionAdminProductsListingFieldsModifier'))
                  return true;
        return false;
    }

    public function uninstall()
    {
        if (parent::uninstall())
                  return true;
        return false;
    }

    public function hookActionAdminProductsListingFieldsModifier($params)
    {

        $params['sql_select']['manufacturer'] = [
        'table' => 'man',
        'field' => 'name'
        ];

        $params['sql_table']['man'] = [
        'table' => 'manufacturer',
        'join' => 'LEFT JOIN',
        'on' => 'p.`id_manufacturer` = m.`id_manufacturer`',
        ];
        
    }

}