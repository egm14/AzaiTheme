             <div id="show-orden"><i class="fa fa-shopping-bag" aria-hidden="true"></i> {l s='Show Order Summary'}<span>		 
             <a colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total (tax excl.)'}{else}{/if}</a>
              <a colspan="2" class="show-orden-price" id="total_price_without_tax">{displayPrice price=$total_price_without_tax}</a></span>
			  </div>       
		
		<!-- Resumen item -->
		<div id="order-detail-content" class="table_block table-responsive">
	    <table id="cart_summary" class="table table-bordered {if $PS_STOCK_MANAGEMENT}stock-management-on{else}stock-management-off{/if}">
      <thead>
        <tr>
          <th class="cart_product first_item">{l s='Product'}</th>
          <th class="cart_description item">{l s='Description'}</th>
          {if $PS_STOCK_MANAGEMENT}
            {assign var='col_span_subtotal' value='3'}
            <th class="cart_avail item">{l s='Avail.'}</th>
          {else}
            {assign var='col_span_subtotal' value='2'}
          {/if}
          <th class="cart_unit item">{l s='Unit price'}</th>
          <th class="cart_quantity item">{l s='Qty'}</th>
          <th class="cart_total item">{l s='Total'}</th>
          <th class="cart_delete last_item">&nbsp;</th>
        </tr>
      </thead>

      <tbody>
        {assign var='odd' value=0}
        {assign var='have_non_virtual_products' value=false}
        {foreach $products as $product}
          {if $product.is_virtual == 0}
            {assign var='have_non_virtual_products' value=true}            
          {/if}
          {assign var='productId' value=$product.id_product}
          {assign var='productAttributeId' value=$product.id_product_attribute}
          {assign var='quantityDisplayed' value=0}
          {assign var='odd' value=($odd+1)%2}
          {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
          {* Display the product line *}
          {include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
          {* Then the customized datas ones*}
          {if isset($customizedDatas.$productId.$productAttributeId[$product.id_address_delivery])}
            {foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
              <tr
                id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
                class="product_customization_for_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if $odd} odd{else} even{/if} customization alternate_item {if $product@last && $customization@last && !count($gift_products)}last_item{/if}">
                <td></td>
                <td colspan="3">
                  {foreach $customization.datas as $type => $custom_data}
                    {if $type == $CUSTOMIZE_FILE}
                      <div class="customizationUploaded">
                        <ul class="customizationUploaded">
                          {foreach $custom_data as $picture}
                            <li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>
                          {/foreach}
                        </ul>
                      </div>
                    {elseif $type == $CUSTOMIZE_TEXTFIELD}
                      <ul class="typedText">
                        {foreach $custom_data as $textField}
                          <li>
                            {if $textField.name}
                              {$textField.name}
                            {else}
                              {l s='Text #'}{$textField@index+1}
                            {/if}
                            : {$textField.value}
                          </li>
                        {/foreach}
                      </ul>
                    {/if}
                  {/foreach}
                </td>
                <td class="cart_quantity" colspan="1">
                  {if isset($cannotModify) && $cannotModify == 1}
                    <span>{if $quantityDisplayed == 0 && isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}</span>
                  {else}
                    <div class="cart_quantity_button clearfix">
                        {if $product.minimal_quantity < ($customization.quantity -$quantityDisplayed) || $product.minimal_quantity <= 1}
                        <a
                          id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
                          class="cart_quantity_down btn btn-secondary-2 button-minus"
                          href="{$link->getPageLink('cart', true, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
                          rel="nofollow"
                          title="{l s='Subtract'}">
                          <span>
                            <i class="fa fa-minus"></i>
                          </span>
                        </a>
                      {else}
                        <a
                          id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}"
                          class="cart_quantity_down btn btn-secondary-2 button-minus disabled"
                          href="#"
                          title="{l s='Subtract'}">
                          <span>
                            <i class="fa fa-minus"></i>
                          </span>
                        </a>
                      {/if}
                    </div>  
                    <input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}_hidden"/>
                    <input type="text" value="{$customization.quantity}" class="cart_quantity_input form-control grey" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"/>
                    <div class="cart_quantity_button clearfix">

                      <a
                        id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
                        class="cart_quantity_up btn btn-secondary-2 button-plus"
                        href="{$link->getPageLink('cart', true, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
                        rel="nofollow"
                        title="{l s='Add'}">
                        <span>
                          <i class="fa fa-plus"></i>
                        </span>
                      </a>
                    </div>
                  {/if}
                </td>
                <td>
                </td>
                <td class="cart_delete text-center">
                  {if isset($cannotModify) && $cannotModify == 1}
                  {else}
                    <a
                      id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
                      class="cart_quantity_delete"
                      href="{$link->getPageLink('cart', true, NULL, "delete=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;id_address_delivery={$product.id_address_delivery}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
                      rel="nofollow"
                      title="{l s='Delete'}">
                        <i class="fa fa-trash-o"></i>
                    </a>
                  {/if}
                </td>
              </tr>
              {assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
            {/foreach}

            {* If it exists also some uncustomized products *}
            {if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}{/if}
          {/if}
        {/foreach}
        {assign var='last_was_odd' value=$product@iteration%2}
        {foreach $gift_products as $product}
          {assign var='productId' value=$product.id_product}
          {assign var='productAttributeId' value=$product.id_product_attribute}
          {assign var='quantityDisplayed' value=0}
          {assign var='odd' value=($product@iteration+$last_was_odd)%2}
          {assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
          {assign var='cannotModify' value=1}
          {* Display the gift product line *}
          {include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
        {/foreach}
      </tbody>
      {if sizeof($discounts)}
        <tbody>
          {foreach $discounts as $discount}
            {if ((float)$discount.value_real == 0 && $discount.free_shipping != 1) || ((float)$discount.value_real == 0 && $discount.code == '')}
              {continue}
            {/if}
            <tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
              <td class="cart_discount_name" colspan="{if $PS_STOCK_MANAGEMENT}3{else}2{/if}">{$discount.name}</td>
              <td class="cart_discount_price">
                <span class="price-discount">
                {if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
                </span>
              </td>
              <td class="cart_discount_delete">1</td>
              <td class="price_discount_del text-center">
                {if strlen($discount.code)}
                  <a
                    href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
                    class="price_discount_delete"
                    title="{l s='Delete'}">
                    <i class="fa fa-trash-o"></i>
                  </a>
                {/if}
              </td>
              <td class="cart_discount_price">
                <span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
              </td>
            </tr>
          {/foreach}
        </tbody>
      {/if}
    </table>
	<!-- Final resumen item -->