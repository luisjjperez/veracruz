<?php
// emails are sent in plain text, trailing whitespace are required for proper formatting
printf( esc_html__( 'Hello %s,', 'another-wordpress-classifieds-plugin'), esc_html( $contact_name ) );
?>

<?php $message = __( 'Your Ad "%1$s" was recently approved by the admin. You should be able to see the Ad published here: %2$s.', 'another-wordpress-classifieds-plugin' ); ?>
<?php echo sprintf( $message, $listing_title, urldecode( url_showad( $listing->ID ) ) ); ?> 

<?php echo awpcp_get_blog_name() ?> 
<?php echo home_url() ?> 
