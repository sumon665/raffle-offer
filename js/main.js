jQuery(document).ready(function($) {
	var offsub = $(".suboff").text();
	if (offsub) {
		$(".single-product div.product p.price").hide();
		$(".woocommerce-product-details__short-description").hide();
		$(".single-product div.product form.cart").hide();
        $(".razzi-sticky-add-to-cart form.cart").hide();
	}

	/* Ajax request */
     $("#offer_form").submit(function(e){
        e.preventDefault();
        $("#offerbtn").text("Loading...");
        var pid = $("#pid").val();
        var uid = $("#uid").val();
        var offer = $("#offer_amount").val();
        
        $.ajax({
            url: ajax_object.ajax_url, // or example_ajax_obj.ajaxurl if using on frontend
            data: {
                'action': 'submit_offer_request',
                'pid' : pid,
                'uid' : uid,
                'offer' : offer,
            },
            dataType: 'json',
            type: "post",            
            success: function (data) {
            	if (data['error'] == 1) {
            		$(".error_msg").text("Your offer is too high. Please submit a lower offer.");
            	} else if (data['error'] == 2) {
            		$(".error_msg").text("Your offer is too low. Please submit a higher offer.");
            	} else if (data['error'] == 3) {
            		$(".error_msg").text("Whoops, it looks like you’ve already purchased this item.");
            	} else if (data['error'] == 4) {
                    location.reload();
                } else if (data['error'] == 5) {
                    window.location.href = "https://woocommerce-532030-2365908.cloudwaysapps.com/checkout/";
                }

                console.log (data);
                $("#offer_amount").val('');
            	$("#offerbtn").text("Submit an Offer");
           }
        });
    });

});