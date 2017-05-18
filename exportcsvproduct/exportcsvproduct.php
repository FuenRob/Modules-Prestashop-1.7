<?php

/*
* 2017 Roberto Morais
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to fuenrob@gmail.com so we can send you a copy immediately.
*
*  @author Roberto Morais <fuenrob@gmail.com>
*  @copyright  2017 Roberto Morais
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Roberto Morais
*/


if (!defined('_PS_VERSION_'))
{
  exit;
}

class exportcsvproduct extends Module {

    
    public function __construct() {
        $this->name = 'exportcsvproduct';
        $this->tab = 'export';
        $this->version = '1.0.0';
        $this->author = 'Roberto Morais';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Exportar CSV de productos');
        $this->description = $this->l('Descargas un CSV con toda la información de precios de los productos de la tienda');

        $this->confirmUninstall = $this->l('¿Estás seguro de que deseas desinstalar el módulo?');

        if (!Configuration::get('MYMODULE_NAME'))
          $this->warning = $this->l('No name provided');
    }
    
    //Install module
   public function install() {
        // Install Tabs
        $parent_tab = new Tab();
        // Need a foreach for the language
        $parent_tab->name[$this->context->language->id] = $this->l('Exportar CSV');
        $parent_tab->class_name = 'AdminExportProduct';
        $parent_tab->id_parent = 9; // Catalogo tab
        $parent_tab->module = $this->name;
        $parent_tab->add();
        
        if (!parent::install())
                  return false;
        return true;
   }
//Uninstall module
   public function uninstall() {
        // Uninstall Tabs
        $id_tab = (int)Tab::getIdFromClassName('AdminExportProduct');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        
        if (!parent::uninstall())
                  return false;
        return true;
   }
}