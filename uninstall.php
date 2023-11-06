<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'vs_uvp_domain' );
delete_site_option( 'vs_uvp_domain' );

delete_option( 'vs_uvp_token' );
delete_site_option( 'vs_uvp_token' );
