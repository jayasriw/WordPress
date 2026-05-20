<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$company_id       = get_the_ID();
$company_location = get_the_terms( $company_id, 'company-location' );
$company_logo     = get_post_meta( $company_id, CIVI_METABOX_PREFIX . 'company_logo' );
$company_website  = get_post_meta( $company_id, CIVI_METABOX_PREFIX . 'company_website', true );
$check_package    = civi_get_field_check_candidate_package( 'info_company' );
$enable_single_company_review = civi_get_option('enable_single_company_review', '1');
?>
<div class="block-archive-inner company-head-details">
	<div class="civi-company-header-top">
		<?php if ( ! empty( $company_logo[0]['url'] ) ) : ?>
			<div class="logo-company">
				<img src="<?php echo $company_logo[0]['url'] ?>" alt=""/>
			</div>
		<?php endif; ?>
		<div class="info">
			<div class="title-wapper">
				<?php if ( ! empty( get_the_title() ) ) : ?>
					<h1><?php echo get_the_title(); ?></h1>
					<?php civi_company_green_tick( $company_id ); ?>
				<?php endif; ?>
			</div>
			<div class="company-info">
				<?php if ( is_array( $company_location ) ) { ?>
					<div class="company-wrapper">
						<i class="fas fa-map-marker-alt"></i>
						<?php foreach ( $company_location as $location ) {
							$cate_link = get_term_link( $location, 'company-location' ); ?>
							<div class="cate-wrapper">
								<a href="<?php echo esc_url( $cate_link ); ?>" class="cate civi-link-bottom">
									<?php echo $location->name; ?>
								</a>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
				<?php if( $enable_single_company_review ) { echo civi_get_total_rating( 'company', $company_id ); } ?>
			</div>
		</div>
	</div>
	<div class="civi-company-header-bottom">
		<?php civi_get_template( 'company/follow.php', array(
				'company_id' => $company_id,
		) ); ?>
		<?php if ( $check_package == - 1 || $check_package == 0 ) { ?>
			<a href="#" class="civi-button button-outline btn-add-to-message button-icon-right"
			   data-text="<?php echo esc_attr( 'Please renew the package to see website', 'civi-framework' ); ?>">
				<?php esc_html_e( 'Visit website', 'civi-framework' ) ?><i class="far fa-external-link-alt"></i>
			</a>
		<?php } else {
			if ( ! empty( $company_website ) ) {
				?>
				<a href="<?php echo $company_website; ?>" class="civi-button button-outline btn-webs button-icon-right"
				   target="_blank">
					<?php esc_html_e( 'Visit website', 'civi-framework' ) ?><i class="far fa-external-link-alt"></i>
				</a>
				<?php
			} ?>

		<?php } ?>
		<?php civi_get_template( 'company/messages.php', array(
				'company_id' => $company_id,
		) ); ?>
	</div>
</div>
