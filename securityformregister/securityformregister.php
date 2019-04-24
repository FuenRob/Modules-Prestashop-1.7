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
class securityformregister extends Module {
    
    public function __construct() {
        $this->name = 'securityformregister';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Roberto Morais';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Security in form register');
        $this->description = $this->l('Increase the security of the customer registration form');
        $this->confirmUninstall = $this->l('¿Are you sure?');
        if (!Configuration::get('MYMODULE_NAME'))
          $this->warning = $this->l('No name provided');
    }
    
   //Install module
   public function install() {
               
        if (!parent::install())
                  return false;
        return true;
   }
   //Uninstall module
   public function uninstall() {
                
        if (!parent::uninstall())
                  return false;
        return true;
   }
}
