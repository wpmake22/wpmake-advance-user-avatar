jQuery(function ($) {
	"use strict";

	$(".wpmake-advance-user-avatar-enhanced-select").select2();

	// Review notice.
	var $notice = $("#wpmake-aua-review-notice");

	if ($notice.length) {

		function sendDismiss(type, callback) {
			$.post(
				wpmake_aua_admin_params.ajax_url,
				{
					action:    "wpmake_advance_user_avatar_upload_dismiss_notice",
					security:  wpmake_aua_admin_params.notice_nonce,
					dismissed: true,
					type:      type
				},
				function () {
					if (typeof callback === "function") {
						callback();
					}
				}
			);
		}

		// "Sure, I'd love to!" — opens WP.org review page and permanently dismisses the notice.
		$notice.find(".notice-link-visit").on("click", function () {
			sendDismiss("rated");
			$notice.slideUp();
			// href opens the review page in a new tab; default is not prevented.
		});

		// "Maybe Later" — snoozes for 14 days.
		$notice.find(".notice-later").on("click", function (e) {
			e.preventDefault();
			sendDismiss("later");
			$notice.slideUp();
		});

		// "I already did!" — permanently dismisses the notice.
		$notice.find(".notice-dismiss-permanently").on("click", function (e) {
			e.preventDefault();
			sendDismiss("rated");
			$notice.slideUp();
		});
	}

	// Shortcode copy-to-clipboard.
	$(".wpmake-aua-shortcode-copy").on("click", function () {
		var $btn  = $(this);
		var code  = $btn.data("code");
		var $icon = $btn.find(".wpmake-aua-copy-icon");

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(code);
		} else {
			var $tmp = $("<textarea>").val(code).appendTo("body").select();
			document.execCommand("copy");
			$tmp.remove();
		}

		$icon.removeClass("dashicons-clipboard").addClass("dashicons-yes");
		$btn.addClass("wpmake-aua-copied");

		setTimeout(function () {
			$icon.removeClass("dashicons-yes").addClass("dashicons-clipboard");
			$btn.removeClass("wpmake-aua-copied");
		}, 2000);
	});
});
