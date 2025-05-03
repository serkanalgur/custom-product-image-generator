jQuery(function ($) {
	$("select#cpig-template-select").on("change", function () {
		// AJAX preview
		const id = $(this).val();
		if (!id) return;
		$.post(
			cpig_ajax.ajax_url,
			{ action: "cpig_get_template_preview", template_id: id, nonce: cpig_ajax.nonce },
			function (data) {
				$("#cpig-template-preview").remove();
				$("#cpig-template-select").after(
					`<img id="cpig-template-preview" src="${data.preview_url}" style="width:100%;margin-top:10px">`
				);
			},
			"json"
		);
	});
});
