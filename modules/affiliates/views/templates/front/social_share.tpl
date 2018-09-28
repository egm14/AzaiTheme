{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2017 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
{if isset($AFFILIATE_FACEBOOK) || isset($AFFILIATE_TWITTER) || isset($AFFILIATE_GOOGLE) || isset($AFFILIATEDIGGT)}
	<p class="affiliates_product list-inline no-print">
		{if $AFFILIATE_TWITTER}
			{if $ps_version >= 1.6}
				<button data-type="twitter" type="button" class="btn btn-default btn-twitter link-social-share btn-primary">
					<i class="icon-twitter"></i> {l s='Tweet' mod='affiliates'}
				</button>
			{else}
				<img width="24" data-type="twitter" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/twitter.png" alt="{l s='Tweet' mod='affiliates'}" class="link-social-share" style="cursor: pointer;"/>
			{/if}
				
		{/if}
		{if $AFFILIATE_FACEBOOK}
			{if $ps_version >= 1.6}
				<button data-type="facebook" type="button" class="btn btn-default btn-facebook link-social-share btn-primary">
					<i class="icon-facebook"></i> {l s='Share' mod='affiliates'}
				</button>
			{else}
				<img width="24" data-type="facebook" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/facebook.png" alt="{l s='Facebook Like' mod='affiliates'}" class="link-social-share" style="cursor: pointer;"/>
			{/if}
				
		{/if}
		{if $AFFILIATE_GOOGLE}
			{if $ps_version >= 1.6}
				<button data-type="google-plus" type="button" class="btn btn-default btn-google-plus link-social-share btn-primary">
					<i class="icon-google-plus"></i> {l s='Google+' mod='affiliates'}
				</button>
			{else}
				<img width="24" data-type="google-plus" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/google.png" alt="{l s='Google Plus' mod='affiliates'}" class="link-social-share" style="cursor: pointer;"/>
			{/if}
				
		{/if}
		{if $AFFILIATE_DIGG}
			{if $ps_version >= 1.6}
				<button data-type="digg" type="button" class="btn btn-default link-social-share btn-primary">
					<i class="icon-digg"></i> {l s='Digg' mod='affiliates'}
				</button>
			{else}
				<img width="24" data-type="digg" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/digg.png" alt="{l s='Digg' mod='affiliates'}" class="link-social-share" style="cursor: pointer;"/>
			{/if}
				
		{/if}
	</p>
{/if}