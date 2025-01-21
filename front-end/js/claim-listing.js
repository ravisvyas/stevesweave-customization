jQuery(document).ready(function($) {
    // Handle form submission
    $('#stevesweave-claim-form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submit
        //Validate Form
        let button      =   jQuery('#steve-claim-submit-btn');
        let requestis   =   true;
        let listing_id = (typeof claimListingData.listingID !== 'undefined' && claimListingData.listingID !== null && claimListingData.listingID.trim() !== '') ? claimListingData.listingID : '';
        let success = jQuery('.claim-submission-success-section');
        let error   = jQuery('.claim-error');
        // console.log(listing_id);
        var full_name   =   jQuery('#full_name').val();
        //Full Name
        if(full_name == ''){
            requestis = false
            jQuery('#full_name').css('outline', '1px solid red');
            jQuery('.full_name_error').remove();
            jQuery('#full_name').after('<span class="full_name_error error" style="display:block; color:red;">Please fill out this field.</span>');
        }else{
            jQuery('.full_name_error').remove();
            jQuery('#full_name').css('outline', 'none');
        }
        //Email Address
        var email_address   =   jQuery('#email_address').val();
        if(email_address == ''){
            requestis = false
            jQuery('#email_address').css('outline', '1px solid red');
            jQuery('.email_address_error').remove();
            jQuery('#email_address').after('<span class="email_address_error error" style="display:block; color:red;">Please fill out this field.</span>');
        }else{
            jQuery('.email_address_error').remove();
            jQuery('#email_address').css('outline', 'none');
        }
        //Phone Number
        var phone_number   =   jQuery('#phone_number').val();
        if(phone_number == ''){
            requestis = false
            jQuery('#phone_number').css('outline', '1px solid red');
            jQuery('.phone_number_error').remove();
            jQuery('#phone_number').after('<span class="phone_number_error error" style="display:block; color:red;">Please fill out this field.</span>');
        }else{
            jQuery('.phone_number_error').remove();
            jQuery('#phone_number').css('outline', 'none');
        }
        // Create new FormData object to get form data
        var formData = new FormData(this);
        // Add additional data to FormData if needed
        formData.append('action', 'stevesweave-submit-claim'); // Adding the action for the server-side handler
        formData.append('listing_id', listing_id); // Add dynamic listing ID
        // Optional: Add a nonce for security
        formData.append('security', claimListingData.nonce);
        if(requestis){
            jQuery('.claim-loader').css('display', 'flex');
            // Send the form data using AJAX
            $.ajax({
                url: claimListingData.ajax_url, 
                type: 'POST',                  
                data: formData,                
                contentType: false,            
                processData: false,          
                success: function(response){
                    let responseData    =   JSON.parse(response);
                    var status          =   responseData.status;
                    var message         =   responseData.message;
                    if(status){
                        error.html('').hide();
                        jQuery('.claim-submission-form-section').hide();
                        success.show();
                    }else{
                        error.html(message).show();
                    }
                    jQuery('.claim-loader').css('display', 'none');
                },
                error: function(error) {
                    jQuery('.claim-loader').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('There was an error submitting the claim.');
                }
            });
        }
    });
});
