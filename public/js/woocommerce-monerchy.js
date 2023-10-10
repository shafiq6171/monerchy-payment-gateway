(function( $ ) {
	$(document).ready(function(){
		

		const valitToken 		= monerchy_params.token;
		const returnUrl 		= monerchy_params.return_url;
		const callbackUrl 		= monerchy_params.callback_url;
		const cancelReturnUrl 	= monerchy_params.cancel_return_url;
		const failedReturnUrl 	= monerchy_params.failed_return_url;
		const successReturnUrl 	= monerchy_params.success_return_url;
		const conversion 		= monerchy_params.conversion;
		const cartTotal 		= monerchy_params.cart_total;
		let description			= monerchy_params.description;
		
		
		
		
		
		
	var successCallback = function(data) {

		var checkout_form = $( 'form.woocommerce-checkout' );

		// deactivate the tokenRequest function event
		checkout_form.off( 'checkout_place_order', tokenRequest );

		// submit the form now
		checkout_form.submit();

	};
		
	var tokenRequest = function() {
		var settings = {
			  "url": "https://sdk.monerchy.com/transactions",
			  "method": "POST",
			  "timeout": 0,
				"headers": {
				"Content-Type": "application/json",
				"Accept": "application/json",
				"Authorization": "Basic "+valitToken
			  },
			  "data": JSON.stringify({
				"amount": cartTotal,
				"currency": "EUR",
				"returnUrl": returnUrl,
				"callbackUrl": callbackUrl,
				"cancelReturnUrl": cancelReturnUrl,
				"failedReturnUrl": failedReturnUrl,
				"successReturnUrl": successReturnUrl,
				"description": description,
				"settings": {
				  "skipAuth": true,
				  "amountConversion": {
					"type": conversion
				  }
				},
				"metadata": {},
				"expiresAt": new Date(Date.now() + 3 * 60 * 60 * 1000),
				
			  }),
			};
		let result = false;
		$.ajax(settings).done(function (response) {
			if(response.payload.paymentUrl){
				console.log(response);
				//document.location.href = response.payload.paymentUrl;
				let transaction = response.payload.id;
				$('#payment_transactions_id').val(transaction);
				//window.open(response.payload.paymentUrl, '_blank');
			}
		});
		return false;
			
	};
	var checkout_form = $( 'form.woocommerce-checkout' );
	checkout_form.on( 'checkout_place_order', tokenRequest );

  });
})( jQuery );
