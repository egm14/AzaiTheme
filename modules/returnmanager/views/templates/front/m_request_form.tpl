<div id="rm_return_form_popup" class="white_content">
    <div class="rm_innerBox">
        <a href="javascript:void(0)" id='rm_popup_close_icon' class="rm_popup_close_icon">&nbsp;</a>
        <div id="rm_row">
            <div id="rm_popup_pro_info" class="rm_popup_left rm_left rm_box_shadow">
		<div class="rm_pop_heading">
			{l s='Item Detail' mod='returnmanager'}
			<span style='font-size: 12px; text-transform: none;'>{l s='Order' mod='returnmanager'}:&nbsp;{$product['odr_reference']|escape:'htmlall':'UTF-8'}</span>
		</div>
                <div class="rm_row rm_pro_detail_block">
                    <div class="rm_popup_pro_img">
			{if isset($product['img_path'])}
                                <img src="{$product['img_path']|escape:'quotes':'UTF-8'}" onerror="this.src='{$path|escape:'quotes':'UTF-8'}returnmanager/views/img/No-image.jpg'">
			{else}
				<img src="{$link->getImageLink($product['link_rewrite'], $product['id_image'], 'medium_default')|escape:'quotes':'UTF-8'}" onerror="this.src='{$path|escape:'quotes':'UTF-8'}returnmanager/views/img/No-image.jpg'">
			{/if}
                    </div>
                    <div class="rm_popup_pro_name_block">
                        <span class="rm_popup_pro_name">{$product['name']|escape:'htmlall':'UTF-8'}</span>
                        {foreach $product['attributes'] as $p_attr}
                            <span class="rm_popup_pro_attr">{$p_attr|escape:'htmlall':'UTF-8'}</span>
                        {/foreach}
                    </div>
                </div>
                <div class="rm_row">
                    <span class="rm_popup_pro_name uppercase" style="margin-bottom:8px;">{l s='Return Address' mod='returnmanager'}</span>
                    <span class="rm_popup_addr">{$product['shipping_address']|escape:'quotes':'UTF-8'}</span>
                </div>
            </div>
            <div id="rm_popup_request_form" class="rm_popup_right rm_left">
                <div class="rm_row_form">
                    <div class="rm_pop_heading">{l s='Easy Return' mod='returnmanager'}</div>
                    <span class="rm-heading-small rm_form_info_text">{l s='Please fill the below form to make request for return.' mod='returnmanager'}</span>
                </div>
                <div class="rm_row_form">
                    <div class="rm_form_left_half">
                        <span class="rm_form_label">{l s='Return Type' mod='returnmanager'}:</span>
                    </div>
                    <div class="rm_form_right_half">
                        <div class="rm_form_control_block">
                            <select name="rm_return_type" class="rm_form_control" onchange="displayReturnNote(this)">
                                <option value="0">{l s='Select Return Type' mod='returnmanager'}</option>
                                {foreach $product['return_types'] as $type}
                                    <option value="{$type['value']|escape:'htmlall':'UTF-8'}">{$type['text']|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div id="rm_return_type_note" class="rm_form_note">
                            {foreach $product['return_types'] as $type}
                                <p id="rm_return_type_note_{$type['value']|escape:'htmlall':'UTF-8'}">{$type['note']|escape:'htmlall':'UTF-8'}</p>
                            {/foreach}
                        </div>
                    </div>
                </div>
                <div class="rm_row_form">
                    <div class="rm_form_left_half">
                        <span class="rm_form_label">{l s='Quantity' mod='returnmanager'}:</span>
                    </div>
                    <div class="rm_form_right_half">
                        <div class="rm_form_control_block">
                            <select name="rm_return_quantity" class="rm_form_control" style="width:50px;">
                                {for $qty=1 to $product['product_quantity']}
                                    <option value="{$qty|escape:'htmlall':'UTF-8'}">{$qty|escape:'htmlall':'UTF-8'}</option>
                                {/for}
                            </select>
                        </div>
                    </div>
                </div>
                {if count($product['reasons']) > 0}
                <div class="rm_row_form">
                    <div class="rm_form_left_half">
                        <span class="rm_form_label">{l s='Reason' mod='returnmanager'}:</span>
                    </div>
                    <div class="rm_form_right_half">
                        <div class="rm_form_control_block">
                            <select name="rm_return_reason" class="rm_form_control" onchange="displayReasonNote(this)">
                                <option value="0">{l s='Select Reason' mod='returnmanager'}</option>
                                {foreach $product['reasons'] as $reason}
                                    <option value="{$reason['reason_id']|escape:'htmlall':'UTF-8'}">{$reason['text']|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div id="rm_reason_type_note" class="rm_form_note">
                            {foreach $product['reasons'] as $reason}
                                <p id="rm_reason_type_note_{$reason['reason_id']|escape:'htmlall':'UTF-8'}">{$reason['shipping_paid_by']|escape:'htmlall':'UTF-8'}</p>
                            {/foreach}
                        </div>
                    </div>
                </div>
                {/if}
                
                {if $enable_image_upload eq '1'}
{*                    <form enctype="multipart/form-data" action="" method="post" name="image_upload_form" id="image_upload_form">*}
                        <div class="rm_row_form">
                            <div class="rm_form_left_half">
                                <span class="rm_form_label">{l s='Choose File' mod='returnmanager'}:</span>
                            </div>
                            <div class="rm_form_right_half">
                                <div class="rm_form_control_block">
                                    <input type="file" name="rm_return_image" id="rm_return_image" accept=".zip,image/*" class="rm_form_control" onChange="CheckFileType(this);"/>
                                </div>
                            </div>
                        </div>
{*                    </form>*}
                {/if}
                
                <div class="rm_row_form" style="margin-bottom:10px;">
                    <span class="rm_form_label">{l s='Comment' mod='returnmanager'}:</span>
                    <div class="rm_form_right_half rm_control_block_fw">
                        <textarea name="rm_comment" class="rm_form_control rm_textarea"></textarea>
                    </div>
                </div>
                <div class="rm_row rm_responsive_left">
                    <div class="rm_left">
                        <input type="checkbox" name="rm_agree_toc" checked/>
                        {l s='I agree with terms & Conditions.' mod='returnmanager'}(<a id="rm_display_toc" href="javascript:void(0)" class="rm_link">{l s='See here' mod='returnmanager'}</a>)
                    </div>
                    <div class="rm_right">
                        <input type="hidden" name="id_order_detail" value="{$product['id_order_detail']|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="id_order" value="{$product['id_order']|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="id_product" value="{$product['id_product']|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="id_product_attribute" value="{$product['id_product_attribute']|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="id_customer" value="{$product['customer_id']|escape:'htmlall':'UTF-8'}" />
                        <input type="hidden" name="id_policy" value="{$product['policy_id']|escape:'htmlall':'UTF-8'}" />
                        <button id="rm_pop_up_close_btn" class="btn btn-medium btn-primary" ><span>{l s='Close' mod='returnmanager'}</span></button>
                        <button onclick="return rmSubmitReturnRequest(this)" class="btn btn-medium btn-success" ><span>{l s='Submit' mod='returnmanager'}</span></button>                                
                    </div>
                </div>
                <div class="rm_clear"></div>
                <div id="rm_toc_block" class="rm_row">
                    <p>{l s='Terms & Conditions' mod='returnmanager'}:</p>
                    <p id="rm_toc_textarea">
                        {$product['return_toc']|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
            </div>
        </div>
        <div class="rm_clear"></div>    
    </div>        
</div>
<div id="rm_fade" class="black_overlay"></div>



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
* Returns Request Form
*}
