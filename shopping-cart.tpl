{capture name=path}{l s='Your shopping cart'}{/capture}

<h1 id="cart_title" class="page-heading">{l s='Shopping-cart summary'}
  {if !isset($empty) && !$PS_CATALOG_MODE}
    <span class="heading-counter">{l s='Your shopping cart contains:'}
      <span id="summary_products_quantity">{$productNumber} {if $productNumber == 1}{l s='product'}{else}{l s='products'}{/if}</span>
    </span>
  {/if}
</h1>

{if isset($account_created)}
  <p class="alert alert-success">
    {l s='Your account has been created.'}
  </p>
{/if}

{assign var='current_step' value='summary'}
{include file="$tpl_dir./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}

{if isset($empty)}
  <p class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
{elseif $PS_CATALOG_MODE}
  <p class="alert alert-warning">{l s='This store has not accepted your new order.'}</p>
{else}
  <p id="emptyCartWarning" class="alert alert-warning unvisible">{l s='Your shopping cart is empty.'}</p>
  {if isset($lastProductAdded) && $lastProductAdded}
    <div class="cart_last_product">
      <div class="cart_last_product_header">
        <div class="left">{l s='Last product added'}</div>
      </div>
      <a class="cart_last_product_img" href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, $lastProductAdded.id_shop)|escape:'html':'UTF-8'}">
        <img src="{$link->getImageLink($lastProductAdded.link_rewrite, $lastProductAdded.id_image, 'tm_small_default')|escape:'html':'UTF-8'}" alt="{$lastProductAdded.name|escape:'html':'UTF-8'}"/>
      </a>
      <div class="cart_last_product_content">
        <p class="product-name">
          <a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
            {$lastProductAdded.name|escape:'html':'UTF-8'}
          </a>
        </p>
        {if isset($lastProductAdded.attributes) && $lastProductAdded.attributes}
          <small>
            <a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
              {$lastProductAdded.attributes|escape:'html':'UTF-8'}
            </a>
          </small>
        {/if}
      </div>
    </div>
  {/if}
  {assign var='total_discounts_num' value="{if $total_discounts != 0}1{else}0{/if}"}
  {assign var='use_show_taxes' value="{if $use_taxes && $show_taxes}2{else}0{/if}"}
  {assign var='total_wrapping_taxes_num' value="{if $total_wrapping != 0}2{else}0{/if}"}
  {* eu-legal *}
  {hook h="displayBeforeShoppingCartBlock"}

  <div id="order-detail-content" class="table_block table-responsive">
    <table id="cart_summary" class="table table-bordered {if $PS_STOCK_MANAGEMENT}stock-management-on{else}stock-management-off{/if}">
        <tfoot>
        {assign var='rowspan_total' value=2+$total_discounts_num+$total_wrapping_taxes_num}
        {if $use_taxes && $show_taxes && $total_tax != 0}
          {assign var='rowspan_total' value=$rowspan_total+1}
        {/if}

        {if $priceDisplay != 0}
          {assign var='rowspan_total' value=$rowspan_total+1}
        {/if}

        {if $total_shipping_tax_exc <= 0 && (!isset($isVirtualCart) || !$isVirtualCart) && $free_ship}
          {assign var='rowspan_total' value=$rowspan_total+1}
        {else}
          {if $use_taxes && $total_shipping_tax_exc != $total_shipping}
            {if $priceDisplay && $total_shipping_tax_exc > 0}
              {assign var='rowspan_total' value=$rowspan_total+1}
            {elseif $total_shipping > 0}
              {assign var='rowspan_total' value=$rowspan_total+1}
            {/if}
          {elseif $total_shipping_tax_exc > 0}
            {assign var='rowspan_total' value=$rowspan_total+1}
          {/if}
        {/if}
        {if $use_taxes}
          {if $priceDisplay}
            <tr class="cart_total_price">
              <td rowspan="{$rowspan_total}" colspan="3" id="cart_voucher" class="cart_voucher">
                {if $voucherAllowed}
                  <form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
                    <fieldset>
                      <h4>{l s='Add code: Gift Card or Credit Slip'}</h4>
                      <input type="text" class="discount_name form-control" id="discount_name" name="discount_name" placeholder="Enter your code" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
                      <input type="hidden" name="submitDiscount" />
                      <button type="submit" name="submitAddDiscount" class="btn btn-secondary btn-sm">
                        <span>{l s='OK'}</span>
                      </button>
                    </fieldset>
                  </form>
                  {if $displayVouchers}
                    <p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
                    <div id="display_cart_vouchers">
                      {foreach $displayVouchers as $voucher}
                        {if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
                      {/foreach}
                    </div>
                  {/if}
                {/if}
              </td>
              <td colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total products (tax excl.)'}{else}{l s='Total products'}{/if}</td>
              <td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
            </tr>
          {else}
            <tr class="cart_total_price">
              <td rowspan="{$rowspan_total}" colspan="2" id="cart_voucher" class="cart_voucher">
                {if $voucherAllowed}
                  <form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
                    <fieldset>
                      <h4>{l s='Add code: Gift Card or Credit Slip'}</h4>
                      <input type="text" class="discount_name form-control" id="discount_name" name="discount_name" placeholder="Enter your code" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
                      <input type="hidden" name="submitDiscount" />
                      <button type="submit" name="submitAddDiscount" class="btn btn-secondary btn-sm"><span>{l s='OK'}</span></button>
                    </fieldset>
                  </form>
                  {if $displayVouchers}
                    <p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
                    <div id="display_cart_vouchers">
                      {foreach $displayVouchers as $voucher}
                        {if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
                      {/foreach}
                    </div>
                  {/if}
                {/if}
              </td>
              <td colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total products (tax incl.)'}{else}{l s='Total products'}{/if}</td>
              <td colspan="2" class="price" id="total_product">{displayPrice price=$total_products_wt}</td>
            </tr>
          {/if}
        {else}
          <tr class="cart_total_price">
            <td rowspan="{$rowspan_total}" colspan="2" id="cart_voucher" class="cart_voucher">
              {if $voucherAllowed}
                <form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher">
                  <fieldset>
                    <h4>{l s='Add code: Gift Card or Credit Slip'}</h4>
                    <input type="text" class="discount_name form-control" id="discount_name" name="discount_name" placeholder="Enter your code" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
                    <input type="hidden" name="submitDiscount" />
                    <button type="submit" name="submitAddDiscount" class="btn btn-secondary btn-sm">
                      <span>{l s='OK'}</span>
                    </button>
                  </fieldset>
                </form>
                {if $displayVouchers}
                  <p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
                  <div id="display_cart_vouchers">
                    {foreach $displayVouchers as $voucher}
                      {if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
                    {/foreach}
                  </div>
                {/if}
              {/if}
            </td>
            <td colspan="{$col_span_subtotal}" class="text-right">{l s='Total products'}</td>
            <td colspan="2" class="price" id="total_product">{displayPrice price=$total_products}</td>
          </tr>
        {/if}
        <tr{if $total_wrapping == 0} style="display: none;"{/if}>
          <td colspan="3" class="text-right">
            {if $use_taxes}
              {if $display_tax_label}{l s='Total gift wrapping (tax incl.)'}{else}{l s='Total gift-wrapping cost'}{/if}
            {else}
              {l s='Total gift-wrapping cost'}
            {/if}
          </td>
          <td colspan="2" class="price-discount price" id="total_wrapping">
            {if $use_taxes}
              {if $priceDisplay}
                {displayPrice price=$total_wrapping_tax_exc}
              {else}
                {displayPrice price=$total_wrapping}
              {/if}
            {else}
              {displayPrice price=$total_wrapping_tax_exc}
            {/if}
          </td>
        </tr>
        {if $total_shipping_tax_exc <= 0 && (!isset($isVirtualCart) || !$isVirtualCart) && $free_ship}
          <tr class="cart_total_delivery{if !$opc && (!isset($cart->id_address_delivery) || !$cart->id_address_delivery)} unvisible{/if}">
            <td colspan="3" class="text-right">{l s='Total shipping'}</td>
            <td colspan="2" class="price" id="total_shipping">{l s='Free shipping!'}</td>
          </tr>
        {else}
          {if $use_taxes && $total_shipping_tax_exc != $total_shipping}
            {if $priceDisplay}
              <tr class="cart_total_delivery{if $total_shipping_tax_exc <= 0} unvisible{/if}">
                <td colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total shipping (tax excl.)'}{else}{l s='Total shipping'}{/if}</td>
                <td colspan="2" class="price" id="total_shipping">{displayPrice price=$total_shipping_tax_exc}</td>
              </tr>
            {else}
              <tr class="cart_total_delivery{if $total_shipping <= 0} unvisible{/if}">
                <td colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total shipping (tax incl.)'}{else}{l s='Total shipping'}{/if}</td>
                <td colspan="2" class="price" id="total_shipping" >{displayPrice price=$total_shipping}</td>
              </tr>
            {/if}
          {else}
            <tr class="cart_total_delivery{if $total_shipping_tax_exc <= 0} unvisible{/if}">
              <td colspan="{$col_span_subtotal}" class="text-right">{l s='Total shipping'}</td>
              <td colspan="2" class="price" id="total_shipping" >{displayPrice price=$total_shipping_tax_exc}</td>
            </tr>
          {/if}
        {/if}
        <tr class="cart_total_voucher{if $total_discounts == 0} unvisible{/if}">
          <td colspan="{$col_span_subtotal}" class="text-right">
            {if $display_tax_label}
              {if $use_taxes && $priceDisplay == 0}
                {l s='Total vouchers (tax incl.)'}
              {else}
                {l s='Total vouchers (tax excl.)'}
              {/if}
            {else}
              {l s='Total vouchers'}
            {/if}
          </td>
          <td colspan="2" class="price-discount price" id="total_discount">
            {if $use_taxes && $priceDisplay == 0}
              {assign var='total_discounts_negative' value=$total_discounts * -1}
            {else}
              {assign var='total_discounts_negative' value=$total_discounts_tax_exc * -1}
            {/if}
            {displayPrice price=$total_discounts_negative}
          </td>
        </tr>
        {if $use_taxes && $show_taxes && $total_tax != 0 }
          {if $priceDisplay != 0}
            <tr class="cart_total_price">
              <td colspan="{$col_span_subtotal}" class="text-right">{if $display_tax_label}{l s='Total (tax excl.)'}{else}{l s='Total'}{/if}</td>
              <td colspan="2" class="price" id="total_price_without_tax">{displayPrice price=$total_price_without_tax}</td>
            </tr>
          {/if}
          <tr class="cart_total_tax">
            <td colspan="{$col_span_subtotal}" class="text-right">{l s='Tax'}</td>
            <td colspan="2" class="price" id="total_tax">{displayPrice price=$total_tax}</td>
          </tr>
        {/if}
        <tr id="this" class="cart_total_price">
          <td colspan="{$col_span_subtotal}" class="total_price_container text-right">
            <span>{l s='Total'}</span>
            <div class="hookDisplayProductPriceBlock-price">
              {hook h="displayCartTotalPriceLabel"}
            </div>
          </td>
          {if $use_taxes}
            <td colspan="2" class="price" id="total_price_container">
              <span id="total_price">{displayPrice price=$total_price}</span>
            </td>
          {else}
            <td colspan="2" class="price" id="total_price_container">
              <span id="total_price">{displayPrice price=$total_price_without_tax}</span>
            </td>
          {/if}
        </tr>
      </tfoot>
      
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
  </div> <!-- end order-detail-content -->

  {if $show_option_allow_separate_package}
    <p>
      <input type="checkbox" name="allow_seperated_package" id="allow_seperated_package" {if $cart->allow_seperated_package}checked="checked"{/if} autocomplete="off"/>
      <label for="allow_seperated_package" class="checkbox inline">
        {l s='Send available products first'}
      </label>
    </p>
  {/if}

  {* Define the style if it doesn't exist in the PrestaShop version*}
  {* Will be deleted for 1.5 version and more *}
  {if !isset($addresses_style)}
    {$addresses_style.company = 'address_company'}
    {$addresses_style.vat_number = 'address_company'}
    {$addresses_style.firstname = 'address_name'}
    {$addresses_style.lastname = 'address_name'}
    {$addresses_style.address1 = 'address_address1'}
    {$addresses_style.address2 = 'address_address2'}
    {$addresses_style.city = 'address_city'}
    {$addresses_style.country = 'address_country'}
    {$addresses_style.phone = 'address_phone'}
    {$addresses_style.phone_mobile = 'address_phone_mobile'}
    {$addresses_style.alias = 'address_title'}
  {/if}

  {if !$advanced_payment_api && ((!empty($delivery_option) && (!isset($isVirtualCart) || !$isVirtualCart)) || $delivery->id || $invoice->id) && !$opc}
    <p class="cart_navigation clearfix">
    {if !$opc}
      <a
        href="{if $back}{$link->getPageLink('order', true, NULL, 'step=1&amp;back={$back}')|escape:'html':'UTF-8'}{else}{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}{/if}"
        class="btn btn-secondary standard-checkout btn-md icon-right"
        title="{l s='Proceed to checkout'}">
        <span>
          {l s='Proceed to checkout'}
        </span>
      </a>
    {/if}
    <a
      href="{if (isset($smarty.server.HTTP_REFERER) && ($smarty.server.HTTP_REFERER == $link->getPageLink('order', true) || $smarty.server.HTTP_REFERER == $link->getPageLink('order-opc', true) || strstr($smarty.server.HTTP_REFERER, 'step='))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index')}{else}{$smarty.server.HTTP_REFERER|escape:'html':'UTF-8'|secureReferrer}{/if}"
      class="btn btn-secondary btn-md icon-left"
      title="{l s='Continue shopping'}">
        <span>{l s='Continue shopping'}</span>
    </a>
  </p>

  {/if}

  <div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>
  <div class="clear"></div>
  <div class="cart_navigation_extra">
    <div id="HOOK_SHOPPING_CART_EXTRA">{if isset($HOOK_SHOPPING_CART_EXTRA)}{$HOOK_SHOPPING_CART_EXTRA}{/if}</div>
  </div>

  {strip}
    {addJsDef deliveryAddress=$cart->id_address_delivery|intval}
    {addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
    {addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
  {/strip}
{/if}