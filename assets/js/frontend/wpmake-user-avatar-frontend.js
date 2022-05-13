jQuery(function ($) {
	var WPMake_User_Avatar_Frontend = {
		init: function () {
			WPMake_User_Avatar_Frontend.process_avatar_upload();
		},
		/**
		 * Process avatar upload.
		 *
		 * @since  1.0.0
		 */
		process_avatar_upload: function () {
			$("body").on(
				"change",
				'.wpmake-user-avatar-upload-node input[type="file"]',
				function () {
					if (this.files && this.files[0]) {
						var reader = new FileReader();

						reader.onload = function (e) {
							$(".img").attr("src", e.target.result);
						};

						reader.readAsDataURL(this.files[0]);

						if (
							wpmake_user_avatar_params.wpmake_user_avatar_enable_cropping_interface
						) {
							var message_body =
								'<img id="crop_container" src="#" alt="your image" class="img"/><input type="hidden" name="cropped_image" class="cropped_image_size"/>';

							Swal.fire({
								title: wpmake_user_avatar_params.wpmake_user_avatar_crop_picture_title,
								html: message_body,
								confirmButtonText:
									wpmake_user_avatar_params.wpmake_user_avatar_crop_picture_button,
								allowOutsideClick: false,
								showCancelButton: true,
								cancelButtonText:
									wpmake_user_avatar_params.wpmake_user_avatar_cancel_button,
								customClass: {
									container:
										"wpmake-user-avatar-swal2-container",
								},
							});

							$(".swal2-cancel ").on("click", function () {
								$(".wpmake-user-avatar-upload")
									.find("#wpmake-user-avatar-pic")
									.val("");
							});
							WPMake_User_Avatar_Frontend.crop_image($(this));
						} else {
							WPMake_User_Avatar_Frontend.send_file($(this));
						}
					}
				}
			);
		},
		/**
		 * Utilizes Jcrop library to provide a space for cropping the picture
		 * and determining exact dimensions of cropped picture.
		 *
		 * @since  1.0.0
		 *
		 */
		crop_image: function (file_instance) {
			var size;
			$("#crop_container").Jcrop({
				aspectRatio: 1,
				onSelect: function (c) {
					size = { x: c.x, y: c.y, w: c.w, h: c.h };
				},
				setSelect: [100, 100, 50, 50],
			});

			$(".swal2-confirm").on("click", function () {
				var cropped_image_size = {
					x: size.x,
					y: size.y,
					w: size.w,
					h: size.h,
					holder_width: $("#crop_container").css("width"),
					holder_height: $("#crop_container").css("height"),
				};
				$(".cropped_image_size").val(
					JSON.stringify(cropped_image_size)
				);
				WPMake_User_Avatar_Frontend.send_file(file_instance);
			});
		},
		remove_avatar: function ($node) {
			var url =
				wpmake_user_avatar_params.ajax_url +
				"?action=wpmake_user_avatar_upload_remove_avatar&security=" +
				wpmake_user_avatar_params.wpmake_user_avatar_remove_nonce;

			$.ajax({
				url: url,
				type: "POST",
				beforeSend: function () {
					$node.text(
						wpmake_user_avatar_params.wpmake_user_avatar_removing
					);
					$node
						.closest(".wpmake-user-avatar-upload")
						.find("#wpmake-user-avatar-pic")
						.val("");
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-input")
						.val("");
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-error")
						.remove();
					$(".profile-preview").attr(
						"src",
						"https://secure.gravatar.com/avatar/?s=96&d=mm&r=g"
					);
				},
				complete: function (ajax_response) {
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-remove")
						.attr("style", "display:none");
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake_user_avatar_take_snapshot ")
						.removeAttr("style");
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake_user_avatar_upload ")
						.removeAttr("style");
					$node
						.closest(".wpmake-user-avatar-upload")
						.find('input[type="file"]')
						.off("click");
				},
			});
		},
		/**
		 * Sends the file, the user is willing to upload as an ajax request
		 * and receives output in order to process any errors occured during file upload
		 * or to display a preview of the picture on the frontend.
		 *
		 * @since  1.0.0
		 *
		 * @param {Function} $node Executes once the picture upload triggers an event.
		 */
		send_file: function ($node) {
			var url =
				wpmake_user_avatar_params.ajax_url +
				"?action=wpmake_user_avatar_upload_method_upload&security=" +
				wpmake_user_avatar_params.wpmake_user_avatar_upload_nonce;
			var formData = new FormData();
			var img = "";

			if (
				wpmake_user_avatar_params.wpmake_user_avatar_enable_cropping_interface
			) {
				// Get cropped img data
				img = $("#crop_container").attr("src");
			} else {
				img = $("#blob_container").attr("src");
				console.log("2");
			}

			if ($node[0].files[0]) {
				formData.append("file", $node[0].files[0]);
			} else {
				// Converts base64/URLEncoded data component to blob using link above and appends to the input type file.
				var blob = WPMake_User_Avatar_Frontend.dataURItoBlob(img);
				var fileOfBlob = new File([blob], "snapshot.jpg");
				formData.append("file", fileOfBlob);
			}
			// Appends the dimensions of cropped image
			formData.append("cropped_image", $(".cropped_image_size").val());

			formData.append(
				"valid_extension",
				$('input[name="profile-pic"]').attr("accept")
			);

			formData.append(
				"max_uploaded_size",
				$('input[name="profile-pic"]').attr("size")
			);

			var upload_node = $node
				.closest(".wpmake-user-avatar-upload")
				.find(".wp_wpmake_user_avatar_upload");
			var upload_node_value = upload_node.text();
			$.ajax({
				url: url,
				data: formData,
				type: "POST",
				processData: false,
				contentType: false,
				// tell jQuery not to set contentType
				beforeSend: function () {
					upload_node.text(
						wpmake_user_avatar_params.wpmake_user_avatar_uploading
					);
				},
				complete: function (ajax_response) {
					var message = "";
					var attachment_id = 0;
					var profile_pic_url = "";

					$node
						.parent()
						.parent()
						.parent()
						.find(".wpmake-user-avatar-error")
						.remove();
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-input")
						.val("");

					try {
						var response_obj = JSON.parse(
							ajax_response.responseText
						);

						if (
							"undefined" === typeof response_obj.success ||
							"undefined" === typeof response_obj.data
						) {
							throw wpmake_user_avatar_params.wpmake_user_avatar_something_wrong;
						}
						message = response_obj.data.message;

						if (!response_obj.success) {
							message =
								'<p class="wpmake-user-avatar-error">' +
								message +
								"</p>";
						}

						if (response_obj.success) {
							message = "";
							attachment_id = response_obj.data.attachment_id;

							// Gets the profile picture url and displays the picture on frontend
							profile_pic_url =
								response_obj.data.profile_picture_url;
							$(".wpmake-user-avatar-container")
								.find(".profile-preview")
								.attr("src", profile_pic_url);
							$node
								.closest(".wpmake-user-avatar-upload")
								.find(".profile-preview")
								.attr("src", profile_pic_url);

							// Shows the remove button and hides the upload and take snapshot buttons after successfull picture upload
							$node
								.closest(".wpmake-user-avatar-upload")
								.find(".wpmake-user-avatar-remove")
								.removeAttr("style");
							$node
								.closest(".wpmake-user-avatar-upload")
								.find(".wpmake_user_avatar_take_snapshot ")
								.attr("style", "display:none");
							$node
								.closest(".wpmake-user-avatar-upload")
								.find(".wpmake_user_avatar_upload ")
								.attr("style", "display:none");
						}
					} catch (e) {
						message =
							wpmake_user_avatar_params.wpmake_user_avatar_something_wrong;
					}

					// Finds and removes any prevaling errors and appends new errors occured during picture upload
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-error")
						.remove();
					$node
						.closest(".wpmake-user-avatar-upload")
						.find(".wpmake-user-avatar-file-error")
						.remove();
					$node
						.closest(".wpmake-user-avatar-upload")
						.append(
							'<span class="wpmake-user-avatar-error">' +
								message +
								"</span>"
						);

					if (attachment_id > 0) {
						$node
							.closest(".wpmake-user-avatar-upload")
							.find(".wpmake-user-avatar-input")
							.val(attachment_id);
					}
					upload_node.text(upload_node_value);
				},
			});
		},
		dataURItoBlob: function (dataURI) {
			// convert base64/URLEncoded data component to raw binary data held in a string
			var byteString;

			if (dataURI.split(",")[0].indexOf("base64") >= 0) {
				byteString = atob(dataURI.split(",")[1]);
			} else {
				byteString = unescape(dataURI.split(",")[1]);
			}

			// separate out the mime component
			var mimeString = dataURI.split(",")[0].split(":")[1].split(";")[0];

			// write the bytes of the string to a typed array
			var ia = new Uint8Array(byteString.length);

			for (var i = 0; i < byteString.length; i++) {
				ia[i] = byteString.charCodeAt(i);
			}

			return new Blob([ia], { type: mimeString });
		},
	};

	WPMake_User_Avatar_Frontend.init(jQuery);

	$(document).on("click", ".wpmake_user_avatar_upload", function () {
		$(this)
			.closest(".wpmake-user-avatar-upload")
			.find('input[type="file"]')
			.trigger("click");
	});

	$(document).on("click", ".wpmake_user_avatar_take_snapshot", function () {
		var message_body = '<div id="my_camera"></div>';
		var $this = $(this);
		Swal.fire({
			title: wpmake_user_avatar_params.wpmake_user_avatar_capture,
			html: message_body,
			confirmButtonText:
				wpmake_user_avatar_params.wpmake_user_avatar_capture,
			allowOutsideClick: false,
			showCancelButton: true,
			cancelButtonText:
				wpmake_user_avatar_params.wpmake_user_avatar_cancel_button,
			customClass: {
				container: "wpmake-user-avatar-swal2-container",
			},
		});

		// Standard image frame size for bigger screen devices
		var width = 320;
		var height = 240;

		// Check if screen size is of smaller screen devices and change height and width.
		if ($(window).width() < $(window).height()) {
			// Standard image frame size for smaller screen devices
			width = 240;
			height = 320;
		}

		/**
		 * Utilizes Webcam js library to provide a container for taking snapshot
		 *
		 * @since  1.0.0
		 *
		 */
		Webcam.set({
			width: width,
			height: height,
			dest_width: width,
			dest_height: height,
			crop_width: width,
			crop_height: height,
			image_format: "jpeg",
			jpeg_quality: 90,
		});

		var error_exist = false;
		Webcam.on("error", function (err) {
			var title = "",
				error_msg = "";

			if ("WebcamError" === err.name) {
				title =
					wpmake_user_avatar_params.wpmake_user_avatar_ssl_error_title;
				error_msg =
					wpmake_user_avatar_params.wpmake_user_avatar_ssl_error_text;
			} else {
				title =
					wpmake_user_avatar_params.wpmake_user_avatar_permission_error_title;
				error_msg =
					wpmake_user_avatar_params.wpmake_user_avatar_permission_error_text;
			}

			error_exist = true;
			swal.fire({
				icon: "warning",
				title: title,
				html: error_msg,
				showConfirmButton: false,
				showCancelButton: true,
				cancelButtonText:
					wpmake_user_avatar_params.wpmake_user_avatar_cancel_button_confirmation,
				cancelButtonColor: "#236bb0",
				customClass: {
					container: "wpmake-user-avatar-swal2-container",
				},
			});
		});

		if (!error_exist) {
			Webcam.attach("#my_camera");

			$(".swal2-confirm").on("click", function () {
				// take snapshot and get image data
				Webcam.snap(function (data_uri) {
					if (
						wpmake_user_avatar_params.wpmake_user_avatar_enable_cropping_interface
					) {
						// display results in page
						var messages =
							'<img id="crop_container" src="#" alt="your image" class="img"/><input type="hidden" name="cropped_image" class="cropped_image_size"/>';

						Swal.fire({
							title: wpmake_user_avatar_params.wpmake_user_avatar_crop_picture_title,
							html: messages,
							confirmButtonText:
								wpmake_user_avatar_params.wpmake_user_avatar_crop_picture_button,
							allowOutsideClick: false,
							showCancelButton: true,
							cancelButtonText:
								wpmake_user_avatar_params.wpmake_user_avatar_cancel_button,
							customClass: {
								container: "wpmake-user-avatar-swal2-container",
							},
						});

						$("#crop_container").attr("src", data_uri);
						WPMake_User_Avatar_Frontend.crop_image(
							$this
								.closest(".wpmake-user-avatar-upload")
								.find(
									'.wpmake-user-avatar-upload-node input[type="file"]'
								)
						);
					} else {
						$this
							.closest(".wpmake-user-avatar-upload")
							.append(
								'<img id="blob_container" src="' +
									data_uri +
									'" alt="your image" class="img" style="display:none;"/>'
							);

						WPMake_User_Avatar_Frontend.send_file(
							$this
								.closest(".wpmake-user-avatar-upload")
								.find(
									'.wpmake-user-avatar-upload-node input[type="file"]'
								)
						);
					}
				});
				Webcam.reset();
			});

			$(".swal2-cancel").on("click", function () {
				Webcam.reset();
			});
		}
	});

	$(document).on("click", ".wpmake-user-avatar-remove", function () {
		WPMake_User_Avatar_Frontend.remove_avatar($(this));
	});
});
