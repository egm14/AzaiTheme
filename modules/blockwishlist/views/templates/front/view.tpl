{assign var='product_price' value=$product->price}

<div id="view_wishlist">
  {capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='My account' mod='blockwishlist'}">{l s='My account' mod='blockwishlist'}</a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    <a href="{$link->getModuleLink('blockwishlist', 'mywishlist')|escape:'html'}" title="{l s='My wishlists' mod='blockwishlist'}">{l s='My wishlists' mod='blockwishlist'}</a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    {$current_wishlist.name|escape:'htmlall':'UTF-8'}
  {/capture}

  <h1 class="page-heading bottom-indent">
    {l s='Wishlist' mod='blockwishlist'}
  </h1>
  {if $wishlists}
    <p>
      <strong class="dark">
        {l s='Other wishlists of %1s %2s:' sprintf=[$current_wishlist.firstname, $current_wishlist.lastname] mod='blockwishlist'}
      </strong>
      {foreach from=$wishlists item=wishlist name=i}
        {if $wishlist.id_wishlist != $current_wishlist.id_wishlist}
          <a href="{$link->getModuleLink('blockwishlist', 'view', ['token' => $wishlist.token])|escape:'html':'UTF-8'}" rel="nofollow" title="{$wishlist.name|escape:'html':'UTF-8'}">
            {$wishlist.name|escape:'htmlall':'UTF-8'}
          </a>
          {if !$smarty.foreach.i.last}
            /
          {/if}
        {/if}
      {/foreach}
    </p>
  {/if}

  <div class="wlp_bought">
    <ul class="row wlp_bought_list">
      {foreach from=$products item=product name=i}
        <li id="wlp_{$product.id_product}_{$product.id_product_attribute}" class="ajax_block_product">
          <div class="row">
            <div id="foto" class="col-xs-12 col-sm-12 col-md-4">
              <div class="product_image">
                <a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'html':'UTF-8'}" title="{l s='Product detail' mod='blockwishlist'}">
                  <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.cover, 'home_default')|escape:'html':'UTF-8'}" alt="{$product.name|escape:'html':'UTF-8'}"/>
                </a>
              </div>
            </div>
            <div id="cont" class="col-xs-12 col-sm-12 col-md-8">
              <div class="product_infos"> <!-- boton de eliminar producto de la lista -->
              <a class="lnkdel" href="javascript:;" onclick="WishlistProductManage('wlp_bought', 'delete', '{$id_wishlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Delete' mod='blockwishlist'}">
                  <i class="fa fa-times-circle"></i>
                </a>
         
                <p id="s_title" class="product-name">
                  {$product.name|truncate:30:'...'|escape:'html':'UTF-8'} 
                      


                  {if isset($product.attributes_small)}
               
                    <a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'html':'UTF-8'}" title="{l s='Product detail' mod='blockwishlist'}">
                      <small>{$product.attributes_small|escape:'html':'UTF-8'}</small>
                    </a>
                  {/if}
                  {if isset($product.price)}
 {if $priceDisplay >= 0 && $priceDisplay <= 2}
                        <span id="our_price_display" itemprop="price" content="{$product.price}">{convertPrice price=$product.price|floatval}</span>
                        <meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                        {hook h="displayProductPriceBlock" product=$product type="price"}
                      {/if} {/if}
                </p>
                <div class="wishlist_product_detail">
                  <p class="form-group">
                    <label for="quantity_{$product.id_product}_{$product.id_product_attribute}">
                      {l s='Quantity' mod='blockwishlist'}:
                    </label>
                    <input class="form-control grey cantidad" type="text" id="quantity_{$product.id_product}_{$product.id_product_attribute}" value="{$product.quantity|intval}" size="3"/>
                  </p>
                  <p class="form-group selector1">
                    <span id="prioridad">
                      <strong>{l s='Priority' mod='blockwishlist'}:</strong> 
                      {$product.priority_name}
                    </span>
                  </p>
                  <div class="btn_action btn-list">
                    {if (isset($product.attribute_quantity) && $product.attribute_quantity >= 1) || (!isset($product.attribute_quantity) && $product.product_quantity >= 1) || (isset($product.allow_oosp) && $product.allow_oosp)}
                      {if !$ajax}
                        <form id="addtocart_{$product.id_product|intval}_{$product.id_product_attribute|intval}" action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
                          <p class="hidden">
                            <input type="hidden" name="id_product" value="{$product.id_product|intval}" id="product_page_product_id"/>
                            <input type="hidden" name="add" value="1"/>
                            <input type="hidden" name="token" value="{$token}"/>
                            <input type="hidden" name="id_product_attribute" id="idCombination" value="{$product.id_product_attribute|intval}"/>
                          </p>
                        </form>
                      {/if}
                      <a href="javascript:void(0);" class="ajax_add_to_cart_button btn btn-default carro" onclick="WishlistBuyProduct('{$token|escape:'html':'UTF-8'}', '{$product.id_product}', '{$product.id_product_attribute}', '{$product.id_product}_{$product.id_product_attribute}', this, {$ajax});" title="{l s='Add to cart' mod='blockwishlist'}" rel="nofollow">
                        <span>{l s='Add to cart' mod='blockwishlist'}</span>
                      </a>
                    {else}
                      <span class="ajax_add_to_cart_button btn btn-default disabled">
                        <span>{l s='Add to cart' mod='blockwishlist'}</span>
                      </span>
                    {/if}
                    <a class="lnk_view btn btn-default ver-p" href="{$link->getProductLink($product.id_product, $product.link_rewrite, $product.category_rewrite)|escape:'html':'UTF-8'}" title="{l s='View' mod='blockwishlist'}" rel="nofollow">
                      <span>{l s='View' mod='blockwishlist'}</span>
                    </a>
                  </div>
                  <!-- .btn_action -->
                </div>
                <!-- .wishlist_product_detail -->
              </div>
              <!-- .product_infos -->
            </div>
          </div>
        </li>
      {/foreach}
    </ul>
  </div>
</div> <!-- #view_wishlist -->