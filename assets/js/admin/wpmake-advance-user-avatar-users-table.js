/**
 * Bulk avatar manager row actions.
 *
 * Change and Remove both act on a single row and repaint only that row -- no page
 * reload, and no re-fetch of the table.
 */
/* global wp, jQuery */
( function ( $ ) {
	"use strict";

	var frames = {};

	function params() {
		return window.wpmake_aua_users_table_params || {};
	}

	function $row( userId ) {
		return {
			avatar: $( '.wpmake-aua-row-avatar[data-user="' + userId + '"]' ),
			remove: $( '.wpmake-aua-row-remove[data-user="' + userId + '"]' ),
			change: $( '.wpmake-aua-row-change[data-user="' + userId + '"]' ),
			status: $( '.wpmake-aua-row-status[data-user="' + userId + '"]' )
		};
	}

	function busy( userId, isBusy ) {
		var r = $row( userId );

		r.change.prop( "disabled", isBusy );
		r.remove.prop( "disabled", isBusy );
		r.status.text( isBusy ? params().savingText : "" );
	}

	function fail( userId, message ) {
		$row( userId ).status.text( message || params().errorText );
	}

	function post( action, data ) {
		return $.ajax( {
			url:  params().ajaxUrl,
			type: "POST",
			data: $.extend( { action: "wpmake_advance_user_avatar_upload_" + action }, data )
		} );
	}

	$( document ).on( "click", ".wpmake-aua-row-change", function ( e ) {
		e.preventDefault();

		if ( ! window.wp || ! wp.media ) {
			return;
		}

		var userId = $( this ).data( "user" );

		if ( ! frames[ userId ] ) {
			frames[ userId ] = wp.media( {
				title:    params().chooseTitle,
				button:   { text: params().chooseButton },
				library:  { type: "image" },
				multiple: false
			} );

			frames[ userId ].on( "open", function () {
				var remembered = window.getUserSetting ? window.getUserSetting( "libraryContent" ) : "";

				frames[ userId ].content.mode( "browse" );

				// mode() writes that remembered tab as a side effect; put it back.
				if ( remembered && window.setUserSetting ) {
					window.setUserSetting( "libraryContent", remembered );
				}
			} );

			frames[ userId ].on( "select", function () {
				var attachment = frames[ userId ].state().get( "selection" ).first().toJSON();

				busy( userId, true );

				post( "set_avatar", { security: params().setNonce, user_id: userId, attachment_id: attachment.id } )
					.done( function ( res ) {
						if ( res && res.success && res.data && res.data.profile_picture_url ) {
							$row( userId ).avatar.attr( "src", res.data.profile_picture_url );
							$row( userId ).remove.show();
							busy( userId, false );
						} else {
							busy( userId, false );
							fail( userId, res && res.data ? res.data.message : "" );
						}
					} )
					.fail( function ( xhr ) {
						busy( userId, false );

						var message = "";

						try {
							message = JSON.parse( xhr.responseText ).data.message;
						} catch ( err ) {
							message = "";
						}

						fail( userId, message );
					} );
			} );
		}

		frames[ userId ].open();
	} );

	$( document ).on( "click", ".wpmake-aua-row-remove", function ( e ) {
		e.preventDefault();

		var userId = $( this ).data( "user" );

		if ( ! window.confirm( params().confirmText ) ) {
			return;
		}

		busy( userId, true );

		post( "remove_avatar", { security: params().removeNonce, user_id: userId } )
			.done( function ( res ) {
				busy( userId, false );

				if ( res && res.success ) {
					var r = $row( userId );

					r.avatar.attr( "src", res.data && res.data.fallback_url ? res.data.fallback_url : r.avatar.data( "default" ) );
					r.remove.hide();
				} else {
					fail( userId, res && res.data ? res.data.message : "" );
				}
			} )
			.fail( function ( xhr ) {
				busy( userId, false );

				var message = "";

				try {
					message = JSON.parse( xhr.responseText ).data.message;
				} catch ( err ) {
					message = "";
				}

				fail( userId, message );
			} );
	} );
}( jQuery ) );
