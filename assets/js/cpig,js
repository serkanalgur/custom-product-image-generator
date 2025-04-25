jQuery(function ($) {
	// Load Google Fonts via WebFontLoader
	WebFont.load({
		google: {
			families: ["Roboto:400,700", "Montserrat:400,700", "Open+Sans:400,700", "Noto+Sans", "Inter", "Oswald"],
		},
		active: function () {
			if (window.canvas) window.canvas.renderAll();
		},
	});

	const canvas = new fabric.Canvas("cpig-canvas");
	let fieldCount = 0;
	let grid = 10;
	let logo1, logo2;

	function addGridToFabric(wd, hg) {
		for (var i = 0; i < wd / grid; i++) {
			canvas.add(
				new fabric.Line([i * grid, 0, i * grid, wd], {
					stroke: "#efefef",
					selectable: false,
					opacity: 0.1,
				})
			);
			canvas.add(
				new fabric.Line([0, i * grid, hg, i * grid], {
					stroke: "#efefef",
					selectable: false,
					opacity: 0.1,
				})
			);
		}
	}

	function RemoveGrid() {
		var objects = canvas.getObjects("line");
		for (let i in objects) {
			canvas.remove(objects[i]);
		}
	}

	function createFieldControls(idx) {
		return $(
			`<div class="cpig-text-item" data-idx="${idx}">
        <input type="text" class="cpig-text-field" data-idx="${idx}" placeholder="Text #${idx + 1}" />
        <select class="cpig-font-family" data-idx="${idx}">
          <option>Arial</option><option>Helvetica</option><option>Tahoma</option>
          <option>Roboto</option><option>Montserrat</option><option>Open Sans</option>
          <option>Noto Sans</option><option>Inter</option><option>Oswald</option>
        </select>
        <input type="number" class="cpig-font-size" data-idx="${idx}" min="10" max="100" value="40" />
        <input type="color" class="cpig-font-color" data-idx="${idx}" value="#000000" />
        <button class="button cpig-bold-toggle" data-idx="${idx}" title="Bold">B</button>
        <button class="button cpig-italic-toggle" data-idx="${idx}" title="Italic">I</button>
        <button class="button cpig-underline-toggle" data-idx="${idx}" title="Underline">U</button>
        <select class="cpig-text-align" data-idx="${idx}">
          <option value="left">Left</option>
          <option value="center">Center</option>
          <option value="right">Right</option>
        </select>
      </div>`
		);
	}

	function addTextField() {
		const idx = fieldCount++;
		const textObj = new fabric.IText("Text", {
			left: canvas.getWidth() / 2,
			top: canvas.getHeight() / 4 + idx * 30,
			fontFamily: "Arial",
			fontSize: 40,
			fill: "#000",
			originX: "center",
		});
		canvas.add(textObj).setActiveObject(textObj);

		const $ctrl = createFieldControls(idx);
		$("#cpig-text-list").append($ctrl);

		$ctrl.on("input change click", "input, select, button", function () {
			const i = $(this).data("idx");
			const obj = canvas.getObjects("i-text")[i];
			obj.set({
				text: $(`.cpig-text-field[data-idx="${i}"]`).val() || "Text",
				fontFamily: $(`.cpig-font-family[data-idx="${i}"]`).val(),
				fontSize: parseInt($(`.cpig-font-size[data-idx="${i}"]`).val(), 10),
				fill: $(`.cpig-font-color[data-idx="${i}"]`).val(),
				fontWeight: $(`.cpig-bold-toggle[data-idx="${i}"]`).hasClass("active") ? "bold" : "normal",
				fontStyle: $(`.cpig-italic-toggle[data-idx="${i}"]`).hasClass("active") ? "italic" : "normal",
				underline: $(`.cpig-underline-toggle[data-idx="${i}"]`).hasClass("active"),
				textAlign: $(`.cpig-text-align[data-idx="${i}"]`).val(),
			});
			canvas.renderAll();
		});

		$ctrl.find(".cpig-bold-toggle").on("click", function () {
			$(this).toggleClass("active").trigger("input");
		});
		$ctrl.find(".cpig-italic-toggle").on("click", function () {
			$(this).toggleClass("active").trigger("input");
		});
		$ctrl.find(".cpig-underline-toggle").on("click", function () {
			$(this).toggleClass("active").trigger("input");
		});
	}

	canvas.setWidth(600).setHeight(400);

	canvas.on("text:changed", function (event) {
		// Get all iText objects on the canvas
		const allITexts = canvas.getObjects().filter((obj) => obj instanceof fabric.IText);
		allITexts.forEach((iText, index) => {
			let input = document.querySelector(`input[data-idx="${index}"]`);
			input.value = iText.text;
			//console.log(`iText ${index}:`, iText.text);
		});
	});

	$("#cpig-select-image").on("click", (e) => {
		e.preventDefault();
		const frame = wp.media({ multiple: false });
		frame.open();
		frame.on("select", () => {
			const sel = frame.state().get("selection").first().toJSON();
			fabric.Image.fromURL(sel.url, (img) => {
				canvas.clear();
				img.set({ selectable: false, evented: false });
				canvas.setWidth(img.width).setHeight(img.height);
				canvas.add(img);
				canvas.sendToBack(img);
				addGridToFabric(img.width, img.height);
				fieldCount = 0;
				$("#cpig-text-list").empty();
				addTextField();
			});
		});
	});

	$("#cpig-product-select").select2({
		placeholder: "Select a product",
		allowClear: true,
		ajax: {
			url: cpig_ajax.ajax_url,
			dataType: "json",
			delay: 250,
			data: (params) => ({
				action: "cpio_search_products",
				q: params.term || "",
				page: params.page || 1,
				nonce: cpig_ajax.nonce,
			}),
			processResults: (data) => data,
			cache: true,
		},
		minimumInputLength: 0,
	});

	$("#cpig-add-text").on("click", (e) => {
		e.preventDefault();
		addTextField();
	});

	// Add Logo 1

	$("#cpig-add-logo1").on("click", (e) => {
		e.preventDefault();
		const frame = wp.media({ multiple: false });
		frame.open();
		frame.on("select", () => {
			const sel = frame.state().get("selection").first().toJSON();
			fabric.Image.fromURL(sel.url, (img) => {
				img.scaleToWidth(100);
				img.set({ left: 100, top: 100, originX: "center", originY: "center" });
				logo1 = img;
				canvas.add(img).setActiveObject(img);
			});
		});
	});

	// Add Logo 2
	$("#cpig-add-logo2").on("click", (e) => {
		e.preventDefault();
		const frame = wp.media({ multiple: false });
		frame.open();
		frame.on("select", () => {
			const sel = frame.state().get("selection").first().toJSON();
			fabric.Image.fromURL(sel.url, (img) => {
				img.scaleToWidth(100);
				img.set({ left: 100, top: 100, originX: "center", originY: "center" });
				logo1 = img;
				canvas.add(img).setActiveObject(img);
			});
		});
	});

	$("#cpig-generate").on("click", () => {
		const pid = $("#cpig-product-select").val();
		const dash = $(".mika");
		if (!pid) return $.notify("Please select a product.", "error");
		RemoveGrid();
		const dataURL = canvas.toDataURL({ format: "png" });
		dash.removeClass("dnon").addClass("spin");
		$.post(cpig_ajax.ajax_url, {
			action: "cpio_save_image",
			image: dataURL,
			product_id: pid,
			nonce: cpig_ajax.nonce,
		}).done(function (res) {
			if (res.success) {
				$.notify("Image saved & assigned!", "success");
				dash.removeClass("spin").addClass("dnon");
			} else {
				$.notify("Error: " + res.data, "error");
			}
		});
	});
});
