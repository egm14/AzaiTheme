{if isset($no_follow) && $no_follow}
  {assign var='no_follow_text' value=' rel="nofollow"'}
{else}
  {assign var='no_follow_text' value=''}
{/if}

{if isset($p) && $p}
  {if isset($smarty.get.id_category) && $smarty.get.id_category && isset($category)}
    {if !isset($current_url)}
      {assign var='requestPage' value=$link->getPaginationLink('category', $category, false, false, true, false)}
    {else}
      {assign var='requestPage' value=$current_url}
    {/if}
    {assign var='requestNb' value=$link->getPaginationLink('category', $category, true, false, false, true)}
  {elseif isset($smarty.get.id_manufacturer) && $smarty.get.id_manufacturer && isset($manufacturer)}
    {assign var='requestPage' value=$link->getPaginationLink('manufacturer', $manufacturer, false, false, true, false)}
    {assign var='requestNb' value=$link->getPaginationLink('manufacturer', $manufacturer, true, false, false, true)}
  {elseif isset($smarty.get.id_supplier) && $smarty.get.id_supplier && isset($supplier)}
    {assign var='requestPage' value=$link->getPaginationLink('supplier', $supplier, false, false, true, false)}
    {assign var='requestNb' value=$link->getPaginationLink('supplier', $supplier, true, false, false, true)}
  {else}
    {if !isset($current_url)}
      {assign var='requestPage' value=$link->getPaginationLink(false, false, false, false, true, false)}
    {else}
      {assign var='requestPage' value=$current_url}
    {/if}
    {assign var='requestNb' value=$link->getPaginationLink(false, false, true, false, false, true)}
  {/if}
  <!-- Pagination -->
  <div id="pagination{if isset($paginationId)}_{$paginationId}{/if}" class="pagination row">
    <div class="product-count col-xs-12 col-lg-3">
      <p>
        {if ($n*$p) < $nb_products }
          {assign var='productShowing' value=$n*$p}
        {else}
          {assign var='productShowing' value=($n*$p-$nb_products-$n*$p)*-1}
        {/if}
        {if $p==1}
          {assign var='productShowingStart' value=1}
        {else}
          {assign var='productShowingStart' value=$n*$p-$n+1}
        {/if}
        {if $nb_products > 1}
          {l s='Showing %1$d - %2$d of %3$d items' sprintf=[$productShowingStart, $productShowing, $nb_products]}
        {else}
          {l s='Showing %1$d - %2$d of 1 item' sprintf=[$productShowingStart, $productShowing]}
        {/if}
      </p>
    </div>

    {if $start!=$stop}
      <ul class="pagination col-xs-12 col-lg-6">
        {if $p != 1}
          {assign var='p_previous' value=$p-1}
          <li id="pagination_previous{if isset($paginationId)}_{$paginationId}{/if}" class="pagination_previous">
            <a{$no_follow_text} href="{$link->goPage($requestPage, $p_previous)}" title="{l s='Previous'}" rel="prev" class="btn btn-sm btn-secondary-2">
              <i class="fa fa-angle-left"></i>
            </a>
          </li>
        {else}
          <li id="pagination_previous{if isset($paginationId)}_{$paginationId}{/if}" class="disabled pagination_previous">
            <span class="btn btn-sm btn-secondary-2">
              <i class="fa fa-angle-left"></i>
            </span>
          </li>
        {/if}
        {if $start==3}
          <li>
            <a{$no_follow_text} href="{$link->goPage($requestPage, 1)}" class="btn btn-sm btn-secondary-2">
              <span>1</span>
            </a>
          </li>
          <li>
            <a{$no_follow_text} href="{$link->goPage($requestPage, 2)}" class="btn btn-sm btn-secondary-2">
              <span>2</span>
            </a>
          </li>
        {/if}
        {if $start==2}
          <li>
            <a{$no_follow_text} href="{$link->goPage($requestPage, 1)}" class="btn btn-sm btn-secondary-2">
              <span>1</span>
            </a>
          </li>
        {/if}
        {if $start>3}
          <li>
            <a{$no_follow_text} href="{$link->goPage($requestPage, 1)}" class="btn btn-sm btn-secondary-2">
              <span>1</span>
            </a>
          </li>
          <li class="truncate">
            <span>
              <span>...</span>
            </span>
          </li>
        {/if}
        {section name=pagination start=$start loop=$stop+1 step=1}
          {if $p == $smarty.section.pagination.index}
            <li class="active current">
              <span class="btn btn-sm btn-secondary-2">
                <span>{$p|escape:'html':'UTF-8'}</span>
              </span>
            </li>
          {else}
            <li>
              <a{$no_follow_text} href="{$link->goPage($requestPage, $smarty.section.pagination.index)}" class="btn btn-sm btn-secondary-2">
                <span>{$smarty.section.pagination.index|escape:'html':'UTF-8'}</span>
              </a>
            </li>
          {/if}
        {/section}
        {if $pages_nb>$stop+2}
          <li class="truncate">
            <span>
              <span>...</span>
            </span>
          </li>
          <li>
            <a href="{$link->goPage($requestPage, $pages_nb)}" class="btn btn-sm btn-secondary-2">
              <span>{$pages_nb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pages_nb==$stop+1}
          <li>
            <a href="{$link->goPage($requestPage, $pages_nb)}" class="btn btn-sm btn-secondary-2">
              <span>{$pages_nb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pages_nb==$stop+2}
          <li>
            <a href="{$link->goPage($requestPage, $pages_nb-1)}" class="btn btn-sm btn-secondary-2">
              <span>{$pages_nb-1|intval}</span>
            </a>
          </li>
          <li>
            <a href="{$link->goPage($requestPage, $pages_nb)}" class="btn btn-sm btn-secondary-2">
              <span>{$pages_nb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pages_nb > 1 && $p != $pages_nb}
          {assign var='p_next' value=$p+1}
          <li id="pagination_next{if isset($paginationId)}_{$paginationId}{/if}" class="pagination_next">
            <a{$no_follow_text} href="{$link->goPage($requestPage, $p_next)}" class="btn btn-sm btn-secondary-2">
              <i class="fa fa-angle-right"></i>
            </a>
          </li>
        {else}
          <li id="pagination_next{if isset($paginationId)}_{$paginationId}{/if}" class="disabled pagination_next">
            <span class="btn btn-sm btn-secondary-2">
              <i class="fa fa-angle-right"></i>
            </span>
          </li>
        {/if}
      </ul>
    {/if}

    {if $nb_products > $products_per_page && $start!=$stop}
      <form class="showall col-xs-12 col-lg-3" action="{if !is_array($requestNb)}{$requestNb}{else}{$requestNb.requestUrl}{/if}" method="get">
        <div>
          {if isset($search_query) && $search_query}
            <input type="hidden" name="search_query" value="{$search_query|escape:'html':'UTF-8'}" />
          {/if}
          {if isset($tag) && $tag && !is_array($tag)}
            <input type="hidden" name="tag" value="{$tag|escape:'html':'UTF-8'}" />
          {/if}
          <button type="submit">
            <span>{l s='Show all'}</span>
          </button>
          {if is_array($requestNb)}
            {foreach from=$requestNb item=requestValue key=requestKey}
              {if $requestKey != 'requestUrl' && $requestKey != 'p'}
                <input type="hidden" name="{$requestKey|escape:'html':'UTF-8'}" value="{$requestValue|escape:'html':'UTF-8'}" />
              {/if}
            {/foreach}
          {/if}
          <input name="n" id="nb_items" class="hidden" value="{$nb_products}" />
        </div>
      </form>
    {/if}
  </div>
  <!-- /Pagination -->
{/if}