<?php
/**
 * Loop Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
    return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count >= 0 ) : ?>

	        <?php echo wc_get_rating_html($average, $rating_count); ?>
		<?php if ( comments_open() ): ?><a href="<?php echo get_permalink() ?>#reviews" class="woocommerce-review-link" rel="nofollow"><?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?> Reviews</a><?php endif ?>
	

<?php endif; ?>