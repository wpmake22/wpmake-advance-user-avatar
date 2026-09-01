/**
 * Profile screen avatar field.
 *
 * Only the media picker and the preview live here. The upload itself is a plain
 * file input posted with the rest of the profile form, so this file is not
 * required for the field to work -- without it the file input still saves.
 */
/* global wp, jQuery, wpmake_aua_user_profile_params */
( function ( $ ) {
	"use strict";

	var frame;

	function params() {
		return window.wpmake_aua_user_profile_params || {};
	}

	$( document ).on( "click", ".wpmake-aua-choose-media", function ( e ) {
		e.preventDefault();

		if ( ! window.wp || ! wp.media ) {
			return;
		}

		if ( frame ) {
			frame.open();
			return;
		}

		frame = wp.media( {
			title:    params().chooseTitle,
			button:   { text: params().chooseButton },
			library:  { type: "image" },
			multiple: false
		} );

		frame.on( "select", function () {
			var attachment = frame.state().get( "selection" ).first().toJSON();
			var url        = attachment.url;

			// Prefer a generated size over the full original for the preview.
			if ( attachment.sizes ) {
				url = ( attachment.sizes.thumbnail || attachment.sizes.medium || attachment.sizes.full || attachment ).url;
			}

			$( "#wpmake-aua-avatar-preview" ).attr( "src", url );
			$( "#wpmake_aua_avatar_id" ).val( attachment.id );

			// Picking one cancels a pending removal, and supersedes a chosen file.
			$( "#wpmake_aua_avatar_remove" ).val( "" );
			$( "#wpmake_aua_avatar_file" ).val( "" );
			$( ".wpmake-aua-remove-avatar" ).show();
		} );

		frame.open();
	} );

	$( document ).on( "click", ".wpmake-aua-remove-avatar", function ( e ) {
		e.preventDefault();

		var $preview = $( "#wpmake-aua-avatar-preview" );

		$( "#wpmake_aua_avatar_remove" ).val( "1" );
		$( "#wpmake_aua_avatar_id" ).val( "" );
		$( "#wpmake_aua_avatar_file" ).val( "" );
		$preview.attr( "src", $preview.data( "default" ) );
		$( this ).hide();
	} );

	// Choosing a file supersedes a pending removal or a library pick.
	$( document ).on( "change", "#wpmake_aua_avatar_file", function () {
		if ( ! this.files || ! this.files.length ) {
			return;
		}

		$( "#wpmake_aua_avatar_remove" ).val( "" );
		$( "#wpmake_aua_avatar_id" ).val( "" );
		$( ".wpmake-aua-remove-avatar" ).show();
	} );
}( jQuery ) );
