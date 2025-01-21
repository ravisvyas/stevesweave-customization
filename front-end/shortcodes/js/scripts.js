jQuery(document).ready(function($) {
    // Handle Register form submission
    $('#stevesweave-register-form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submit
        //Validate Form
        let button      =   jQuery('#submit-register-form-btn');
        let requestis   =   true;
        let success     =   jQuery('.register-verification-section');
        let error       =   jQuery('.error-msg');
        var full_name   =   jQuery('#full_name').val();
        //Full Name
        if(full_name == ''){
            requestis = false
            jQuery('#full_name').css('outline', '1px solid red');
            jQuery('.full_name_error').remove();
            jQuery('#full_name').after('<span class="full_name_error error" style="display:block; color:red;">Please fill out this field.</span>').focus();
        }else{
            jQuery('.full_name_error').remove();
            jQuery('#full_name').css('outline', 'none');
        }
        //Email Address
        var email   =   jQuery('#email').val();
        if(email == ''){
            requestis = false
            jQuery('#email').css('outline', '1px solid red');
            jQuery('.email_error').remove();
            jQuery('#email').after('<span class="email_error error" style="display:block; color:red;">Please fill out this field.</span>').focus();
        }else{
            jQuery('.email_error').remove();
            jQuery('#email').css('outline', 'none');
        }
        //Location
        var location_preference   =   jQuery('#location_preference').val();
        if(location_preference == ''){
            requestis = false
            jQuery('#location_preference').css('outline', '1px solid red');
            jQuery('.location_preference_error').remove();
            jQuery('#location_preference').after('<span class="location_preference_error error" style="display:block; color:red;">Please fill out this field.</span>').focus();
        }else{
            jQuery('.location_preference_error').remove();
            jQuery('#location_preference').css('outline', 'none');
        }

        //password
        var password   =   jQuery('#password').val();
        if(password == ''){
            requestis = false
            jQuery('#password').css('outline', '1px solid red');
            jQuery('.password_error').remove();
            jQuery('#password').after('<span class="password_error error" style="display:block; color:red;">Please fill out this field.</span>').focus();
        }else{
            jQuery('.password_error').remove();
            jQuery('#password').css('outline', 'none');
        }

        //password
        var confirm_password   =   jQuery('#confirm_password').val();
        if(confirm_password == ''){
            requestis = false
            jQuery('#confirm_password').css('outline', '1px solid red');
            jQuery('.confirm_password_error').remove();
            jQuery('#confirm_password').after('<span class="confirm_password_error error" style="display:block; color:red;">Please fill out this field.</span>').focus();
        }else{
            jQuery('.confirm_password_error').remove();
            jQuery('#confirm_password').css('outline', 'none');
        }

        //accept_terms
        var accept_terms = jQuery('#accept_terms').is(':checked');
        if(!accept_terms){
            requestis = false
            jQuery('#accept_terms').css('outline', '1px solid red').focus();
        }else{
            jQuery('#accept_terms').css('outline', 'none');
        }

        // Create new FormData object to get form data
        var formData = new FormData(this);
        // Add additional data to FormData if needed
        formData.append('action', 'stevesweave-register'); // Adding the action for the server-side handler
        if(requestis){
            jQuery('#loader-section').css('display', 'flex');
            // Send the form data using AJAX
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
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
                        jQuery('.register-section').hide();
                        success.show();
                    }else{
                        error.html(message).show();
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('There was an error submitting the Sign up form.');
                }
            });
        }
    });
    //Handle Verification Form Submission
    $('#verification-form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submit
        //Validate Form
        let button      =   jQuery('#submit-register-form-btn');
        let requestis   =   true;
        let success     =   jQuery('.success-msg');
        let error       =   jQuery('.error-msg');
        //code1
        var code1   =   jQuery('#code1').val();
        if(code1 == ''){
            requestis = false
            jQuery('#code1').css('outline', '1px solid red');
        }else{
            jQuery('#code1').css('outline', 'none');
        }
        //code2
        var code2   =   jQuery('#code2').val();
        if(code2 == ''){
            requestis = false
            jQuery('#code2').css('outline', '1px solid red');
        }else{
            jQuery('#code2').css('outline', 'none');
        }
        //code3
        var code3   =   jQuery('#code3').val();
        if(code3 == ''){
            requestis = false
            jQuery('#code3').css('outline', '1px solid red');
        }else{
            jQuery('#code3').css('outline', 'none');
        }
        //code4
        var code4   =   jQuery('#code4').val();
        if(code4 == ''){
            requestis = false
            jQuery('#code4').css('outline', '1px solid red');
        }else{
            jQuery('#code4').css('outline', 'none');
        }
        //code5
        var code5   =   jQuery('#code5').val();
        if(code5 == ''){
            requestis = false
            jQuery('#code5').css('outline', '1px solid red');
        }else{
            jQuery('#code5').css('outline', 'none');
        }
        //code6
        var code6   =   jQuery('#code6').val();
        if(code6 == ''){
            requestis = false
            jQuery('#code6').css('outline', '1px solid red');
        }else{
            jQuery('#code6').css('outline', 'none');
        }
        // Create new FormData object to get form data
        var formData = new FormData(this);
        // Add additional data to FormData if needed
        formData.append('action', 'stevesweave-register-verification'); // Adding the action for the server-side handler
        formData.append('email', jQuery('#email').val());
        if(requestis){
            jQuery('#loader-section').css('display', 'flex');
            // Send the form data using AJAX
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
                type: 'POST',                  
                data: formData,                
                contentType: false,            
                processData: false,          
                success: function(response){
                    let responseData    =   JSON.parse(response);
                    var status          =   responseData.status;
                    var message         =   responseData.message;
                    var redirect_url         =   responseData.redirect_url;
                    if(status){
                        error.html('').hide();
                        success.html(message).show();
                        if(redirect_url){
                            setTimeout(() => {
                                window.location.href = redirect_url;
                            }, 1000);
                        }
                    }else{
                        error.html(message).show();
                        success.html('').hide();
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('There was an error submitting the Verification form.');
                }
            });
        }
    });

    //Password strength..
    function evaluateStrength(password) {
        let strength = 0;

        if (password.length >= 6) strength++; // Minimum length
        if (/[A-Z]/.test(password)) strength++; // Uppercase letter
        if (/[0-9]/.test(password)) strength++; // Numeric digit
        if (/[\W_]/.test(password)) strength++; // Special character

        return strength;
    }

    function updateStrengthIndicator(strength) {
        const strengthSpans = $('.stevesweave-password-strength span');
        strengthSpans.removeClass('active weak average strong very-strong');

        for (let i = 0; i < strength; i++) {
            if (strength === 1) strengthSpans.eq(i).addClass('active weak');
            else if (strength === 2) strengthSpans.eq(i).addClass('active average');
            else if (strength === 3) strengthSpans.eq(i).addClass('active strong');
            else if (strength === 4) strengthSpans.eq(i).addClass('active very-strong');
        }
    }

    function checkPasswordMatch(password, confirmPassword) {
        if (confirmPassword && password !== confirmPassword) {
            $('.password-error').text('Passwords do not match').show();
        } else {
            $('.password-error').hide();
        }
    }

    $('#password').on('input', function () {
        const password = $(this).val();
        const strength = evaluateStrength(password);

        updateStrengthIndicator(strength);

        // Re-check match in case of change in password
        checkPasswordMatch(password, $('#confirm_password').val());
    });

    $('#confirm_password').on('input', function () {
        const confirmPassword = $(this).val();
        const password = $('#password').val();

        checkPasswordMatch(password, confirmPassword);
    });

    //Handle Login Form Submission
    $('#stevesweave-login-form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submit
        //Validate Form
        let button      =   jQuery('#submit-login-form-btn');
        let requestis   =   true;
        let success     =   jQuery('.success-msg');
        let error       =   jQuery('.error-msg');
        //email_address
        var email_address   =   jQuery('#email_address').val();
        if(email_address == ''){
            requestis = false
            jQuery('#email_address').css('outline', '1px solid red');
        }else{
            jQuery('#email_address').css('outline', 'none');
        }
        //l_password
        var l_password   =   jQuery('#l_password').val();
        if(l_password == ''){
            requestis = false
            jQuery('#l_password').css('outline', '1px solid red');
        }else{
            jQuery('#l_password').css('outline', 'none');
        }
        // Create new FormData object to get form data
        var formData = new FormData(this);
        // Add additional data to FormData if needed
        formData.append('action', 'stevesweave-login'); // Adding the action for the server-side handler
        if(requestis){
            jQuery('#loader-section').css('display', 'flex');
            // Send the form data using AJAX
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
                type: 'POST',                  
                data: formData,                
                contentType: false,            
                processData: false,          
                success: function(response){
                    let responseData    =   JSON.parse(response);
                    var status          =   responseData.status;
                    var message         =   responseData.message;
                    var redirect_url    =   responseData.redirect_url;
                    if(status){
                        error.html('').hide();
                        success.html(message).show();
                        if(redirect_url){
                            setTimeout(() => {
                                window.location.href = redirect_url;
                            }, 1000);
                        }
                    }else{
                        error.html(message).show();
                        success.html('').hide();
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('There was an error submitting the Verification form.');
                }
            });
        }
    });

    //Handle Forgot Password Submission
    $('#forgot-password-form').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submit
        //Validate Form
        let button      =   jQuery('#forgot-password-response');
        let requestis   =   true;
        let success     =   jQuery('.success-msg');
        let error       =   jQuery('.error-msg');
        //user_email
        var user_email   =   jQuery('#user_email').val();
        if(user_email == ''){
            requestis = false
            jQuery('#user_email').css('outline', '1px solid red');
        }else{
            jQuery('#user_email').css('outline', 'none');
        }
        // Create new FormData object to get form data
        var formData = new FormData(this);
        // Add additional data to FormData if needed
        formData.append('action', 'stevesweave-forgot-password'); // Adding the action for the server-side handler
        if(requestis){
            jQuery('#loader-section').css('display', 'flex');
            // Send the form data using AJAX
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
                type: 'POST',                  
                data: formData,                
                contentType: false,            
                processData: false,          
                success: function(response){
                    let responseData    =   JSON.parse(response);
                    var status          =   responseData.status;
                    var message         =   responseData.message;
                    // var redirect_url    =   responseData.redirect_url;
                    if(status){
                        error.html('').hide();
                        success.html(message).show();
                        // if(redirect_url){
                        //     setTimeout(() => {
                        //         window.location.href = redirect_url;
                        //     }, 1000);
                        // }
                    }else{
                        error.html(message).show();
                        success.html('').hide();
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('There was an error submitting the Verification form.');
                }
            });
        }
    });
});
jQuery(document).ready(function($) {
    // Inline Editing
    $('.stevesweave-edit-btn').on('click', function(e) {
        e.preventDefault();
        const field = $(this).data('field');
        const save = $(this).data('save');
        
        if (save === false) {
            // Switch to input mode
            $(`#stevesweave-user-${field}`).hide();
            $(`#stevesweave-input-${field}`).show();
            $(this).text('Save').data('save', true);
        } else {
            // Save the input value via AJAX
            const value = $(`#stevesweave-input-${field}`).val();
            const data = {
                action: 'stevesweave_update_account_field',
                field: field,
                value: value
            };
            jQuery('#loader-section').css('display', 'flex');
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
                type: 'POST',                  
                data: data,          
                success: function(response){
                    if (response.success) {
                        $(`#stevesweave-user-${field}`).text(value).show();
                        $(`#stevesweave-input-${field}`).hide();
                        $(`button[data-field=${field}]`).text('Edit').data('save', false);
                        // alert('Updated successfully!');
                    } else {
                        alert('Failed to update. Please try again.');
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('Failed to update. Please try again.');
                }
            });
        }
    });

    // Delete Account
    $('#stevesweave-delete-account').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete your account? This action is permanent.')) {
            jQuery('#loader-section').css('display', 'flex');
            $.ajax({
                url: ShortcodesScriptData.ajax_url, 
                type: 'POST',                  
                data: {
                    action: 'stevesweave_delete_user_account',
                },          
                success: function(response){
                    let responseData    =   JSON.parse(response);
                    var status          =   responseData.status;
                    var message         =   responseData.message;
                    if (status) {
                        alert('Account deleted successfully.');
                        window.location.href = responseData.url;
                    } else {
                        alert('Failed to delete account. Please try again.');
                    }
                    jQuery('#loader-section').css('display', 'none');
                },
                error: function(error) {
                    jQuery('#loader-section').css('display', 'none');
                    console.log('Error:', error); // Handle error
                    alert('Failed to delete account. Please try again.');
                }
            });
        }
    });

    //Update Password
    // Show Password Modal
    $('#stevesweave-update-password').on('click', function (e) {
        e.preventDefault();
        $('#stevesweave-password-modal').fadeIn();
    });

    // Close Modal
    $('.stevesweave-close').on('click', function () {
        $('.stevesweave-modal').fadeOut();
    });

    // Update Password via AJAX
    $('#stevesweave-password-form').on('submit', function (e) {
        e.preventDefault();

        const currentPassword   = $('#stevesweave-current-password').val();
        const newPassword       = $('#stevesweave-new-password').val();
        const confirmPassword   = $('#stevesweave-confirm-password').val();

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match!');
            return;
        }
        jQuery('#update-password-popup-btn').html('Please wait...');
        $.ajax({
            url: ShortcodesScriptData.ajax_url, 
            type: 'POST',                  
            data: {
                action: 'stevesweave_update_password',
                current_password: currentPassword,
                new_password: newPassword,
            },          
            success: function(response){
                let responseData    =   JSON.parse(response);
                var status          =   responseData.status;
                var message         =   responseData.message;
                if (status) {
                    alert(message);
                    $('.stevesweave-modal').fadeOut();
                    location.reload();
                } else {
                    alert(message);
                }
                jQuery('#update-password-popup-btn').html('Update Password');
            },
            error: function(error) {
                console.log('Error:', error); // Handle error
                alert('Failed to update password. Please try again.');
            }
        });
    });   
});