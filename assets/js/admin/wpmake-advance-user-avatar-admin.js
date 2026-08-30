jQuery(function ($) {
	"use strict";

	$(".wpmake-advance-user-avatar-enhanced-select").select2();

	// The review notice's dismiss handlers are printed with the notice itself, in
	// Admin::print_review_notice_script(). They are dependency-free because the
	// notice shows on every admin screen and this bundle does not.

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
