jQuery( document ).ready( function( $ ) {
	
	txtallnone = post2media_strings.txtallnone;
	txtall = post2media_strings.txtall;
	txtnone = post2media_strings.txtnone;
	ttlcb = post2media_strings.ttlcb;
	
	// Run only if we have images to display
	if ( 0 == $( '#media-items > *' ).length ) 
		return;

	var $include = '', $is_update = false, $is_checked;
	
	// Add Gallery include All or None, for easier selection
	$( '#sort-buttons' ).prepend( txtallnone+' <a id="gallery-include-all" href="#">'+txtall+'</a> | <a id="gallery-include-none" style="margin-right:2em;" href="#">'+txtnone+'</a>' );
	$( '#gallery-include-all' ).click( function() {
		$( '#media-items input[type=checkbox]' ).each( function() {
			$(this).attr( 'checked', 'checked' );
		});		
	});
	$( '#gallery-include-none' ).click( function() {
		$( '#media-items input[type=checkbox]' ).each( function() {
			$(this).removeAttr( 'checked' );
		});
	});
	
	// Select parent editor, read existing gallery data	
	w = wpgallery.getWin();
	editor = w.tinymce.EditorManager.activeEditor;
	gal = editor.selection.getNode();
	
	if ( editor.dom.hasClass( gal, 'wpGallery' ) ) {
		$include = editor.dom.getAttrib( gal, 'title' ).match( /include=['"]([^'"]+)['"]/i );
		$is_update = true;
		if ($include != null)
			$include = $include[1];
	} else {
		$( '#insert-gallery' ).show();
		$( '#update-gallery' ).hide();
	}
	
	// Check which images have been selected for inclusion
	$( '#media-items .media-item' ).each( function( $count ) {
		var $imgid = $( this ).attr( 'id' ).split( '-' )[2];
		
		if ( ( null != $include ) && ( -1 != $include.indexOf($imgid) ) )
			$is_checked = ' checked="checked" ';
		else
			$is_checked = '';
			
		$( '.menu_order', this).append( ' <label for="include-in-gallery-'+$imgid+'" class="include-in-gallery"><input type="checkbox" title="'+ttlcb+'" id="include-in-gallery-'+$imgid+'" '+$is_checked+' value="1" /></label>' );
	});
	
	$( '#insert-gallery' ).attr( 'onmousedown', '' );
	
	// Insert or update the actual shortcode
	$( '#update-gallery, #insert-gallery, #save-all' ).mousedown( function() {
		var $to_include = '';
		var orig_gallery = editor.dom.decode( editor.dom.getAttrib(gal, 'title' ) );
		
		// Check which images have been selected to be included
		$( '#media-items .media-item' ).each( function($count) {
			$imgid = $(this).attr( 'id' ).split( '-' )[2];
			
			if ($( '#include-in-gallery-'+$imgid+':checked', this).val() != null)
				$to_include += $imgid + ', ';
		});
		
		if ($to_include.length > 2) {
			$to_include = $to_include.substr(0, $to_include.length - 2); // remove the last comma
			$to_include = ' include="' + $to_include + '" ';
		}
		
		if ($(this).attr( 'id' ) == 'insert-gallery' ) {
			w.send_to_editor( '[gallery' + wpgallery.getSettings() + $to_include + ']' );
		}
		
		// Update existing shortcode
		if ($is_update) {
			if ( ( '' != $to_include ) && ( -1 == orig_gallery.indexOf( ' include=' ) ) )
				editor.dom.setAttrib( gal, 'title', orig_gallery + $to_include);
			else if (orig_gallery.indexOf( ' include=' ) != -1)
				editor.dom.setAttrib( gal, 'title', orig_gallery.replace(/include=['"]([^'"]+)['"]/i, $to_include) );
			else
				editor.dom.setAttrib( gal, 'title', orig_gallery.replace(/include=['"]([^'"]+)['"]/i, '' ) );
		}
	});

});
