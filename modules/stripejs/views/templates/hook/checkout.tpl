{*
* This file is part of module Stripe Reloaded
*
*  @author    Bellini Services <bellini@bellini-services.com>
*  @copyright 2007-2017 bellini-services.com
*  @license   readme
*
* Your purchase grants you usage rights subject to the terms outlined by this license.
*
* You CAN use this module with a single, non-multi store configuration, production installation and unlimited test installations of PrestaShop.
* You CAN make any modifications necessary to the module to make it fit your needs. However, the modified module will still remain subject to this license.
*
* You CANNOT redistribute the module as part of a content management system (CMS) or similar system.
* You CANNOT resell or redistribute the module, modified, unmodified, standalone or combined with another product in any way without prior written (email) consent from bellini-services.com.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*}

<div class="row">
	<div class="col-xs-12">
		{if isset($smarty.get.stripe_error)}<a id="sc_error" name="sc_error"></a><div class="sc-payment-errors">{l s='There was a problem processing your payment, please double check your payment information and try again.' mod='stripejs'}</div>{/if}

		<form action="{$sc_actionurl|escape:'html':'UTF-8'}" method="POST">
			<p class="payment_module" id="sc_payment_button">
				<span class="payment-div">
					<!--<img src="../themes/azai-theme/assets/payment-credit-card-logo.png" height="30px" width="auto" class="paymente-credit-logos" />-->
				  <script data-keepinline="true" src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				    data-key="{$sc_pk}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-image="{$sc_image}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-name="{$sc_name}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-description="{$sc_description}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-amount="{$sc_amount}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-currency="{$sc_currency}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-locale="auto"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-zip-code="{$sc_zipcode}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-email="{$sc_email}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-allow-remember-me="{$sc_remember_me}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-bitcoin="{$sc_bitcoin}"	{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    data-label="{$sc_label}"		{* cannot be escaped, nothing is posted to server, there is no security issue *}
				    >
				  </script>
			 	
			</span>
			</p>
		</form>
	</div>
</div>
