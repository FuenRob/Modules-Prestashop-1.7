<?php
class Validate extends ValidateCore
{
    public static function isCustomerName($name)
    {
        if (preg_match(Tools::cleanNonUnicodeSupport('/www|http/ui'),$name)) {
        	return false;
        }

        return preg_match(Tools::cleanNonUnicodeSupport('/^[^0-9!\[\]<>,;?=+()@#"°{}_$%:\/\\\*\^]*$/u'), $name);
    }
}