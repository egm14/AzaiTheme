{if (($hide_left_column || $hide_right_column) && ($hide_left_column !='true' || $hide_right_column !='true')) && !$content_only}
  {assign var="columns" value="2"}
{elseif (($hide_left_column && $hide_right_column) && ($hide_left_column =='true' && $hide_right_column =='true')) && !$content_only}
  {assign var="columns" value="1"}
{elseif $content_only}
  {assign var="columns" value="1"}
{else}
  {assign var="columns" value="3"}
{/if}

<div id="idTab5">
  <div id="product_comments_block_tab">
    {if $comments}
      {foreach from=$comments item=comment}
        {if $comment.content}
          <div class="comment row" itemprop="review" itemscope itemtype="https://schema.org/Review">
            <div class="comment_author{if $columns == 2} col-sm-3{elseif $columns == 3} col-xs-12 col-md-4{else} col-sm-3{/if}">
              <span>{l s='Grade' mod='productcomments'}&nbsp;</span>
              <div class="star_content clearfix" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
                {section name="i" start=0 loop=5 step=1}
                  {if $comment.grade le $smarty.section.i.index}
                    <div class="star"></div>
                  {else}
                    <div class="star star_on"></div>
                  {/if}
                {/section}
                <meta itemprop="worstRating" content = "0" />
                <meta itemprop="ratingValue" content = "{$comment.grade|escape:'html':'UTF-8'}" />
                <meta itemprop="bestRating" content = "5" />
              </div>
              <div class="comment_author_infos">
                <strong itemprop="author">{$comment.customer_name|escape:'html':'UTF-8'}</strong>
                <em>{$comment.date_add|escape:'html':'UTF-8'}</em>
              </div>
            </div> <!-- .comment_author -->

            <div class="comment_details{if $columns == 2} col-sm-9{elseif $columns == 3} col-xs-12 col-md-8{else} col-sm-9{/if}">
              <h6 class="title_block">{$comment.title}</h6>
              <p itemprop="reviewBody">{$comment.content|escape:'html':'UTF-8'|nl2br}</p>
              <ul>
                {if $comment.total_advice > 0}
                  <li>
                    {l s='%1$d out of %2$d people found this review useful.' sprintf=[$comment.total_useful,$comment.total_advice] mod='productcomments'}
                  </li>
                {/if}
                {if $is_logged}
                  {if !$comment.customer_advice}
                    <li>
                      <div class="useful-text">{l s='Was this comment useful to you?' mod='productcomments'}</div>
                      <div class="useful-buttons">
                        <button class="usefulness_btn btn btn-primary btn-xs" data-is-usefull="1" data-id-product-comment="{$comment.id_product_comment}">
                          <span>{l s='Yes' mod='productcomments'}</span>
                        </button>
                        <button class="usefulness_btn btn btn-default btn-xs" data-is-usefull="0" data-id-product-comment="{$comment.id_product_comment}">
                          <span>{l s='No' mod='productcomments'}</span>
                        </button>
                      </div>
                    </li>
                  {/if}
                  {if !$comment.customer_report}
                    <li>
                      <span class="report_btn" data-id-product-comment="{$comment.id_product_comment}">
                        {l s='Report abuse' mod='productcomments'}
                      </span>
                    </li>
                  {/if}
                {/if}
              </ul>
            </div><!-- .comment_details -->
          </div> <!-- .comment -->
        {/if}
      {/foreach}
      {if (!$too_early && ($is_logged || $allow_guests))}
        <p class="align_center">
          <a id="new_comment_tab_btn" class="btn btn-primary btn-sm open-comment-form" href="#new_comment_form" title="{l s='Write your review' mod='productcomments'}">
            <span>{l s='Write your review!' mod='productcomments'}</span>
          </a>
        </p>
      {/if}
    {else}
      {if (!$too_early && ($is_logged || $allow_guests))}
        <p class="align_center">
          <a id="new_comment_tab_btn" class="btn btn-default btn-sm open-comment-form" href="#new_comment_form">
            <span>{l s='Be the first to write your review!' mod='productcomments'}</span>
          </a>
        </p>
      {else}
        <p class="align_center">{l s='No customer reviews for the moment.' mod='productcomments'}</p>
      {/if}
    {/if}
  </div> <!-- #product_comments_block_tab -->
</div>

<!-- Fancybox -->
<div style="display: none;">
  <div id="new_comment_form">
    <form id="id_new_comment_form" action="#">
      <h2 class="page-subheading">
        {l s='Write a review' mod='productcomments'}
      </h2>
      <div class="row">
        {if isset($product) && $product}
          <div class="product clearfix col-xs-12 col-sm-6">
            <img src="{$productcomment_cover_image}" alt="{$product->name|escape:'html':'UTF-8'}" />
            <div class="product_desc">
              <p class="product_name">
                <strong>{$product->name}</strong>
              </p>
              {$product->description_short}
            </div>
          </div>
        {/if}
        <div class="new_comment_form_content col-xs-12 col-sm-6">
          <div id="new_comment_form_error" class="error alert alert-danger" style="display: none; padding: 15px 25px">
            <ul></ul>
          </div>
          {if $criterions|@count > 0}
            <ul id="criterions_list">
              {foreach from=$criterions item='criterion'}
                <li>
                  <label>{$criterion.name|escape:'html':'UTF-8'}:</label>
                  <div class="star_content">
                    <input class="star not_uniform" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="1" />
                    <input class="star not_uniform" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="2" />
                    <input class="star not_uniform" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="3" checked="checked" />
                    <input class="star not_uniform" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="4" />
                    <input class="star not_uniform" type="radio" name="criterion[{$criterion.id_product_comment_criterion|round}]" value="5" />
                  </div>
                  <div class="clearfix"></div>
                </li>
              {/foreach}
            </ul>
          {/if}
          <label for="comment_title">
            {l s='Title:' mod='productcomments'} <sup class="required">*</sup>
          </label>
          <input id="comment_title" class="form-control" name="title" type="text" value=""/>
          <label for="content">
            {l s='Comment:' mod='productcomments'} <sup class="required">*</sup>
          </label>
          <textarea id="content" class="form-control" name="content"></textarea>
          {if $allow_guests == true && !$is_logged}
            <label>
              {l s='Your name:' mod='productcomments'} <sup class="required">*</sup>
            </label>
            <input id="commentCustomerName" name="customer_name" type="text" value=""/>
          {/if}
          <div id="new_comment_form_footer">
            <input id="id_product_comment_send" class="form-control" name="id_product" type="hidden" value='{$id_product_comment_form}' />
            <p class="fl required"><sup>*</sup> {l s='Required fields' mod='productcomments'}</p>
            <p class="fr">
              <button id="submitNewMessage" name="submitMessage" type="submit" class="btn btn-default btn-sm">
                <span>{l s='Submit' mod='productcomments'}</span>
              </button>&nbsp;
              {l s='or' mod='productcomments'}&nbsp;
              <a class="closefb" href="#" title="{l s='Cancel' mod='productcomments'}">
                {l s='Cancel' mod='productcomments'}
              </a>
            </p>
            <div class="clearfix"></div>
          </div> <!-- #new_comment_form_footer -->
        </div>
      </div>
    </form><!-- /end new_comment_form_content -->
  </div>
</div>
<!-- End fancybox -->
{strip}
  {addJsDef productcomments_controller_url=$productcomments_controller_url|@addcslashes:'\''}
  {addJsDef moderation_active=$moderation_active|boolval}
  {addJsDef productcomments_url_rewrite=$productcomments_url_rewriting_activated|boolval}
  {addJsDef secure_key=$secure_key}
  {addJsDefL name=confirm_report_message}{l s='Are you sure that you want to report this comment?' mod='productcomments' js=1}{/addJsDefL}
  {addJsDefL name=productcomment_added}{l s='Your comment has been added!' mod='productcomments' js=1}{/addJsDefL}
  {addJsDefL name=productcomment_added_moderation}{l s='Your comment has been added and will be available once approved by a moderator.' mod='productcomments' js=1}{/addJsDefL}
  {addJsDefL name=productcomment_title}{l s='New comment' mod='productcomments' js=1}{/addJsDefL}
  {addJsDefL name=productcomment_ok}{l s='OK' mod='productcomments' js=1}{/addJsDefL}
{/strip}