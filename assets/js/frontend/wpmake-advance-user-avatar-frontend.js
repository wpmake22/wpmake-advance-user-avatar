jQuery(function ($) {
	"use strict";

	/* =========================================================================
	   WPMakeModal — zero-dependency overlay modal (replaces SweetAlert2)
	   ========================================================================= */
	var WPMakeModal = (function () {
		var _el = null;

		function open(opts) {
			close();

			var overlay = document.createElement("div");
			overlay.className = "wpmake-modal-overlay";

			var box = document.createElement("div");
			box.className = "wpmake-modal" + (opts.className ? " " + opts.className : "");

			// Header
			if (opts.title || opts.showClose !== false) {
				var hdr = document.createElement("div");
				hdr.className = "wpmake-modal-header";

				if (opts.title) {
					var ttl = document.createElement("h3");
					ttl.className = "wpmake-modal-title";
					ttl.textContent = opts.title;
					hdr.appendChild(ttl);
				}

				if (opts.showClose !== false) {
					var xBtn = document.createElement("button");
					xBtn.type = "button";
					xBtn.className = "wpmake-modal-close";
					xBtn.innerHTML = "&times;";
					xBtn.onclick = close;
					hdr.appendChild(xBtn);
				}
				box.appendChild(hdr);
			}

			// Body
			var body = document.createElement("div");
			body.className = "wpmake-modal-body";
			if (typeof opts.html === "string") {
				body.innerHTML = opts.html;
			} else if (opts.html) {
				body.appendChild(opts.html);
			}
			box.appendChild(body);

			// Footer buttons
			if (opts.buttons && opts.buttons.length) {
				var ftr = document.createElement("div");
				ftr.className = "wpmake-modal-footer";
				opts.buttons.forEach(function (b) {
					var btn = document.createElement("button");
					btn.type = "button";
					btn.className = "wpmake-btn " + (b.className || "");
					btn.textContent = b.text;
					btn.onclick = function () {
						if (b.closes !== false) { close(); }
						if (b.onClick) { b.onClick(); }
					};
					ftr.appendChild(btn);
				});
				box.appendChild(ftr);
			}

			overlay.appendChild(box);
			document.body.appendChild(overlay);
			_el = overlay;

			if (opts.onOpen) { opts.onOpen(box); }
		}

		function close() {
			if (_el && _el.parentNode) {
				_el.parentNode.removeChild(_el);
			}
			_el = null;
		}

		return { open: open, close: close };
	}());

	/* =========================================================================
	   WPMakeCropper — canvas-based image cropper with live preview
	   ========================================================================= */
	var WPMakeCropper = (function () {
		var S  = {}; // state
		var D  = {}; // DOM refs
		var CB = {}; // callbacks

		function open(src, onSave, onRetake, retakeLabel) {
			CB.onSave       = onSave;
			CB.onRetake     = onRetake;
			CB.retakeLabel  = retakeLabel;

			var img = new Image();
			img.onload = function () {
				S.img      = img;
				S.naturalW = img.naturalWidth;
				S.naturalH = img.naturalHeight;
				_build(src);
			};
			img.src = src;
		}

		function _build(src) {
			var p = wpmake_advance_user_avatar_params;

			/* ---- Left column: stage ---- */
			var leftCol = document.createElement("div");
			leftCol.className = "wpmake-cropper-left";

			var stage = document.createElement("div");
			stage.className = "wpmake-crop-stage";
			D.stage = stage;

			var cropImg = document.createElement("img");
			cropImg.className  = "wpmake-crop-image";
			cropImg.src        = src;
			cropImg.draggable  = false;
			D.img = cropImg;
			stage.appendChild(cropImg);

			var cropBox = document.createElement("div");
			cropBox.className = "wpmake-crop-box";
			D.box = cropBox;

			["nw", "n", "ne", "e", "se", "s", "sw", "w"].forEach(function (dir) {
				var h = document.createElement("span");
				h.className = "wpmake-handle wpmake-handle-" + dir;
				h.setAttribute("data-dir", dir);
				cropBox.appendChild(h);
			});
			stage.appendChild(cropBox);
			leftCol.appendChild(stage);

			// Ratio label
			var ratioLbl = document.createElement("p");
			ratioLbl.className   = "wpmake-crop-ratio-label";
			ratioLbl.textContent = p.wpmake_advance_user_avatar_crop_ratio_label || "Square crop (1:1)";
			leftCol.appendChild(ratioLbl);

			// Hint
			var hint = document.createElement("p");
			hint.className   = "wpmake-crop-hint";
			hint.textContent = "Drag to reposition \u00b7 Handles to resize";
			leftCol.appendChild(hint);

			// Zoom bar
			var zoomBar = document.createElement("div");
			zoomBar.className = "wpmake-zoom-bar";

			var zoomLbl = document.createElement("span");
			zoomLbl.textContent = "Zoom";

			var slider = document.createElement("input");
			slider.type      = "range";
			slider.className = "wpmake-zoom-slider";
			slider.step      = "0.01";
			D.slider = slider;

			var zoomVal = document.createElement("span");
			zoomVal.className = "wpmake-zoom-value";
			D.zoomVal = zoomVal;

			zoomBar.appendChild(zoomLbl);
			zoomBar.appendChild(slider);
			zoomBar.appendChild(zoomVal);
			leftCol.appendChild(zoomBar);

			/* ---- Right column: sidebar ---- */
			var sidebar = document.createElement("div");
			sidebar.className = "wpmake-cropper-sidebar";

			var previewLbl = document.createElement("p");
			previewLbl.className   = "wpmake-preview-label";
			previewLbl.textContent = "Preview at display sizes";
			sidebar.appendChild(previewLbl);

			var previewRow = document.createElement("div");
			previewRow.className = "wpmake-preview-sizes";
			D.previews = [];

			[
				{ px: 64, label: "Profile" },
				{ px: 40, label: "Review" },
				{ px: 24, label: "Comment" }
			].forEach(function (s) {
				var item = document.createElement("div");
				item.className = "wpmake-preview-item";

				var cv = document.createElement("canvas");
				cv.width  = s.px;
				cv.height = s.px;
				cv.className  = "wpmake-preview-circle";
				cv.style.cssText = "width:" + s.px + "px;height:" + s.px + "px;";
				D.previews.push(cv);

				var lbl = document.createElement("p");
				lbl.innerHTML = s.label + "<br>" + s.px + "px";
				item.appendChild(cv);
				item.appendChild(lbl);
				previewRow.appendChild(item);
			});
			sidebar.appendChild(previewRow);

			// Action buttons
			var actions = document.createElement("div");
			actions.className = "wpmake-cropper-actions";

			var retakeBtn = document.createElement("button");
			retakeBtn.type      = "button";
			retakeBtn.className = "wpmake-btn wpmake-btn-secondary";
			retakeBtn.textContent = CB.retakeLabel || p.wpmake_advance_user_avatar_retake || "Retake";
			retakeBtn.onclick = function () {
				WPMakeModal.close();
				if (CB.onRetake) { CB.onRetake(); }
			};

			var saveBtn = document.createElement("button");
			saveBtn.type      = "button";
			saveBtn.className = "wpmake-btn wpmake-btn-primary";
			saveBtn.textContent = p.wpmake_advance_user_avatar_save_avatar || "Save avatar";
			saveBtn.onclick = function () {
				var dataURL = _getCropped();
				WPMakeModal.close();
				if (CB.onSave) { CB.onSave(dataURL); }
			};

			var cancelBtn = document.createElement("button");
			cancelBtn.type      = "button";
			cancelBtn.className = "wpmake-btn wpmake-btn-cancel";
			cancelBtn.textContent = p.wpmake_advance_user_avatar_cancel_button || "Cancel";
			cancelBtn.onclick = function () {
				WPMakeModal.close();
			};

			actions.appendChild(retakeBtn);
			actions.appendChild(saveBtn);
			actions.appendChild(cancelBtn);
			sidebar.appendChild(actions);

			// Assemble wrapper
			var wrap = document.createElement("div");
			wrap.className = "wpmake-cropper-wrap";
			wrap.appendChild(leftCol);
			wrap.appendChild(sidebar);

			WPMakeModal.open({
				html: wrap,
				showClose: false,
				className: "wpmake-cropper-modal",
				onOpen: function () {
					_initState();
					_draw();
					_bindEvents();
					_updatePreviews();
				}
			});
		}

		function _initState() {
			var stageW = D.stage.offsetWidth;
			var stageH = D.stage.offsetHeight;

			// Zoom to cover stage completely
			var z = Math.max(stageW / S.naturalW, stageH / S.naturalH);
			S.zoom   = z;
			S.dispW  = S.naturalW * z;
			S.dispH  = S.naturalH * z;

			D.slider.min   = z.toFixed(2);
			D.slider.max   = (z * 3).toFixed(2);
			D.slider.value = z.toFixed(2);

			// Center image in stage
			S.imgX = (stageW - S.dispW) / 2;
			S.imgY = (stageH - S.dispH) / 2;

			// Crop box: 80% of smaller stage dimension, centered, 1:1
			var sz   = Math.round(Math.min(stageW, stageH) * 0.8);
			S.cropW  = sz;
			S.cropH  = sz;
			S.cropX  = Math.round((stageW - sz) / 2);
			S.cropY  = Math.round((stageH - sz) / 2);
		}

		function _draw() {
			D.img.style.left   = S.imgX  + "px";
			D.img.style.top    = S.imgY  + "px";
			D.img.style.width  = S.dispW + "px";
			D.img.style.height = S.dispH + "px";

			D.box.style.left   = S.cropX + "px";
			D.box.style.top    = S.cropY + "px";
			D.box.style.width  = S.cropW + "px";
			D.box.style.height = S.cropH + "px";

			D.zoomVal.textContent = S.zoom.toFixed(1) + "x";
		}

		function _updatePreviews() {
			var srcX = (S.cropX - S.imgX) / S.zoom;
			var srcY = (S.cropY - S.imgY) / S.zoom;
			var srcW = S.cropW / S.zoom;
			var srcH = S.cropH / S.zoom;

			D.previews.forEach(function (cv) {
				var px  = cv.width;
				var ctx = cv.getContext("2d");
				ctx.clearRect(0, 0, px, px);
				ctx.save();
				ctx.beginPath();
				ctx.arc(px / 2, px / 2, px / 2, 0, Math.PI * 2);
				ctx.clip();
				if (srcW > 0 && srcH > 0) {
					try {
						ctx.drawImage(S.img, srcX, srcY, srcW, srcH, 0, 0, px, px);
					} catch (e) {}
				}
				ctx.restore();
			});
		}

		function _getCropped(size) {
			size = size || 500;
			var srcX = (S.cropX - S.imgX) / S.zoom;
			var srcY = (S.cropY - S.imgY) / S.zoom;
			var srcW = S.cropW / S.zoom;
			var srcH = S.cropH / S.zoom;
			var cv   = document.createElement("canvas");
			cv.width = cv.height = size;
			cv.getContext("2d").drawImage(S.img, srcX, srcY, srcW, srcH, 0, 0, size, size);
			return cv.toDataURL("image/jpeg", 0.92);
		}

		// Keep image large enough so the crop box never exposes the dark stage
		function _constrainImg() {
			if (S.imgX > S.cropX)                        { S.imgX = S.cropX; }
			if (S.imgX + S.dispW < S.cropX + S.cropW)   { S.imgX = S.cropX + S.cropW - S.dispW; }
			if (S.imgY > S.cropY)                        { S.imgY = S.cropY; }
			if (S.imgY + S.dispH < S.cropY + S.cropH)   { S.imgY = S.cropY + S.cropH - S.dispH; }
		}

		function _bindEvents() {
			var drag  = null;
			var MIN   = 50;

			function pt(e) {
				var src = e.touches ? e.touches[0] : e;
				return { x: src.clientX, y: src.clientY };
			}

			D.stage.addEventListener("mousedown",  down);
			D.stage.addEventListener("touchstart", down, { passive: false });

			function down(e) {
				e.preventDefault();
				var p   = pt(e);
				var dir = e.target.getAttribute && e.target.getAttribute("data-dir");

				if (dir) {
					drag = {
						type: "handle", dir: dir,
						ox: p.x, oy: p.y,
						s0: { x: S.cropX, y: S.cropY, w: S.cropW, h: S.cropH }
					};
				} else {
					drag = { type: "img", ox: p.x, oy: p.y, ix0: S.imgX, iy0: S.imgY };
				}

				window.addEventListener("mousemove", move);
				window.addEventListener("touchmove", move, { passive: false });
				window.addEventListener("mouseup",   up);
				window.addEventListener("touchend",  up);
			}

			function move(e) {
				if (!drag) { return; }
				e.preventDefault();
				var p       = pt(e);
				var dx      = p.x - drag.ox;
				var dy      = p.y - drag.oy;
				var stageW  = D.stage.offsetWidth;
				var stageH  = D.stage.offsetHeight;

				if (drag.type === "img") {
					S.imgX = drag.ix0 + dx;
					S.imgY = drag.iy0 + dy;
					_constrainImg();
				} else {
					var d  = drag.dir;
					var s0 = drag.s0;
					var sz, nx, ny;

					// Compute square size from drag direction
					if (d === "se") {
						sz = Math.max(MIN, Math.min(s0.w + Math.max(dx, dy), stageW - s0.x, stageH - s0.y));
						nx = s0.x;
						ny = s0.y;
					} else if (d === "sw") {
						sz = Math.max(MIN, Math.min(s0.w + Math.max(-dx, dy), s0.x + s0.w, stageH - s0.y));
						nx = s0.x + s0.w - sz;
						ny = s0.y;
					} else if (d === "ne") {
						sz = Math.max(MIN, Math.min(s0.w + Math.max(dx, -dy), stageW - s0.x, s0.y + s0.h));
						nx = s0.x;
						ny = s0.y + s0.h - sz;
					} else if (d === "nw") {
						sz = Math.max(MIN, Math.min(s0.w + Math.max(-dx, -dy), s0.x + s0.w, s0.y + s0.h));
						nx = s0.x + s0.w - sz;
						ny = s0.y + s0.h - sz;
					} else if (d === "e") {
						sz = Math.max(MIN, Math.min(s0.w + dx, stageW - s0.x, stageH - s0.y));
						nx = s0.x;
						ny = s0.y + (s0.h - sz) / 2;
					} else if (d === "w") {
						sz = Math.max(MIN, Math.min(s0.w - dx, s0.x + s0.w, stageH - s0.y));
						nx = s0.x + s0.w - sz;
						ny = s0.y + (s0.h - sz) / 2;
					} else if (d === "s") {
						sz = Math.max(MIN, Math.min(s0.h + dy, stageH - s0.y, stageW - s0.x));
						nx = s0.x + (s0.w - sz) / 2;
						ny = s0.y;
					} else { // n
						sz = Math.max(MIN, Math.min(s0.h - dy, s0.y + s0.h, stageW - s0.x));
						nx = s0.x + (s0.w - sz) / 2;
						ny = s0.y + s0.h - sz;
					}

					// Clamp box to stage bounds
					nx = Math.max(0, nx);
					ny = Math.max(0, ny);
					if (nx + sz > stageW) { sz = stageW - nx; }
					if (ny + sz > stageH) { sz = stageH - ny; }
					sz = Math.max(MIN, sz);

					S.cropX = nx;
					S.cropY = ny;
					S.cropW = sz;
					S.cropH = sz;
					_constrainImg();
				}

				_draw();
				_updatePreviews();
			}

			function up() {
				drag = null;
				window.removeEventListener("mousemove", move);
				window.removeEventListener("touchmove", move);
				window.removeEventListener("mouseup",   up);
				window.removeEventListener("touchend",  up);
			}

			// Zoom slider
			D.slider.addEventListener("input", function () {
				var newZ = parseFloat(this.value);
				// Zoom around the crop-box centre
				var cx    = S.cropX + S.cropW / 2;
				var cy    = S.cropY + S.cropH / 2;
				var imgCx = (cx - S.imgX) / S.zoom;
				var imgCy = (cy - S.imgY) / S.zoom;

				S.zoom  = newZ;
				S.dispW = S.naturalW * newZ;
				S.dispH = S.naturalH * newZ;
				S.imgX  = cx - imgCx * newZ;
				S.imgY  = cy - imgCy * newZ;
				_constrainImg();
				_draw();
				_updatePreviews();
			});
		}

		return { open: open };
	}());

	/* =========================================================================
	   Upload + send helpers
	   ========================================================================= */
	var WPMake_Advance_User_Avatar_Frontend = {

		init: function () {
			this.process_avatar_upload();
		},

		process_avatar_upload: function () {
			$("body").on(
				"change",
				'.wpmake-advance-user-avatar-upload-node input[type="file"]',
				function () {
					var file   = this.files && this.files[0];
					var $input = $(this);
					if (!file) { return; }

					var reader    = new FileReader();
					reader.onload = function (e) {
						var src = e.target.result;

						if (wpmake_advance_user_avatar_params.wpmake_advance_user_avatar_enable_cropping_interface) {
							// Clear now — upload uses canvas blob, not the file reference.
							// Ensures change event fires again if user cancels and re-selects.
							$input.val("");
							WPMakeCropper.open(
								src,
								function (croppedDataURL) {  // onSave
									var blob = WPMake_Advance_User_Avatar_Frontend.dataURItoBlob(croppedDataURL);
									WPMake_Advance_User_Avatar_Frontend.send_file($input, blob);
								},
								function () {                // onRetake — re-open file picker
									$input.val("").trigger("click");
								},
								wpmake_advance_user_avatar_params.wpmake_advance_user_avatar_reupload || "Re-upload"
							);
						} else {
							WPMake_Advance_User_Avatar_Frontend.send_file($input, null);
						}
					};
					reader.readAsDataURL(file);
				}
			);
		},

		remove_avatar: function ($node) {
			var url = wpmake_advance_user_avatar_params.ajax_url +
				"?action=wpmake_advance_user_avatar_upload_remove_avatar&security=" +
				wpmake_advance_user_avatar_params.wpmake_advance_user_avatar_remove_nonce;

			$.ajax({
				url:  url,
				type: "POST",
				beforeSend: function () {
					var $wrap = $node.closest(".wpmake-advance-user-avatar-upload");
					$wrap.find("#wpmake-advance-user-avatar-pic").val("");
					$wrap.find(".wpmake-advance-user-avatar-input").val("");
					$wrap.find(".wpmake-advance-user-avatar-error").remove();
					$(".profile-preview").attr(
						"src",
						"https://secure.gravatar.com/avatar/?s=96&d=mm&r=g"
					);
				},
				complete: function () {
					var $wrap = $node.closest(".wpmake-advance-user-avatar-upload");
					$wrap.find(".wpmake-advance-user-avatar-remove").hide();
					$wrap.find(".wpmake_advance_user_avatar_take_snapshot").show();
					$wrap.find(".wpmake_advance_user_avatar_upload").show();
					$wrap.find('input[type="file"]').off("click");
				}
			});
		},

		// blob: pre-cropped Blob/File to upload; null = use file from $node
		send_file: function ($node, blob) {
			var p   = wpmake_advance_user_avatar_params;
			var url = p.ajax_url +
				"?action=wpmake_advance_user_avatar_upload_method_upload&security=" +
				p.wpmake_advance_user_avatar_upload_nonce;

			var formData = new FormData();

			if (blob) {
				formData.append("file", new File([blob], "avatar.jpeg", { type: "image/jpeg" }));
			} else if ($node[0].files && $node[0].files[0]) {
				formData.append("file", $node[0].files[0]);
			} else {
				return; // nothing to send
			}

			// Empty cropped_image signals server to skip Jcrop re-crop
			formData.append("cropped_image",    "");
			formData.append("valid_extension",  $('input[name="profile-pic"]').attr("accept"));
			formData.append("max_uploaded_size", $('input[name="profile-pic"]').attr("size"));

			var $wrap      = $node.closest(".wpmake-advance-user-avatar-upload");
			var $uploadBtn = $wrap.find(".wpmake_advance_user_avatar_upload");
			var origTxt    = $uploadBtn.text();

			$.ajax({
				url:         url,
				data:        formData,
				type:        "POST",
				processData: false,
				contentType: false,
				beforeSend: function () {
					$uploadBtn.text(p.wpmake_advance_user_avatar_uploading);
				},
				complete: function (xhr) {
					$uploadBtn.text(origTxt);
					$wrap.find(".wpmake-advance-user-avatar-error, .wpmake-advance-user-avatar-success").remove();
					$wrap.find(".wpmake-advance-user-avatar-input").val("");

					var errorIcon = '<img src="' + p.wpmake_assets_url + '/images/error.png" width="30px">';
					var isError   = false;
					var message   = "";

					try {
						var res = JSON.parse(xhr.responseText);
						if (typeof res.success === "undefined" || typeof res.data === "undefined") {
							throw new Error();
						}

						if (!res.success) {
							message = errorIcon + res.data.message;
							isError = true;
						} else {
							var successIcon = '<img src="' + p.wpmake_assets_url + '/images/success.png" width="30px">';
							$(".wpmake-advance-user-avatar-container .profile-preview, .wpmake-advance-user-avatar-upload .profile-preview")
								.attr("src", res.data.profile_picture_url);
							$wrap.find(".wpmake-advance-user-avatar-remove").show();
							$wrap.find(".wpmake_advance_user_avatar_take_snapshot, .wpmake_advance_user_avatar_upload").hide();
							$wrap.find(".wpmake-advance-user-avatar-input").val(res.data.attachment_id);
							$wrap.append(
								'<div class="wpmake-advance-user-avatar-success">' +
								successIcon + p.wpmake_advance_user_avatar_upload_success_message +
								"</div>"
							);
						}
					} catch (e) {
						message = errorIcon + p.wpmake_advance_user_avatar_something_wrong;
						isError = true;
					}

					if (isError) {
						$wrap.append('<div class="wpmake-advance-user-avatar-error">' + message + "</div>");
					}

					$(document).trigger("wpmake_advance_user_avatar_ajax_complete");
				}
			});
		},

		dataURItoBlob: function (dataURI) {
			var parts    = dataURI.split(",");
			var byteStr  = (parts[0].indexOf("base64") >= 0) ? atob(parts[1]) : unescape(parts[1]);
			var mime     = parts[0].split(":")[1].split(";")[0];
			var ia       = new Uint8Array(byteStr.length);
			for (var i = 0; i < byteStr.length; i++) { ia[i] = byteStr.charCodeAt(i); }
			return new Blob([ia], { type: mime });
		}
	};

	WPMake_Advance_User_Avatar_Frontend.init();

	/* =========================================================================
	   Upload button — trigger hidden file input
	   ========================================================================= */
	$(document).on("click", ".wpmake_advance_user_avatar_upload", function () {
		$(this).closest(".wpmake-advance-user-avatar-upload")
			.find('input[type="file"]')
			.trigger("click");
	});

	/* =========================================================================
	   Webcam button
	   ========================================================================= */
	$(document).on("click", ".wpmake_advance_user_avatar_take_snapshot", function () {
		var p          = wpmake_advance_user_avatar_params;
		var $wrap      = $(this).closest(".wpmake-advance-user-avatar-upload");
		var $fileInput = $wrap.find('input[type="file"]');

		function openWebcam() {
			var container = document.createElement("div");
			container.id  = "wpmake-webcam-inner";

			WPMakeModal.open({
				title: p.wpmake_advance_user_avatar_capture,
				html:  container,
				buttons: [
					{
						text:      p.wpmake_advance_user_avatar_cancel_button,
						className: "wpmake-btn-secondary",
						onClick:   function () { Webcam.reset(); }
					},
					{
						text:      p.wpmake_advance_user_avatar_capture,
						className: "wpmake-btn-primary",
						closes:    false,
						onClick:   function () {
							Webcam.snap(function (dataUri) {
								Webcam.reset();
								WPMakeModal.close();

								if (p.wpmake_advance_user_avatar_enable_cropping_interface) {
									WPMakeCropper.open(
										dataUri,
										function (croppedDataURL) { // onSave
											var blob = WPMake_Advance_User_Avatar_Frontend.dataURItoBlob(croppedDataURL);
											WPMake_Advance_User_Avatar_Frontend.send_file($fileInput, blob);
										},
										function () { openWebcam(); }, // onRetake
										p.wpmake_advance_user_avatar_retake || "Retake"
									);
								} else {
									var blob = WPMake_Advance_User_Avatar_Frontend.dataURItoBlob(dataUri);
									WPMake_Advance_User_Avatar_Frontend.send_file($fileInput, blob);
								}
							});
						}
					}
				],
				onOpen: function () {
					var w = 320, h = 240;
					if (window.innerWidth < window.innerHeight) { w = 240; h = 320; }

					Webcam.set({
						width: w, height: h,
						dest_width: w, dest_height: h,
						crop_width: w, crop_height: h,
						image_format: "jpeg", jpeg_quality: 90
					});

					Webcam.off("error");
					Webcam.on("error", function (err) {
						var isSSL = (err.name === "WebcamError");
						Webcam.reset();
						WPMakeModal.open({
							title: isSSL
								? p.wpmake_advance_user_avatar_ssl_error_title
								: p.wpmake_advance_user_avatar_permission_error_title,
							html: "<p style='margin:0'>" + (isSSL
								? p.wpmake_advance_user_avatar_ssl_error_text
								: p.wpmake_advance_user_avatar_permission_error_text) + "</p>",
							buttons: [
								{
									text:      p.wpmake_advance_user_avatar_cancel_button_confirmation,
									className: "wpmake-btn-secondary"
								},
								{
									text:      p.wpmake_advance_user_avatar_use_upload_instead,
									className: "wpmake-btn-primary",
									onClick:   function () {
										$wrap.find(".wpmake_advance_user_avatar_upload").trigger("click");
									}
								}
							]
						});
					});

					Webcam.attach("#wpmake-webcam-inner");

				// Face-guide overlay (corner brackets, grid, concentric circles, badge)
				var ov = document.createElement("div");
				ov.className = "wpmake-webcam-overlay";
				ov.innerHTML =
					'<span class="wpmake-corner wpmake-corner-tl"></span>' +
					'<span class="wpmake-corner wpmake-corner-tr"></span>' +
					'<span class="wpmake-corner wpmake-corner-bl"></span>' +
					'<span class="wpmake-corner wpmake-corner-br"></span>' +
					'<div class="wpmake-grid">' +
						'<div class="wpmake-grid-v wpmake-grid-v1"></div>' +
						'<div class="wpmake-grid-v wpmake-grid-v2"></div>' +
						'<div class="wpmake-grid-h wpmake-grid-h1"></div>' +
						'<div class="wpmake-grid-h wpmake-grid-h2"></div>' +
					'</div>' +
					'<div class="wpmake-face-guide">' +
						'<div class="wpmake-circle-outer"></div>' +
						'<div class="wpmake-circle-mid"></div>' +
						'<div class="wpmake-circle-dot"></div>' +
					'</div>' +
					'<div class="wpmake-position-badge">Position face</div>';
				document.getElementById("wpmake-webcam-inner").appendChild(ov);
				}
			});
		}

		openWebcam();
	});

	/* =========================================================================
	   Remove button — inline confirmation (no modal)
	   ========================================================================= */
	$(document).on("click", ".wpmake-advance-user-avatar-remove:not(.wpmake-confirming)", function () {
		var p    = wpmake_advance_user_avatar_params;
		var $btn = $(this);
		$btn.addClass("wpmake-confirming");

		var $bar = $(
			'<span class="wpmake-remove-confirm">' +
				'<span class="wpmake-remove-confirm-text">' +
					p.wpmake_advance_user_avatar_remove_confirm_text +
				"</span>" +
				' <button type="button" class="button wpmake-remove-yes">' +
					p.wpmake_advance_user_avatar_remove_yes +
				"</button>" +
				' <button type="button" class="button wpmake-remove-no">' +
					p.wpmake_advance_user_avatar_cancel_button +
				"</button>" +
			"</span>"
		);
		$btn.hide().after($bar);

		$bar.find(".wpmake-remove-yes").one("click", function () {
			$bar.remove();
			$btn.removeClass("wpmake-confirming").show();
			WPMake_Advance_User_Avatar_Frontend.remove_avatar($btn);
		});

		$bar.find(".wpmake-remove-no").one("click", function () {
			$bar.remove();
			$btn.removeClass("wpmake-confirming").show();
		});
	});

	/* =========================================================================
	   Auto-dismiss success / error messages after 5 s
	   ========================================================================= */
	$(document).on("wpmake_advance_user_avatar_ajax_complete", function () {
		setTimeout(function () {
			$(".wpmake-advance-user-avatar-error, .wpmake-advance-user-avatar-success").remove();
		}, 5000);
	});
});
