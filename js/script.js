jQuery(function ($) {
	buttonaddfunc = function() {
		btntext = post2media_strings.btntext;
		
		reg = /\d+/;
		$( '.savesend > .button' ) . each( function() {
			inputname = $( this ) . attr( 'name' );
			number = reg . exec( inputname );
			$( this ) . after( '<input type="submit" value="' + btntext + '" name="link[' + number + ']" class="button">' );
		} );
		$( '.describe-toggle-on' ).unbind( 'click', buttonaddfunc );
	};
	$( '.describe-toggle-on' ).bind( 'click', buttonaddfunc );
} );
