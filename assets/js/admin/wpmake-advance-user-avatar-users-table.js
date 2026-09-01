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

		if ( isBusy ) {
			r.status.text( params().savingText ).removeClass( "is-error" );
		} else {
			clearStatus( userId );
		}
	}

	function fail( userId, message ) {
		$row( userId ).status.text( message || params().errorText ).addClass( "is-error" );
	}

	function clearStatus( userId ) {
		$row( userId ).status.text( "" ).removeClass( "is-error" );
	}

	function messageFrom( xhr ) {
		try {
			return JSON.parse( xhr.responseText ).data.message;
		} catch ( err ) {
			return "";
		}
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
						fail( userId, messageFrom( xhr ) );
					} );
			} );
		}

		frames[ userId ].open();
	} );

	/*
	 * An inline confirmation rather than window.confirm(). A browser that has decided
	 * to suppress repeated dialogs -- which is exactly what happens to an admin
	 * clearing several avatars in a row -- swallows the prompt and auto-dismisses it,
	 * so the row silently stops responding. This also matches the front-end
	 * uploader's confirmation, added in 1.3.0.
	 */
	$( document ).on( "click", ".wpmake-aua-row-remove", function ( e ) {
		e.preventDefault();

		var $btn    = $( this );
		var userId  = $btn.data( "user" );
		var $wrap   = $( '.wpmake-aua-row-actions[data-user="' + userId + '"]' );

		if ( $wrap.find( ".wpmake-aua-row-confirm" ).length ) {
			return;
		}

		clearStatus( userId );

		var $bar = $(
			'<span class="wpmake-aua-row-confirm">' +
				'<span class="wpmake-aua-row-confirm-text"></span>' +
				'<button type="button" class="button button-link-delete wpmake-aua-confirm-yes"></button>' +
				'<button type="button" class="button wpmake-aua-confirm-no"></button>' +
			"</span>"
		);

		$bar.find( ".wpmake-aua-row-confirm-text" ).text( params().confirmText );
		$bar.find( ".wpmake-aua-confirm-yes" ).text( params().confirmYes );
		$bar.find( ".wpmake-aua-confirm-no" ).text( params().confirmNo );

		$wrap.children().hide();
		$wrap.append( $bar );
		$bar.find( ".wpmake-aua-confirm-yes" ).trigger( "focus" );

		// Cancel just puts the row back. Remove was visible a moment ago -- that is
		// how we got here -- so showing the hidden children again is enough.
		$bar.find( ".wpmake-aua-confirm-no" ).one( "click", function () {
			$bar.remove();
			$wrap.children().show();
			$btn.trigger( "focus" );
		} );

		$bar.find( ".wpmake-aua-confirm-yes" ).one( "click", function () {
			$bar.remove();
			$wrap.children().show();

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
					fail( userId, messageFrom( xhr ) );
				} );
		} );
	} );
}( jQuery ) );
