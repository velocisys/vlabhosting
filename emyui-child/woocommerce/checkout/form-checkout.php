<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
do_action( 'woocommerce_before_checkout_form', $checkout );
// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
	<?php if ( $checkout->get_checkout_fields() ) : ?>
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
		<div class="row" id="customer_details">
			<div class="col-8">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<div class="emyui-additional-info">
					<h3><?php _e('Additional Information', 'emyui'); ?></h3>
					<div class="col-4">
						<?php 
							if(!empty(WC()->cart->get_cart())){
								foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
									$product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
									if($product_id){	
										$emyui_main = emyui_main::instance();
										echo $emyui_main->emyui_get_package_dropdown($product_id, 'shared_hosting');
										?>
										
										<?php
									}
								}
							}
						?>
					</div>
					<div class="col-4">
						<?php 
							$package_group  = get_post_meta($product_id, '_hosting_plan_meta', true);
		        			if(!empty($package_group)){
								$plan_meta = json_decode($package_group, true);
								$cookies_price 	= isset($_COOKIE['plan_price']) ? sanitize_text_field($_COOKIE['plan_price']) : '';
								if(!empty($cookies_price)){
									$cookies_offer 	= isset($_COOKIE['plan_offer']) ? sanitize_text_field($_COOKIE['plan_offer']) : '';
									$default_plan_price = wc_price($cookies_price);
									$default_plan_offer = $cookies_offer;
								}else{
									$default_plan = array_filter($plan_meta, function($plan) {
										return $plan['plan_default'] === 'yes';
									});
									$default_plan 	= array_values($default_plan);
									$default_plan_price = isset($default_plan[0]['plan_price']) ? wc_price(sanitize_text_field($default_plan[0]['plan_price'])) : '';
									$default_plan_offer = isset($default_plan[0]['plan_offer']) ? sanitize_text_field($default_plan[0]['plan_offer']) : '';
								}
								?>
								<div class="emyui-hosting-deal-wrap">
									<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span><span class="sr-only">Loading...</span>
									<select name="emyui_hosting_deal" class="emyui_hosting_deal">
										<?php
		    								foreach ($plan_meta as $key => $plan) {
		    									$plan_name = isset($plan['plan_name']) ? sanitize_text_field($plan['plan_name']) : '';
		    									$plan_price = isset($plan['plan_price']) ? sanitize_text_field($plan['plan_price']) : '';
		    									$plan_offer = isset($plan['plan_offer']) ? sanitize_text_field($plan['plan_offer']) : '';
		    									$plan_default = isset($plan['plan_default']) ? sanitize_text_field($plan['plan_default']) : '';
		    									$selected = '';
		    									if($plan_price == $cookies_price){
		    										$selected = 'selected';
		    									}
		    									if($plan_default == 'yes' && empty($cookies_price)){
		    										$selected = 'selected';
		    									}
		    									?>
												<option data-index_id="<?php echo $key; ?>" <?php echo $selected; ?> value="<?php echo $plan_price; ?>" data-plan_price='<?php echo wc_price($plan_price); ?>' data-plan-offer="<?php echo $plan_offer; ?>">
													<?php echo $plan_name; ?>		
												</option>
		    									<?php
		    								}
										?>
									</select>
								</div>
								<?php
							}
						?>
						<div id="selected-plan-price"><?php echo $default_plan_price; ?></div>
    					<div id="selected-plan-offer"><?php echo $default_plan_offer; ?></div>
					</div>
					<div class="col-4">
						<?php 
						$saved_fields = get_option('emyui_data_center', []);
						if(is_array($saved_fields) && !empty($saved_fields)){
							?>
							<div class="emyui-data-center-wrap">
								<select name="emyui_data_center_id" class="emyui-data-center_id">
									<?php 
									foreach ($saved_fields as $key => $data_center) {
										if(isset($_COOKIE['data_center']) && $_COOKIE['data_center'] == $data_center){
											$selected = 'selected';
										}else{
											$selected = '';
										}
										?>
											<option <?php echo $selected; ?> value="<?php echo $data_center; ?>"><?php echo $data_center; ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<div class="col-4">
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>
				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
			</div>
		</div>
		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
	<?php endif; ?>
</form>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
