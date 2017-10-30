require("blueimp-file-upload/js/vendor/jquery.ui.widget.js");
require("blueimp-file-upload/js/jquery.iframe-transport.js");
require("blueimp-file-upload/js/jquery.fileupload.js");
const Spinner = require("./spin.min");
const Utilities = require("./cc.utilities.js").default;
const User = require("./cc.user.js").default;

const Profile = (function(){

    var postUrl = '/user/profile/save';
    var button = null;
    var privateButton = null;
    var cords = {
        x: 0,
        y: 0,
        w: 100,
        h: 100
    }
    var cropProfilePicSettings = {
        imgDiv: "profile-pic-crop",
        modal: '#crop-photo-modal',
        spinner:'searching_spinner_center',
        spinnerWrapper:'#spinner-wrapper'

    }

    var jcropApi = null;


    function readURL(input) {
        var $prev = $(input).parent().find('img');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $prev.attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);

        } else {
            $prev.attr('src', '/bundles/classcentralsite/slashpixel/images/profile-pic-placeholder.png');
        }
    }

    $('#edit-profile-photo').on('change',function(){
        readURL(this);
    });



    // Function to add a class for styling purposes to select list
    function colorizeSelect(){
        if($(this).val() == "") $(this).addClass("empty");
        else $(this).removeClass("empty")
    }

    $(".js-colorize-select")
        .on('change keyup', colorizeSelect)
        .change();




    function getFormFields() {
        var aboutMe =  $('textarea[name=about-me]').val();
        var name    =  $('input:text[name=full-name]').val();
        var location = $('input:text[name=location]').val();
        var fieldOfStudy = $('input:text[name=field-of-study]').val();
        var jobTitle = $('input:text[name=job-title]').val();
        var highestDegree = $('select[name=highest-degree]').val();
        var privacy = !$('input[name=privacy]').is(":checked");

        // Social
        var twitter =  getFormFieldValue ($('input:text[name=profile-twitter]').val());
        var coursera = getFormFieldValue ($('input:text[name=profile-coursera]').val());
        var linkedin= getFormFieldValue ($('input:text[name=profile-linkedin]').val());
        var website = getFormFieldValue ($('input:text[name=profile-website]').val());
        var facebook = getFormFieldValue ($('input:text[name=profile-facebook]').val());
        var gplus = getFormFieldValue ($('input:text[name=profile-gplus]').val());

        return {
            aboutMe: aboutMe,
            name: name,
            location: location,
            fieldOfStudy:fieldOfStudy,
            highestDegree:highestDegree,
            jobTitle: jobTitle,
            privacy: privacy,
            twitter: twitter,
            coursera:coursera,
            linkedin: linkedin,
            website: website,
            gplus: gplus,
            facebook: facebook
        };
    }

    function getFormFieldValue (data) {
        if( typeof  data === "undefined" ) {
            return '';
        }
        return data;
    }

    /**
     * Calculates the percentage for the completeness bar
     * @returns {number}
     */
    function profileCompletenessPercentage(){
        var listOfFields = [ 'aboutMe', 'name', 'location', 'fieldOfStudy',  'jobTitle', 'highestDegree'];
        var formFields = getFormFields();
        var emptyFields = 0;
        for(var i = 0; i < listOfFields.length; i++) {
            var field = listOfFields[i];
            if(!Utilities.isEmpty( formFields[field] )) {
                emptyFields++;
            }
        }

        return Number( emptyFields*100/ listOfFields.length);

    }

    /**
     *
     * @param button id of the save profile button
     */
    function init( btn_id, profile_image_upload_btn_id, btn_crop ) {
        // Attach event handler
        button = $(btn_id);
        button.click( handler );

        $(profile_image_upload_btn_id).fileupload({
            maxFileSize: 1000000
        });

        // Bind fileupload plugin callbacks
        $(profile_image_upload_btn_id)
            .bind('fileuploadstart', function(){
                // Check if the user is logged in
                User.isLoggedIn(true); // Redirects the user to login if not logged in

                showSpinner(); // Show loading
                $('#crop-photo-modal .modal-title').text("Crop Photo");
                $(cropProfilePicSettings.modal).modal('show');

            })
            .bind('fileuploaddone', postStep1)
            .bind('fileuploadfail', function (e, data) {
                // File upload failed. Show an error message
                Utilities.notify(
                    "Error",
                    "Error uploading file. Max file size is 1mb",
                    "error"
                );
            });

        // Crop button
        $(btn_crop).click(cropButtonHandler);

        $(cropProfilePicSettings.modal).on('hidden.bs.modal', clearImage);

        // Attach event handler to the tab clicks to upate the url via push state
        $('#profile-tabs li').click( profileTabClickHandler);
    }

    function showCoords(c) {
        // variables can be accessed here as
        // c.x, c.y, c.x2, c.y2, c.w, c.h
        cords.x = c.x;
        cords.y = c.y;
        cords.w = c.w;
        cords.h = c.h;

    };

    /**
     * Update the url when the tabs are clicked
     * @param event
     */
    function profileTabClickHandler( event ) {
        var tabName = $(this).data('tab');
        var url = window.location.href.toString().split(window.location.host)[1];

        // Retrieve the base url
        var baseUrl = null;

        // Scenario 1 - url is of the for
        if(url.search('/u/') == 0 && url.match(/\//g).length > 2) {
            baseUrl =  url.substring(0, url.lastIndexOf('/') );
        } else if (url.search('/@') == 0 && url.match(/\//g).length > 1) {
            baseUrl =  url.substring(0, url.lastIndexOf('/') );
        } else {
            baseUrl = url;
        }

        // Build the path
        var path = null;
        if(tabName == 'transcript') {
            path = baseUrl;
        } else {
            path = baseUrl + '/' + tabName;
        }
        history.replaceState( null, null, path);
    }

    function showSpinner() {
        var opts = {
            lines: 13, // The number of lines to draw
            length: 20, // The length of each line
            width: 10, // The line thickness
            radius: 30, // The radius of the inner circle
            corners: 1, // Corner roundness (0..1)
            rotate: 0, // The rotation offset
            direction: 1, // 1: clockwise, -1: counterclockwise
            color: '#000', // #rgb or #rrggbb or array of colors
            speed: 1, // Rounds per second
            trail: 60, // Afterglow percentage
            shadow: false, // Whether to render a shadow
            hwaccel: false, // Whether to use hardware acceleration
            className: 'spinner', // The CSS class to assign to the spinner
            zIndex: 2e9, // The z-index (defaults to 2000000000)
            top: 'auto', // Top position relative to parent in px
            left:'auto' // Left position relative to parent in px
        };
        var target = document.getElementById(cropProfilePicSettings.spinner);
        var spinner = new Spinner(opts).spin(target);
        $(target).data('spinner', spinner);
        $(cropProfilePicSettings.spinnerWrapper).show();
    }

    function hideSpinner() {
        $('#'+ cropProfilePicSettings.spinner).data('spinner').stop();
        $(cropProfilePicSettings.spinnerWrapper).hide();
    }


    /**
     * This function is called after the step 1 of profile image
     * upload is executed on the backend
     */
    function postStep1(e,data ){
        var result = JSON.parse(data.result);

        if(!result.success){
            Utilities.notify(
                "Profile photo upload error",
                result.message,
                "error"
            )
        } else {

            ga('send','event', 'Profile Photo Upload',"Step 1 Completed");
            // Image uploaded. Load the crop plugin
            var imgUrl = result.message.imgUrl;
            $("<img src='" + imgUrl+"' id='" + cropProfilePicSettings.imgDiv + "'/>").load(function() {
                // Hide the spinner
                hideSpinner();

                // Show the image
                $(this).appendTo(cropProfilePicSettings.modal + " .modal-body");
                $('#'+cropProfilePicSettings.imgDiv).Jcrop({
                        minSize:      [200,200],
                        maxSize:      [800,800],
                        bgColor:      'black',
                        boxWidth:     400,
                        bgOpacity:   .4,
                        aspectRatio: 1,
                        setSelect:   [0,0,400,400],
                        onSelect:    showCoords,
                        onChange:    showCoords
                    },function(){
                        jcropApi = this;
                    });
                });
        }
    }

    /**
     * Handles the click event for crop button
     */
    function cropButtonHandler(){
        // Check if the user is logged in
        User.isLoggedIn(true); // Redirects the user to login if not logged in

        // Remove the photo
        clearImage();

        // Update the modal title
        $('#crop-photo-modal .modal-title').text("Cropping...");
        // Show the spinner
        showSpinner();

        // Post the co-ordinates  to the server
        $.ajax({
            type:"post",
            url: "/user/profile/image/step2",
            data: JSON.stringify(cords)
        }).done(function(result){
            result = JSON.parse(result);
            if( result['success'] ){
                ga('send','event', 'Profile Photo Upload',"Step 2 Completed");
                // Refresh the page
                location.reload(true);
            } else {
                // Show an error message
                Utilities.notifyWithDelay(
                    'Error Cropping photo',
                    'Some error occurred, please try again later',
                    'error',
                    60
                );
                hideSpinner();
            }
        });
    }

    /**
     * Its clears the image in the modal
     * the jcrop plugin
     *
     */
    function clearImage(){
        jcropApi.destroy();
        // Remove the image from the dom
        $('#'+ cropProfilePicSettings.imgDiv).remove();
    }

    /**
     * Validates the profile form fields and shows
     * the respective error messages
     * @param profile
     * @returns {boolean}
     */
    function validate( profile ){
        var validationError = false;
        // Name cannot be empty and should be
        // atleast 3 letters long
        if(Utilities.isEmpty(profile.name) || profile.name.length < 3 ) {
            validationError = true;
            $('#full-name-error').show();
        } else {
            $('#full-name-error').hide();
        }
        return validationError;
    }

    /**
     * handler which is called when save profile button is clicked
     * @param event
     */
    function handler(event) {
        event.preventDefault();
        // Disable the save profile button
        button.attr('disabled',true);
        if(validateAndSaveProfile()) {
            button.attr('disabled',false);
        }

    }

    function validateAndSaveProfile() {
        var profile = getFormFields();
        var validationError = validate(profile);

        if(!validationError) {
            // Ajax post to save the profile
            save(profile);
        } else {
            Utilities.notify(
                "Profile Validation Error",
                "Please make sure to enter only valid values in the form",
                "error"
            );
            return false;
        }

        return true;
    }


    /**
     * Function to save the validated profile
     * @param profile
     */
    function save(profile) {
        $.ajax({
            type:"post",
            url: postUrl,
            data: JSON.stringify(profile)
        })
            .done(
                function(result) {
                    result = JSON.parse(result);
                    if( result['success'] ){
                        // Refresh the page
                        location.reload(true);
                    } else {
                        // Show an error message
                        Utilities.notifyWithDelay(
                            'Error saving profile',
                            'Some error occurred, please try again later',
                            'error',
                            60
                        );
                        button.attr('disabled',false);
                    }
                }
            );
    }

    // =====================================================
    //      Edit Profile - Private form
    // ======================================================


    /**
     * Initialize private info
     * @param privateFormSubmit id of the form button
     */
    function initPrivateForm( privateFormSubmit ) {
        $(privateFormSubmit).click( savePrivateForm );
        privateButton = $(privateFormSubmit);

        // Delete profile button
        $('#delete-profile-link').click( deleteProfile );
    }

    function getPrivateDataFormValues() {
        var currentEmail = $('input[name=edit-profile-email]').data('current-email') || '';
        var email = $('input[name=edit-profile-email]').val() || '';
        var curPassword = $('input:password[name=edit-profile-current-password]').val() || '';
        var newPassword = $('input:password[name=edit-profile-new-password]').val() || '';
        var confirmPassword = $('input:password[name=edit-profile-confirm-password]').val() || '';

        return {
            currentEmail: currentEmail.trim(),
            email: email.trim(),
            currentPassword: curPassword.trim(),
            newPassword: newPassword.trim(),
            confirmPassword: confirmPassword.trim()
        }
    }

    function showPrivateFormError(msg) {
        $('#private-form-error').html( msg );
        $('#private-form-error').removeClass('hide');
    }

    function hidePrivateFormError() {
        $('#private-form-error').addClass('hide');
    }

    function savePrivateForm( event ){
        event.preventDefault();

        // Disable the update my private info button
        var savePrivateButton = $('#save-profile-private');
        savePrivateButton.attr('disabled',true);

        var pInfo = getPrivateDataFormValues();

        // Check if it is an email change
        var isEmailChange = ( pInfo.currentEmail != pInfo.email ) ;

        // Check if password is being changed
        var isPasswordChange = (pInfo.newPassword != null && pInfo.newPassword.trim() != '');
        hidePrivateFormError(); // Hide the error
        if(isEmailChange || isPasswordChange) {
            if(isPasswordChange) {
                // Check if the new and old passwords are equal
                if( pInfo.newPassword != pInfo.confirmPassword ) {
                    // Show an error message
                    showPrivateFormError("New Password and Verify Password do not match");
                    privateButton.attr('disabled',false); // Enable the update private info button
                } else {
                    // Call the api to change password
                    updatePassword( pInfo );
                }
            } else {
                // Call the api to update email address
                updateEmail( pInfo );
            }

        } else {
            // Nothing is being changed
            showPrivateFormError("Nothing to update");
            privateButton.attr('disabled',false); // Enable the update private info button
        }
    }

    function updatePassword( pInfo ){
        $.ajax({
            type:"post",
            url: "/user/profile/updatePassword",
            data: JSON.stringify(pInfo)
        }).done(function(result){
            result = JSON.parse(result);
            if( result['success'] ){
                // Refresh the page
                location.reload(true);
            } else {
                showPrivateFormError( result['message'] );
            }
            privateButton.attr('disabled',false); // Enable the update private info button
        });
    }

    function updateEmail(pInfo){
        $.ajax({
            type: "post",
            url: "/user/profile/updateEmail",
            data: JSON.stringify(pInfo)
        }).done( function(result){
            result = JSON.parse( result );
            if( result['success'] ){
                // Refersh the page
                location.reload(true);
            } else {
                showPrivateFormError( result.message );
            }
            privateButton.attr('disabled',false); // Enable the update private info button
        });
    }

    function deleteProfile( event ) {
        event.preventDefault();
        var pInfo = getPrivateDataFormValues();

        // Ask for confirmation
        var check = confirm("Are you sure you want to delete your account? This cannot be undone");
        if(check == true) {
            User.isLoggedIn(true); // Redirects if the user is not logged in
            $.ajax({
                type: "post",
                url: "/user/profile/delete",
                data: JSON.stringify(pInfo)
            }).done( function(result){
                result = JSON.parse( result );
                if( result['success'] ){
                    // Refresh the page
                    location.reload(true);
                } else {
                    showPrivateFormError( result.message );
                }
            });
        }
    }

    init('#save-profile','#profile-photo-upload','#btn-crop');
    initPrivateForm( '#save-profile-private' );

    return {
        init: init,
        initPrivateForm: initPrivateForm,
        profileCompletenessPercentage: profileCompletenessPercentage,
        validateAndSaveProfile: validateAndSaveProfile
    };
})();

export default Profile;
