<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || exit;
$emyui_main = emyui_main::instance();
$emyui_main->emyui_display_woocommerce_notice_transient('woocommerce_notice_cart');
do_action( 'woocommerce_before_cart' ); ?>
<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
				<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
				<th class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				/**
				 * Filter the product name.
				 *
				 * @since 2.1.0
				 * @param string $product_name Name of the product in the cart.
				 * @param array $cart_item The product in the cart.
				 * @param string $cart_item_key Key for the product in the cart.
				 */
				$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						<td class="product-remove">
							<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s is the product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() )
									),
									$cart_item_key
								);
							?>
						</td>
						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( $product_name . '&nbsp;' );
						} else {
							/**
							 * This filter is documented above.
							 *
							 * @since 2.1.0
							 */
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}
						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
						}
						?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>
						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
							?>
						</td>
					</tr>
					<tr>
						<td><h3><?php _e('Additional information', 'emyui'); ?></h3></td>
					</tr>
					<tr>
						<td>
							<?php 
								$emyui_main = emyui_main::instance();
								echo $emyui_main->emyui_get_package_dropdown($product_id, 'shared_hosting');
							?>
						</td>
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
								<td>
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
								</td>
								<?php
							}
						?>
						<td id="selected-plan-price"><?php echo $default_plan_price; ?></td>
    					<td id="selected-plan-offer"><?php echo $default_plan_offer; ?></td>
					</tr>
					<?php 
					$saved_fields = get_option('emyui_data_center', []);
					if(is_array($saved_fields) && !empty($saved_fields)){
						?>
						<tr>
							<td>
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
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
			<?php do_action( 'woocommerce_cart_contents' ); ?>
			<tr>
				<td colspan="6" class="actions">
					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</td>
			</tr>
			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>
<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
<div class="cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>
<?php do_action( 'woocommerce_after_cart' ); ?>
