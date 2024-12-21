jQuery(function ($) {
	"use strict";

	$(".wpmake-advance-user-avatar-enhanced-select").select2();

	// Review notice.
	$(".wpmake-aua-notice").each(function () {
		$(this)
			.find(".notice-dismiss")
			.on("click", function (e) {
				e.preventDefault();

				$(this).closest("#wpmake-aua-review-notice").hide();

				var data = {
					action: "wpmake_advance_user_avatar_upload_dismiss_notice",
					security: wpmake_aua_admin_params.notice_nonce,
					dismissed: true
				};

				$.post(
					wpmake_aua_admin_params.ajax_url,
					data,
					function (response) {
						// Success. Do nothing. Silence is golden.
					}
				);
			});
	});
});
