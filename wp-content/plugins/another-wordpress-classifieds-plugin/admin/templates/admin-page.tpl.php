<?php awpcp_print_messages(); ?>

<div id="<?php echo esc_attr( $page_slug ); ?>" class="<?php echo esc_attr( $page_slug ); ?> awpcp-admin-page awpcp-page wrap">
    <?php if ( version_compare( get_bloginfo('version'), '4.4-beta4', '<' ) ): ?>
	<div class="page-content">
    <?php endif; ?>

        <?php
            if ( $should_show_title ) {
                $heading_params = array(
                    'attributes' => array(
                        'class' => 'awpcp-page-header',
                    ),
                    'content' => $page_title, // no need to escape; title() is allowed to output html
                );

                echo awpcp_html_admin_first_level_heading( $heading_params );
            }
        ?>

        <?php $sidebar = $show_sidebar ? awpcp_admin_sidebar() : ''; ?>
        <?php echo $sidebar; ?>

		<div class="awpcp-main-content <?php echo empty( $sidebar ) ? 'without-sidebar' : 'with-sidebar'; ?>">
            <div class="awpcp-inner-content">
            <?php echo $content; ?>
            </div>
        </div>

    <?php if ( version_compare( get_bloginfo('version'), '4.4-beta4', '<' ) ): ?>
    </div>
    <?php endif; ?>
</div>
