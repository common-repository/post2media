<?php
/**
 * Plugin Name: Post2Media
 * Plugin URI: http://inpsyde.com
 * Text Domain: post2media
 * Domain Path: /languages
 * Description: This adds a new button on the media page, which allows to link a media to a post, without inserting it into the content.
 * Version: 0.2
 * Author: Inpsyde GmbH
 * Author URI: http://inpsyde.com
 * License: GPL
*/

/**
License:
==============================================================================
Copyright 2010 Robert Windisch, Frank Bültge  (email : info@inpsyde.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Requirements:
==============================================================================
This plugin requires WordPress >= 2.8 and tested with PHP Interpreter >= 5.2.9
*/

//avoid direct calls to this file where wp core files not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

if ( !class_exists( 'post2media' ) ) {
	
	define( 'INPSYDE_P2M_BASEDIR', dirname( plugin_basename(__FILE__) ) );
	define( 'INPSYDE_P2M_TEXTDOMAIN', 'post2media' );
	
	class post2media {
	
		function __construct() {
			
			add_action( 'init', array(&$this, 'text_domain') );
			add_action( 'media_upload_library', array( &$this, 'do_media_upload_library' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'add_scripts') );
		}
		
		function text_domain() {
			
			load_plugin_textdomain( INPSYDE_P2M_TEXTDOMAIN, false, INPSYDE_P2M_BASEDIR . '/languages' );
		}
		
		function add_scripts($where) {
			
			if ( 'media-upload-popup' != $where )
				return;
				
			wp_enqueue_script(
				'post2media', 
				WP_CONTENT_URL . '/plugins/' . INPSYDE_P2M_BASEDIR . '/js/script.js', 
				array( 'jquery', 'media-upload')
			);
			wp_enqueue_script(
				'media-selector', 
				WP_CONTENT_URL . '/plugins/' . INPSYDE_P2M_BASEDIR . '/js/selector.js', 
				array( 'jquery', 'media-upload', 'utils' ),
				1.0,
				true
			);
			wp_localize_script( 'post2media', 'post2media_strings', $this->localize_vars() );
		}
		
		function do_media_upload_library() {
			
			if ( !isset($_POST['post_id'] ) )
				return;
				
			if ( 0 < $_POST['post_id'] && isset( $_POST['link'] ) ) {
			
				$linkid = (int) array_shift ( array_keys( $_POST['link'] ) );
			
				if ( 0 < $linkid ) {  
					$post = (array) get_post ( $linkid );
					
					if ( 0 < $post[ 'post_parent' ] ) { // check if the image is already linked to a post
						$file = get_post_meta( $post[ 'ID' ], '_wp_attached_file', true );
						if ( '' != $file ) { // copy file and link to the post
							$fileinfo = pathinfo( $file );
							if ( 0 < count ( $fileinfo ) ) {
								$filedir  = wp_upload_dir( $fileinfo[ 'dirname' ] );
								$filename = wp_unique_filename( $filedir[ 'path' ], $fileinfo[ 'basename' ] );
								$copy     = copy( $filedir[ 'path' ] . '/' . $fileinfo[ 'basename' ] , $filedir[ 'path' ] . '/' . $filename );
								if ( $copy ) {
									unset ( $post[ 'ID' ] ); // unset the postID to prevent the override of the original image
									$attachment	= array_merge( $post, array(
										'guid' => $filedir['url'] . '/' . $filename,
										'post_parent' => $_POST[ 'post_id' ],
										)
									);
									$attach_id = wp_insert_attachment( $attachment, $filedir['path'] . '/' . $filename ); //insert the image
									if ( !is_wp_error( $attach_id ) ) {
										wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filedir['path'] . '/' . $filename ) );
										add_post_meta( $attach_id, '_inpsyde_copy_of' , $post[ 'ID' ], true);
									}// update the image data
								}	
							}
						}
					} else {
						$post['post_parent'] = $_POST[ 'post_id' ];
						wp_update_post( $post );
					}
				}
			}
		}
		
		function localize_vars() {
			
			$strings = array(
					'btntext'    => __( 'Link with post', INPSYDE_P2M_TEXTDOMAIN ),
					'txtallnone' => __( 'Include in gallery:', INPSYDE_P2M_TEXTDOMAIN ),
					'txtall'     => __( 'All', INPSYDE_P2M_TEXTDOMAIN ),
					'txtnone'    => __( 'None', INPSYDE_P2M_TEXTDOMAIN ),
					'ttlcb'      => __( 'Include image in this gallery', INPSYDE_P2M_TEXTDOMAIN )
				);
			
			return $strings;
		}
		
	}
	
	function post2media_start() {

		new post2media();
	}
	
	add_action( 'plugins_loaded', 'post2media_start' );
}
?>
