 <th>
    <select name="filter_column_name_manufacturer" data-toggle="select2">
        <option value="">{l s='Manufacturer' mod='addcolumnlist'}</option>
        {foreach from=$manufacturers item=manufacturer}
            <option value="{$manufacturer.id_manufacturer}" 
            {if $filter_column_name_manufacturer == $manufacturer.id_manufacturer} selected="selected"{/if}>
            {$manufacturer.name}
            </option>
        {/foreach}
    </select>
</th>
 <th>
    <select name="filter_column_name_supplier" data-toggle="select2">
        <option value="">{l s='Suppliers' mod='addcolumnlist'}</option>
        {foreach from=$suppliers item=supplier}
            <option value="{$supplier.id_supplier}" 
            {if $filter_column_name_supplier == $supplier.id_supplier} selected="selected"{/if}>
            {$supplier.name}
            </option>
        {/foreach}
    </select>
</th>