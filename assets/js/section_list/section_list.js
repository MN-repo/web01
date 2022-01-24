function section_list(def) {
	var sections = document.querySelectorAll("body > section");
	var group = document.createElement("div");
	group.id = "section-list";
	var select = document.createElement("select");

	var showall = document.createElement("button");
	group.appendChild(showall);
	showall.textContent = "all";
	showall.addEventListener("click", function() {
		sections.forEach(function(section) {
			section.querySelector("h1").style.display = "block";
			section.style.display = "block";
		});
		group.style.display = "none";
	});

	sections.forEach(function(section) {
		section.querySelector("h1").style.display = "none";
		select[select.options.length] = new Option(
			section.querySelector("h1").textContent,
			section.id,
			section.id == def,
			window.location.hash ?
				"#" + section.id == window.location.hash :
				section.id == def
		);
	});

	group.appendChild(select);
	document.querySelector("body > h1:nth-of-type(2)")
		.insertAdjacentElement("afterend", group);

	window.addEventListener("hashchange", function() {
		sections.forEach(function(section) { section.style.display = "none"; });
		document.querySelector(window.location.hash).style.display = "block";
	});

	window.location.hash = "";
	window.location.hash = "#" + select.value;
	select.addEventListener("change", function() {
		window.location.hash = "#" + select.value;
	});
}
