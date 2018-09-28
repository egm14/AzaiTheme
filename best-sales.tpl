{capture name=path}{l s='Top sellers'}{/capture}

<h1 class="page-heading product-listing">{l s='Top sellers'}</h1>

{if $products}
  <div class="content_sortPagiBar">
    <div class="sortPagiBar clearfix">
      {include file="./product-sort.tpl"}
      {include file="./product-compare.tpl"}
      {include file="./nbr-product-page.tpl"}
    </div>
  </div>

  {include file="./product-list.tpl" products=$products}

  <div class="content_sortPagiBar">
    <div class="bottom-pagination-content clearfix">
      {include file="./pagination.tpl" paginationId='bottom'}
    </div>
  </div>
{else}
  <p class="alert alert-warning">{l s='No top sellers for the moment.'}</p>
{/if}