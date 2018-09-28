<div id="block">
    <div class="rm_row rm_b_margin">
        <div class="rm_half_row rm_left">
            <div class="rm_order_heading">{if isset($customer_info['firstname'])}{$customer_info['firstname']|escape:'htmlall':'UTF-8'} {/if} {if isset($customer_info['lastname'])} {$customer_info['lastname']|escape:'htmlall':'UTF-8'} {/if}</div>
            <br>
            <div class="rm_order_heading" style="font-size:14px;">{$customer_info['email']|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="rm_half_row rm_right">
            <div class="rm_order_heading">
                <button id="rm_find_another_order_btn" class="btn btn-medium btn-warning" style='font-weight:bold;'><span>{l s='Find another order' mod='returnmanager'}</span></button>
            </div>    
        </div>        
    </div>
    
    <table class="rm_single_order_row" cellspaing="4" cellpadding="2">
        <tr class="rm_order_header">
            <td class="rm_label_hightlight" colspan='2'>{$reference_id|escape:'htmlall':'UTF-8'}</td>
            <td class="rm_order_header rm_right_align" colspan='3'>
                <div class="rm_label_orderstatus" style="background-color:{$order_state_color|escape:'htmlall':'UTF-8'}">{$order_state|escape:'htmlall':'UTF-8'}</div>
            </td>
        </tr>

        {foreach $products as $product}            
            <tr class="rm_pro_row">
                <td class="rm_img_col"><a href="{$link->getProductLink($product['id_product'])|escape:'htmlall':'UTF-8'}"><img src='{$link->getImageLink( '', $product['id_image'], 'home_default')|escape:'htmlall':'UTF-8'}' height='auto' width='100'/></a></td>
                <td class="rm_description_col">
                    <div class="rm_product_name">
                        <a href="{$link->getProductLink($product['id_product'])|escape:'htmlall':'UTF-8'}" >{$product['name']|escape:'htmlall':'UTF-8'}</a>
                    </div>
                    {foreach $product['attributes'] as $p_attr}
                        <div class="rm_product_attr">{$p_attr|escape:'htmlall':'UTF-8'}</div>                        
                    {/foreach}                    
                    <div>
                        {l s='Quantity' mod='returnmanager'}: {$product['quantity']|escape:'htmlall':'UTF-8'}
                        {if isset($product['total_return_qty'])}
                            <br>{l s='Returned' mod='returnmanager'}: {$product['total_return_qty']|escape:'htmlall':'UTF-8'}
                        {/if}
                    </div>
                </td>

                <td class="rm_center_align">{$product['price']|escape:'htmlall':'UTF-8'}</td>
                
                <td class="rm_center_align">
                    {if $product['is_delivered']}
                        {if $product['is_returnable']}
                            {if $product['is_creditable']}
                                <span class="rm_product_return_stat">{l s='Creditable within' mod='returnmanager'} {$product['credit_days']|escape:'htmlall':'UTF-8'} {l s='Days' mod='returnmanager'}</span>
                            {/if}
                            {if $product['is_refundable']}
                                <span class="rm_product_return_stat">{l s='Refundable within' mod='returnmanager'} {$product['refund_days']|escape:'htmlall':'UTF-8'} {l s='Days' mod='returnmanager'}</span>
                            {/if}
                            {if $product['is_replacement']}
                                <span class="rm_product_return_stat">{l s='Replacement within' mod='returnmanager'} {$product['replacement_days']|escape:'htmlall':'UTF-8'} {l s='Days' mod='returnmanager'}</span>
                            {/if}
                        {else if $product['already_returned']}
                            <span class="rm_product_return_stat">{l s='Already Returned' mod='returnmanager'}</span>                            
                        {else}
                            <span class="rm_product_return_stat">{l s='Not Applicable' mod='returnmanager'}</span>
                        {/if}
                    {else}
                        <span class="rm_product_return_stat">{l s='Not Applicable' mod='returnmanager'}</span>
                    {/if}
                </td>

                <td class="{if $product['is_creditable'] || $product['is_refundable'] || $product['is_replacement']}rm_right_align{else}rm_center_align{/if}">
                    {if !$product['is_delivered']}
                        {l s='Product is not delievered yet.' mod='returnmanager'}
                    {else if $product['already_returned']}
                        {l s='Already Returned' mod='returnmanager'}
                    {else if !$product['is_returnable']}
                        {l s='Not Applicable' mod='returnmanager'}
                    {else if $product['is_creditable'] || $product['is_refundable'] || $product['is_replacement']}
                        {$value_arr[0] = $product['id_order']}
                        {$value_arr[1] = $product['id_order_detail']}
                        {$value_arr[2] = $product['id_product']}
                        {$value_arr[3] = $rm_customer_id}
                        {$value_arr[4] = $product['rm_policy_id']}
                        {$value_str= '_'|implode:$value_arr}
                        <button class="btn btn-medium btn-primary" id='return' style='font-weight:bold;' onclick="getReturnForm('{$value_str|escape:'htmlall':'UTF-8'}', this);"><span>{l s='Return' mod='returnmanager'}</span></button>   
                    {/if}
                </td>
            </tr>
        {/foreach}
        <tr class="rm_single_order_total_row">
            <td colspan='3' style='text-align: left;'>{l s='Order placed on' mod='returnmanager'}: {$order_date|escape:'htmlall':'UTF-8'}</td>
            <td colspan='2' style="text-align: right; font-weight: bold;"><div>{l s='Order Total' mod='returnmanager'}: {$cart_total|escape:'htmlall':'UTF-8'}</div>
                <div>{l s='Total Paid' mod='returnmanager'}: {$total_paid|escape:'htmlall':'UTF-8'}</div>
            </td>
        </tr>
    </table>
    <br>
    <div class="rm_row rm_history_header">
        <div class="rm_half_row rm_left">
            <div class="rm_order_heading"><div class="rm_label_orderstatus" style="background-color:black; color: white;">{l s='Return History' mod='returnmanager'}</div></div>
        </div>        
    </div>
    {if count($return_history) > 0}   
    <table class="rm_single_order_row" cellspacing="4" cellpadding="1">
    <thead>
        <tr class="rm_order_header">
            <th>{l s='Product' mod='returnmanager'}</th>
            <th>{l s='Status' mod='returnmanager'}</th>
            <th>{l s='Return Type' mod='returnmanager'}</th>
            <th>{l s='Notes' mod='returnmanager'}</th>
            <th>{l s='Request Date' mod='returnmanager'}</th>
            <th>{l s='Approve/Deny' mod='returnmanager'}</th>            
        </tr>
    </thead>

    <tbody>    
    {foreach $return_history as $return}            
        <tr class="rm_pro_row">            
            <td>
                <label class='rm_product_name'><a href="{$link->getProductLink($return['product_id'])|escape:'htmlall':'UTF-8'}">{$return['product_name']|escape:'htmlall':'UTF-8'}</a></label>{if $return['product_attr'] != ''}<br>{$return['product_attr']|escape:'htmlall':'UTF-8'}{/if}<br>{l s='Quantity' mod='returnmanager'}: {$return['quantity']|escape:'htmlall':'UTF-8'}
            </td>
            <td>{$return['status']|escape:'htmlall':'UTF-8'}</td>
            <td>{$return['return_type']|escape:'htmlall':'UTF-8'}</td>
            <td style="max-width: 300px;">{if $return['comment'] != ''}{$return['comment']|escape:'htmlall':'UTF-8'}{else}<span class='vss_italic_text'>{l s='No comments by customer.' mod='returnmanager'}</span>{/if}</td>
            <td>{$return['request_date']|escape:'htmlall':'UTF-8'}</td>
            <td>
			{if $return['active'] eq 2}
				<label style='color: green;'>{l s='Approved' mod='returnmanager'}</label>
				{if isset($return_slip) && $return_slip == 1}
					<p><a target="_blank" class="rm_return_slip_link" href="{$return['slip_link']|escape:'htmlall':'UTF-8'}">{l s='Return Slip' mod='returnmanager'}</a></p>
				{/if}
			{else if $return['active'] eq 3}
				<label style='color: red;'>{l s='Denied' mod='returnmanager'}</label>
			{else if $return['active'] eq 4}<label style='color: darkblue;'>
				{l s='Completed' mod='returnmanager'}</label>
			{else}
				{l s='Pending' mod='returnmanager'}{/if}
	    </td>
        </tr>
    {/foreach}
    </tbody>        
    </table>
    {else}
        <div class="rm_history_nodata">{l s='No Data to Display' mod='returnmanager'}</div>
    {/if}
</div>

{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer tohttp://www.prestashop.com for more information.
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* @category  PrestaShop Module
* @author    knowband.com <support@knowband.com>
* @copyright 2015 Knowband
* @license   see file: LICENSE.txt
*
* Description
*
* Return Manager Order Detail Page
*}