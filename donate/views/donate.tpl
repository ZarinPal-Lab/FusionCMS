<div id="donate">
		<div class="ucp_divider"></div>
		<section id="paypal_area">
			<form method="post" action="{$url}donate/zarinpal" class="page_form">
				<input type="hidden" name="cmd" value="_xclick" />
				<input type="hidden" name="item_name" value="{lang("donation_for", "donate")} {$server_name}" />
				<input type="hidden" name="quantity" value="1" />
				<input type="hidden" name="custom" value="{$user_id}" />
				{foreach from=$donate_zarinpal.values item=value key=key}
					<label for="option_{$key}">
						<input type="radio" name="amount" value="{$key}" id="option_{$key}" {if reset($donate_zarinpal.values) == $value}checked="checked"{/if}/> <b>{$value} {lang("dp", "donate")}</b> {lang("for", "donate")} <b>{$currency_sign}{$key}</b>
					</label>
				{/foreach}

				<input type='submit' value='{lang("pay_paypal", "donate")}' />
				<div class="clear"></div>
			</form>
		</section>
</div>