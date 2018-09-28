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
<div class="row">
	<div class="center_column col-xs-12 col-sm-12" id="center_column">
		<h4 class="page-heading title_block panel">{l s='Payment Details' mod='affiliates'}</h4>
		<div class="panel-payments">
			<center>
				<br>
				<div class="clearfix"></div>
				<div id="payment-details" class="table-responsive">
					{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD}
						<form id="paymentDetails-form" action="{$link->getModuleLink('affiliates', 'myaffiliates', ['paymentDetails' => '1'])|escape:'htmlall':'UTF-8'}" method="POST" enctype="multipart/form-data">
							<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table well {if $ps_version < 1.6}std{/if}">
							{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(1, $PAYMENT_METHOD)}
							<tr>
								<td class="text-right first_row">
									<label class="required">{l s='Paypal Email' mod='affiliates'}</label>
									<p class="hint-block help-block">{l s='Please enter your paypal email here.' mod='affiliates'}</p>
								</td>
								<td>
									<input id="payment_detail_1" type="text" name="payment_details[1]" class="form-control" value="{if isset($paypal_details) AND $paypal_details}{$paypal_details.details|escape:'htmlall':'UTF-8'}{/if}">
								</td>
							</tr>
							{/if}
							{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(2, $PAYMENT_METHOD)}
							<tr>
								<td class="text-right first_row">
									<label class="required">{l s='Bank Wire' mod='affiliates'}</label>
									<p class="hint-block help-block">{l s='Please enter your Bank details here (i.e bank name, account owner, account number).' mod='affiliates'}</p>
								</td>
								<td>
									<textarea id="payment_detail_2" col="10" row="30" name="payment_details[2]" class="form-control">{if isset($bankwire_details) AND $bankwire_details}{$bankwire_details.details|escape:'htmlall':'UTF-8'}{/if}</textarea>
								</td>
							</tr>
							{/if}
							</table>
			                <div class="pull-right">
			                	{if $ps_version < 1.6}
									<input id="submit-payment-details" type="submit" class="button" name="paymentDetails" value="{l s='Save Details' mod='affiliates'}">
								{else}
									<button id="submit-payment-details" class="btn btn-default button button-medium" name="paymentDetails">
										<span><i class="icon-university"></i> {l s='Save Details' mod='affiliates'}</span>
									</button>
								{/if}
			                </div>
			            </form>
		            {else}
			            <div class="text panel">
			            	<p class="alert alert-info info">{l s='No Payment method available' mod='affiliates'}</p>
			           	</div>
					{/if}
	            </div>
			</center>
		</div>
	</div>
</div>