//Code by: Electroalek<http://electroalek.com>, 2020

$(document).ready(function(){

	$("form[method=post]").append(
		$("<input>")
			.attr("type", "hidden")
			.attr("name", "csrf")
			.attr("value", $("meta[name=csrf]").attr("content"))
	);

	$("form").submit(function(e){
		if($(this).hasClass("submitted")) {
			e.preventDefault();
		}else{
			$(this).addClass("submitted");
		}
	});

	$('[data-toggle="tooltip"]').tooltip();

	$(".auto-copy")
		.focus(function(e){
			$(this).select();
			try{
				document.execCommand("copy");
			}catch(e){
			}
		})
		.mouseup(function(e){
			e.preventDefault();
		});

	$("select").each(function(){
		var raw = $(this).data("value");
		if(raw == null || raw == undefined) return;
		$(this).find('option[value="' + raw + '"]').attr("selected", "selected");
	});

	$("input[type=datetime-local]").each(function(){
		var raw = $(this).data("value");
		if(raw == "" || raw == null || raw == undefined || raw == 0) return;
		var dateObj = new Date(raw * 1000);
		var formated = ""; //yyyy-MM-ddThh:mm
		formated += dateObj.getFullYear();
		formated += "-";
		formated += ((dateObj.getMonth() + 1) <= 9 ? "0" : "") + (dateObj.getMonth() + 1).toString();
		formated += "-";
		formated += (dateObj.getDate() <= 9 ? "0" : "") + dateObj.getDate().toString();
		formated += "T";
		formated += (dateObj.getHours() <= 9 ? "0" : "") + dateObj.getHours().toString();
		formated += ":";
		formated += (dateObj.getMinutes() <= 9 ? "0" : "") + dateObj.getMinutes().toString();
		$(this).val(formated);
	});

	$("input[type=date]").each(function(){
		var raw = $(this).data("value");
		if(raw == "" || raw == null || raw == undefined || raw == 0) return;
		var dateObj = new Date(raw * 1000);
		var formated = ""; //yyyy-MM-dd
		formated += dateObj.getFullYear();
		formated += "-";
		formated += ((dateObj.getMonth() + 1) <= 9 ? "0" : "") + (dateObj.getMonth() + 1).toString();
		formated += "-";
		formated += (dateObj.getDate() <= 9 ? "0" : "") + dateObj.getDate().toString();
		$(this).val(formated);
	});

	$(".toggle-password").click(function() {
		$(this).toggleClass("fa-eye fa-eye-slash");
		var input = $($(this).attr("toggle"));
		if (input.attr("type") == "password") {
			input.attr("type", "text");
		} else {
			input.attr("type", "password");
		}
	});

	if($(".auto-option").length){
		$(".auto-option").each(function(){
			var container = $(this);
			container.find(".auto-option-element").each(function(){
				var element = $(this);
				element.addClass("d-flex mb-3");
				element.append($("<button>")
					.attr("type", "button")
					.addClass("btn btn-link text-danger fa fa-trash-alt auto-option-remove")
					.click(function(){
						element.remove();
					})
				);
			});
			var copy = $(this).find(".auto-option-element").first().clone();
			copy.find("input").val("");
			container.prepend(
				$("<div>")
					.addClass("d-flex justify-content-end mt-n4")
					.append($("<button>")
						.attr("type", "button")
						.addClass("btn btn-link text-success fa fa-plus auto-option-remove")
						.click(function(){
							var fresh = copy.clone();
							fresh.find(".auto-option-remove").click(function(){
								fresh.remove();
							});
							container.append(fresh);
						})
					)
			);
		});
	}

	if($(".dynamic-menu").length){
		var url = window.location.pathname;
		var urlRegExp = new RegExp(url.replace(/\/$/,'') + "$");
		$(".dynamic-menu a").each(function(){
			if(urlRegExp.test(this.href.replace(/\/$/,''))){
				$(this).addClass("active");
			}
		});
	}

    $('.nav-link').on('click', function () {
        $('.navbar-collapse').collapse('hide');
    });
    
    var html_body = $('html, body');
    $('.custm_scrl a').on('click', function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                html_body.animate({
                    scrollTop: target.offset().top + 10
                }, 1500);
                return false;
            }
        }
	});
    
  $(window).scroll(function () {
        var $scrolling = $(this).scrollTop();
        var bc2top = $(".back-top-btn");
        if ($scrolling > 150) {
            bc2top.fadeIn(1000);
        } else {
            bc2top.fadeOut(1000);
        }
    });

    $('.back-top-btn').click(function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 1500);
    });

	$('.payment_boxs_wrap').click(function(){
		$('.payment_boxs_wrap').removeClass("actt");
		$(this).addClass("actt");
	});

	if($("#navbarNav").length){

		$("#navbarNav .nav-link").each(function(){
			try{
				var selected = new URL($(this).attr("href"));
				var current = new URL(window.location.href);
				if(current.pathname.trim("/").startsWith(selected.pathname.trim("/"))  && 
				(selected.hash == "" || selected.hash == current.hash)){
					$(this).addClass("active");
				}
			}catch{

			}
		});

	}


	
	function calculatePrice(){
		var option = $("#product-option option:selected");
		var qty = $("#product-quantity").val() - 0;
		var price = option.data("price") - 0;
		var currency = $("#product-total").data("currency");
		var price = currency + (price * qty).toFixed(2);
		$("#product-total").text(price);
	}

	function setQuantity(){
		var qty = $("#product-quantity");
		var option = $("#product-option option:selected");
		var origMin = qty.data("min") - 0;
		if(origMin == 0) origMin = 1;
		var origMax = qty.data("max") - 0;
		if(origMax == 0) origMax = 1000;
		var min = option.data("min") - 0;
		var max = option.data("max") - 0;
		var stock = option.data("stock") - 0;
		$("#product-button").prop("disabled", stock == 0);
		if(stock == 0){
			qty.attr("max", 0);
			qty.attr("min", 0);
			qty.val(0);
		}else{
			if(max > 0){
				qty.attr("max", stock > max ? max : stock);
			}else{
				qty.attr("max", stock > origMax ? origMax : stock);
			}
			if(min > 0) qty.attr("min", min);
			qty.val(min > 0 ? min : origMin);
		}
		calculatePrice();
	}

	if($("#product-form").length){

		setQuantity();

	}

	$("body").on("click change mouseup focus mousedown blur", "#product-option", setQuantity);
	$("body").on("click change mouseup focus mousedown blur", "#product-quantity", calculatePrice);

	if($("#buy-now-modal").length){
		$(".buy-now-btn").click(function(){
			var id = $(this).data("product");
			$("#buy-now-loader").show();
			$("#buy-now-content").empty();
			$("#buy-now-title").text($(this).data("title"));
			$("#buy-now-modal").modal("show");
			$("#buy-now-content").load("/product/ajax/" + id, function(){
				$("#buy-now-content form[method=post]").append(
					$("<input>")
						.attr("type", "hidden")
						.attr("name", "csrf")
						.attr("value", $("meta[name=csrf]").attr("content"))
				);
				$("#buy-now-loader").hide();
				setQuantity();
			});
		});
	}

	if($("#checkout-form").length){

		$(".checkout-processor").change(function(){
			$("#checkout-captcha").toggle($(this).data("captcha") == "1");
		})

	}

	if($("#search-form").length){

		$("#search-title, #search-category, #search-button").on(
			"click mouseup keyup change", function(e){

			var title = $("#search-title").val();
			var category = $("#search-category").val();
			var products = $("#products-parent .product-item");

			products.hide().filter(function(){
				var info = $(this).data();
				return (title == "" || info.title.toLowerCase().includes(title.toLowerCase())) && (info.category == category || category == "");
			}).show();

			if($("#products-parent .product-item:visible").length > 0){
				$("#search-empty").hide();
			}else{
				$("#search-empty").show();
			}

		});
	}
	
});