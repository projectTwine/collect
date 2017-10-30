const Utilities = require("./cc.utilities.js").default;
const Profile = require("./cc.profile.js").default;

const Signup = (function(){
    var promptShownCookie = 'signup_prompt'; // is set when a user is shown signup form for the first time

    function init() {
        $( document).ready(function () {
            $('form[name="classcentral_sitebundle_signuptype"]').submit( signupFormSubmit);
        });
    }

    function signupFormSubmit(e) {
        e.preventDefault();
        // Disable the button
        $('#classcentral_sitebundle_signuptype_save').attr('disabled',true);
        $('#classcentral_sitebundle_signuptype_save').html("Creating account...");
        var form = $(this);
        if (isFormValid( $(this), getSignupFormValues($(this))) ) {
            // Submit the form using post
            var actionurl = e.currentTarget.action;
            $.ajax({
                url: actionurl,
                type: 'post',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(result) {
                  if(result.success) {
                      var userSignedUpSrc = $(form).find('.signup-modal-src').text();
                      ga('send','event','New User Created', userSignedUpSrc);
                      // Signup successful. Hide the modal
                      form.parent().parent().parent().parent().parent().parent().modal("hide");
                      // Show the form update
                      if($('#meet-your-next-course-page').length > 0) {
                          // Skip the first two steps and move to the profile step
                          showOnboardingProfileStep();
                      } else {
                          showOnboardingFollowSubjectStep();
                      }

                  } else {
                    // Signup failed
                      showErrorMessage( form,result.message);
                      $('#classcentral_sitebundle_signuptype_save').attr('disabled',false);
                      $('#classcentral_sitebundle_signuptype_save').html("Sign Up");
                  }
                }
            });
        } else {
            // Error message is shown by the validate function. Do nothing
        }

    }

    function showOnboardingProfileStep()
    {
        var profileStepUrl = '/user/onboarding/profile';
        ga('send','event','Onboarding Nav', 'Profile','Shown');
        $.ajax({
            url: profileStepUrl,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");
                $("#onboarding-profile-modal").modal("show");
                updateProfileProgress();
                $('#onboarding-profile-modal__save').click( function(){
                    ga('send','event','Onboarding Nav', 'Profile','Update');
                    Profile.validateAndSaveProfile();
                });
                // update the progress of the profile fields when form fields are updated
                $('#onboarding-profile-modal form :input').each( function(){
                    $(this).focusout(  updateProfileProgress );
                });
                $('#onboarding-profile-modal form select').change(  updateProfileProgress );

                $('#onboarding-profile-modal__back').click(function(){
                    ga('send','event','Onboarding Nav', 'Profile','Back');
                    onboardingProfileUpdateBackStepButton();
                });

                // Reload the page if someone says skip profile
                $('#onboarding-profile-modal__skip').click( function(){
                    ga('send','event','Onboarding Nav', 'Profile','Skip');
                    location.reload();
                });

            },
            async: false
        })
    }

    function onboardingProfileUpdateBackStepButton() {
        $("#onboarding-profile-modal").modal("hide") // hide the modal
        $("#onboarding-profile-modal").remove();
        showOnboardingFollowCoursesStep(); // show the previous modal
    }

    function updateProfileProgress() {
        updateOnbardingFooterProgressBar( Profile.profileCompletenessPercentage() )
    }

    function updateOnbardingFooterProgressBar( percentage ) {
        $('.meter__bar').width( percentage + '%');
        if( percentage == 100 ) {
            $('#onboarding-profile-modal__save').addClass('active');
        } else {
            $('#onboarding-profile-modal__save').removeClass('active');
        }
    }

    function isFormValid ( form, formValues ) {

        hideErrorMesssage( form );
        // Front end check
        if( Utilities.isEmpty (formValues.email) || !Utilities.validateEmail(formValues.email) ){
            // email is invalid
            showErrorMessage(form,"Invalid Email");
            return false;
        }

        if( Utilities.isEmpty (formValues.name) ) {
            // name cannot be empty
            showErrorMessage(form,"Name is required");
            return false;
        }

        if( Utilities.isEmpty (formValues.password) ) {
            // password cannot be empty
            showErrorMessage(form,"Password cannot be empty");
            return false;
        }

        return true;
    }

    function showErrorMessage(form, message) {
        $(form).find('.cc-signup-form-error-message').html(message);
    }

    function hideErrorMesssage(form) {
        $(form).find('.cc-signup-form-error-message').html("");
    }

    function getSignupFormValues( form ){
        var email = $(form).find("#classcentral_sitebundle_signuptype_email"  ).val();
        var name  = $(form).find("#classcentral_sitebundle_signuptype_name"  ).val();
        var password = $(form).find("#classcentral_sitebundle_signuptype_password"  ).val();

        return {
            name : name,
            email : email,
            password: password
        }
    }

    function showOnboardingFollowSubjectStep()
    {
        window.CC.Class.Follow.setPersonalizationPromptShown();

        var url = '/user/onboarding/follow-subjects';
        // Set the cookie - so that the user is not shown the popup again in the same session
        Cookies.set( 'follow_personalized_page_prompt', 1, { expires :30} );
        ga('send','event','Onboarding Nav', 'Follow Subjects','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");

                updateFollowSubjectsModalFooter(); // Update follow button text
                $("#onboarding-follow-subjects-modal").modal("show");

                $("#onboarding-follow-subjects-modal").find('.tagboard__tag').bind("followingChanged",  updateFollowSubjectsModalFooter);

                // Init and attach event handlers to the follow buttons
                window.CC.Class.Follow.init();


                // Hookup next and skip buttons
                $('#onboarding-follow-subjects__next').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Subjects','Next');
                    onboardingFollowSubjectNextStepButton()
                });
                $('#onboarding-follow-subjects__skip').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Subjects','Skip');
                    onboardingFollowSubjectNextStepButton();
                });

                $('[data-toggle="tooltip"]').tooltip(); // load the tooltips
            },
            async: false
        })
    }

    /**
     * When the next/skip button is clicked on the follow subject onboarding button
     */
    function onboardingFollowSubjectNextStepButton() {
        $("#onboarding-follow-subjects-modal").modal("hide"); // hide the modal
        $("#onboarding-follow-subjects-modal").remove();
        showOnboardingFollowInstitutionsStep(); // show the next step
    }

    // Update the footer to show correct percentage and proper messages on the next button
    function updateFollowSubjectsModalFooter(){
        var nextButton = $('#onboarding-follow-subjects__next');
        var numFollows = $("#onboarding-follow-subjects-modal").find('.tagboard__tag.active').length;

        var percentage = numFollows*100/5;
        $("#onboarding-follow-subjects-modal .meter__bar").width( percentage + '%');

        if(numFollows >= 5) {
            $(nextButton).addClass('active');
            $(nextButton).find("span").text('Pick more subjects or move on to Step 2 (of 4)');
        } else {
            var followsLeft = 5 - numFollows;
            $(nextButton).removeClass('active');
            if( followsLeft == 1) {
                $(nextButton).find("span").text('One more to go...');
            } else {
                $(nextButton).find("span").text('Pick ' + followsLeft + ' more subjects to unlock recommendations');
            }
        }
    }

    function showOnboardingFollowInstitutionsStep()
    {
        var url = '/user/onboarding/follow-institutions';
        ga('send','event','Onboarding Nav', 'Follow Institutions','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");

                updateFollowInstitutionsModalFooter();
                $("#onboarding-follow-institutions-modal").modal("show");
                $("#onboarding-follow-institutions-modal").find('.tagboard__tag').bind("followingChanged",  updateFollowInstitutionsModalFooter);

                // Init and attach event handlers to the follow buttons
                window.CC.Class.Follow.init();

                $('#onboarding-follow-institutions__next').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Institutions','Next');
                    onboardingFollowInstitutionsNextStepButton();
                });

                $('#onboarding-follow-institutions__skip').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Institutions','Skip');
                    onboardingFollowInstitutionsNextStepButton();
                });

                $('#onboarding-follow-institutions__back').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Institutions','Back');
                    onboardingFollowInstitutionsBackStepButton();
                });

                $('[data-toggle="tooltip"]').tooltip(); // load the tooltips
            },
            async: false
        })
    }

    /**
     * When the next/skip button is clicked on the follow institution onboarding button
     */
    function onboardingFollowInstitutionsNextStepButton() {
        $("#onboarding-follow-institutions-modal").modal("hide"); // hide the modal
        $("#onboarding-follow-institutions-modal").remove();
        showOnboardingFollowCoursesStep(); // show the next step
    }

    function onboardingFollowInstitutionsBackStepButton() {
        $("#onboarding-follow-institutions-modal").modal("hide"); // hide the modal
        $("#onboarding-follow-institutions-modal").remove();
        showOnboardingFollowSubjectStep(); // show the next step
    }

    // Update the footer to show correct percentage and proper messages on the next button
    function updateFollowInstitutionsModalFooter(){
        var nextButton = $('#onboarding-follow-institutions__next');
        var numFollows = $("#onboarding-follow-institutions-modal").find('.tagboard__tag.active').length;

        var percentage = numFollows*100/5;
        $("#onboarding-follow-institutions-modal .meter__bar").width( percentage + '%');

        if(numFollows >= 5) {
            $(nextButton).addClass('active');
            $(nextButton).find("span").text('Pick more providers or move on to the next step');
        } else {
            var followsLeft = 5 - numFollows;
            $(nextButton).removeClass('active');
            if( followsLeft == 1) {
                $(nextButton).find("span").text('One more to go...');
            } else {
                $(nextButton).find("span").text('Pick ' + followsLeft + ' more providers to unlock recommendations');
            }
        }
    }

    function showOnboardingFollowCoursesStep()
    {
        var url = '/user/onboarding/follow-courses';
        ga('send','event','Onboarding Nav', 'Follow Courses','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");

                // updateFollowInstitutionsModalFooter();
                $("#onboarding-follow-courses-modal").modal("show");
                //$("#onboarding-follow-courses-modal").find('.tagboard__tag').bind("followingChanged",  updateFollowInstitutionsModalFooter);

                // Init and attach event handlers to the follow buttons
                window.CC.Class.Follow.init();

                $('#onboarding-follow-courses__next').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Courses','Next');
                    onboardingFollowCoursesNextStepButton();
                });

                $('#onboarding-follow-courses__skip').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Courses','Skip');
                    onboardingFollowCoursesNextStepButton();
                });

                $('#onboarding-follow-courses__back').click(function(){
                    ga('send','event','Onboarding Nav', 'Follow Courses','Back');
                    onboardingFollowCoursesBackStepButton();
                });

                $('[data-toggle="tooltip"]').tooltip(); // load the tooltips
            },
            async: false
        })
    }

    /**
     * When the next/skip button is clicked on the follow courses onboarding modal
     */
    function onboardingFollowCoursesNextStepButton() {
        $("#onboarding-follow-courses-modal").modal("hide"); // hide the modal
        $("#onboarding-follow-courses-modal").remove();
        showOnboardingProfileStep(); // show the next step
    }

    function onboardingFollowCoursesBackStepButton() {
        $("#onboarding-follow-courses-modal").modal("hide"); // hide the modal
        $("#onboarding-follow-courses-modal").remove();
        showOnboardingFollowInstitutionsStep(); // show the next step
    }

    function showSignupPrompt(delay){
      const self = this;

        if ( !isMobile.phone && Cookies.get( promptShownCookie) === undefined ) {
            $.ajax({
                url: "/ajax/isLoggedIn",
                cache: true
            })
                .done(function(result){
                    var loggedInResult = $.parseJSON(result);
                    if( !loggedInResult.loggedIn) {

                        // Show the signup form
                        setTimeout(function() {
                            // Check the cookie again
                            if(Cookies.get( promptShownCookie) === undefined ) {
                                self.showSignupModal("ask_for_signup");
                                Utilities.hideWidgets();
                            }

                        },delay);
                    }
                }
            );
        }
    }

    function showSignupModal(src) {
      const self = this;
        Cookies.set( promptShownCookie, 1, { expires :30} ); // Users are not shown the signup prompt again

        $.ajax({
            url: "/ajax/isLoggedIn",
            cache: true
        })
            .done(function(result){
                    var loggedInResult = $.parseJSON(result);
                    if( !loggedInResult.loggedIn) {
                      self.requestSignupModal(src);
                    }
                }
            );
    }

    function requestSignupModal(src, params) {
      // Show the signup form
      var url = '/ajax/signup/' + src;
      $.ajax({
          url: url,
          type: "POST",
          data: params || {},
          dataType: "json",
          cache: false,
          success: function( result ) {
              $(result.modal).appendTo("body");

              // Setup the modal
              var signupFormId = "#signupModal-" + src;
              // mini slider functionality
              $( signupFormId + " .js-mini-slider" ).each(function( index, element ) {
                  $(element).flexslider({
                      selector: " .js-mini-slider-slides .js-mini-slider-slide",
                      slideshow: true,
                      slideshowSpeed: 4000,
                      directionNav: false,
                      manualControls: signupFormId +  " .js-mini-slider-controls .js-mini-slider-control"
                  });

                  var $slide = $(element).find(" .js-mini-slider-slide");

                  var numberOfSlides = $slide.length;

                  $slide.on("click", function(e) {
                      $(element).flexslider("next");
                  });
              });

              $('form[name="classcentral_sitebundle_signuptype"]').submit( signupFormSubmit);

              $(signupFormId).modal("show");
          }
      })
    }

    init();

    return {
        init: init,
        'profileOnboarding' : showOnboardingProfileStep,
        'followSubjectOnboarding' : showOnboardingFollowSubjectStep,
        'followInstitutionOnboarding' : showOnboardingFollowInstitutionsStep,
        'followCourseOnboarding':showOnboardingFollowCoursesStep,
        'showSignupPrompt' : showSignupPrompt,
        'showSignupModal' : showSignupModal,
        'requestSignupModal' : requestSignupModal,
    }
})();

export default Signup;
