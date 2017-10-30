const Utilities = require("./cc.utilities.js").default;
const User = require("./cc.user.js").default;
const Signup = require("./cc.signup.js").default;

const Credential = (function(){
    var saveReviewClicked = false;

    function init() {
       ;
        // Attach handle to save button
        $('#cr-save-review').click(saveReview);

        // Attach handlers to filters
        $(".filter-credential-type").click(filterCredentialNameClicked);
        $(".tick-wrap-credential .tick").click(filterCredentialTickClicked);

        $('#cred-button-signup').click(function(e){
            e.preventDefault();
            Signup.showSignupModal("credential_create_free_account");

        });

        // More reviews button
        $('#credential-more-reviews').click(function(e){
            e.preventDefault();
            $('.review-block').removeClass('hidden');
            $('#credential-more-reviews').addClass('hidden');
        });

        // Handling clicks for Learn more tabs
        $('.table-tabs li').click( learnMoreTabClickHandler);

        $('#cred-read-reviews').click( function(e){
                e.preventDefault();
                $.scrollTo('#reviews',{ duration: 1000    });
                var url = window.location.href.toString().split(window.location.host)[1];
                if(url.indexOf('#reviews') == -1) {
                    history.replaceState( null, null, url + '#reviews');
                }
            }
        );

    }

    function learnMoreTabClickHandler(event){
        var tabName = $(this).data('tab');
        var url = window.location.href.toString().split(window.location.host)[1];

        // Retrieve the base url
        var baseUrl = null;

        if(url.search('/certificate/') == 0 && url.match(/\//g).length > 2) {
            baseUrl =  url.substring(0, url.lastIndexOf('/') );
        } else {
            baseUrl = url;
        }

        // Build the path
        var path = null;
        if(tabName == 'overview') {
            path = baseUrl;
        } else {
            path = baseUrl + '/' + tabName;
        }
        history.replaceState( null, null, path);
    }

    // =====================================================
    //      Credential - Create Review
    // ======================================================

    function getReviewFormFields() {
        var rating = $('#cr-rating').raty('score');
        var title = $('#cr-title').val();
        var reviewText = $('#cr-review-text').val();
        var progress = $('#cr-progress').val();
        var certificateLink = $('#cr-certificate-link').val();
        var topicCoverage = $('#cr-topic-coverage').raty('score');
        var jobReadiness = $('#cr-job-readiness').raty('score');
        var support = $('#cr-support').raty('score');
        var effort = $('#cr-effort').val();
        var duration = $('#cr-duration').val();

        return {
            'rating' : rating,
            'title': title,
            'reviewText': reviewText,
            'progress' : progress,
            'certificateLink' : certificateLink,
            'topicCoverage': topicCoverage,
            'jobReadiness' : jobReadiness,
            'support' : support,
            'effort' : effort,
            'duration' : duration,
            'name' : $('#cr-name').val(),
            'email' : $('#cr-email').val(),
            'jobTitle': $('#cr-job-title').val(),
            'highestDegree' : $('#cr-highest-degree').val(),
            'fieldOfStudy' : $('#cr-field-of-study').val()
        };
    }

    function validateReviewForm( review ) {

        var validationError = false;
        var errorDiv = '';
        // Rating cannot be empty
        if(review.rating === undefined) {
            $('#cr-error-rating').show();
            validationError = true;
            errorDiv = '#cr-error-rating';
        } else {
            $('#cr-error-rating').hide();
        }

        // progress cannot be empty
        if(review.progress === undefined || Utilities.isEmpty(review.progress)) {
            $('#cr-error-progress').show();
            validationError = true;
            if(!errorDiv) errorDiv = '#cr-error-progress';
        } else {
            $('#cr-error-progress').hide();
        }

        if(!Utilities.isEmpty(review.reviewText)) {
            // Non empty review. Should be 20 words long
            var words = review.reviewText.split(' ');
            if(words.length < 20) {
                $('#cr-error-review-text').show();
                validationError = true;
                if(!errorDiv) errorDiv = '#cr-error-review-text';
            } else {
                $('#cr-error-review-text').hide();
            }
        } else {
            $('#cr-error-review-text').hide();
        }

        if(!Utilities.isEmpty(review.reviewText)) {
            if(Utilities.isEmpty(review.title)) {
                $('#cr-error-title').show();
                 validationError = true;
                if(!errorDiv) errorDiv = '#cr-error-title';
            } else {
                $('#cr-error-title').hide();
            }
        }

        // Validate email if the user is not logged in
        if( !$('#loggedin').data('value') ) {
            $('#cr-error-email').hide();
            if(!Utilities.validateEmail(review.email) ) {
                validationError = true;
                $('#cr-error-email').show();
                if(!errorDiv) errorDiv = '#cr-error-email';
            }
        }

        if(errorDiv) $.scrollTo(errorDiv,{ duration: 400, offset : -90 });

        return validationError;
    }

    function saveReview(event) {
        event.preventDefault();
        $('#cr-save-review').attr('disabled', true);
        if(saveReviewClicked)
        {
            // Do nothing. Prevents duplicate clicks
            return false;
        }

        saveReviewClicked = true;

        var review = getReviewFormFields();
        var validationError = validateReviewForm( review );
        if( !validationError ) {
            // Redirect user to the credential page when they say no to signup
            $('#signupModal-create_credential_review').on('hidden.bs.modal',function(e){
                window.location.href = '/certificate/' + $('#credentialslug').data('value');
            });

            $.ajax({
                type : "post",
                url  : "/certificate/review/save/" + $('#credentialid').data('value'),
                data : JSON.stringify(review)
            })
                .done(
                    function(result) {
                        result = JSON.parse(result);
                        if(result['success']) {
                            // Redirect back to the certificate apage
                            window.location.href = '/certificate/' + $('#credentialslug').data('value');
                        } else {
                            // Show an error message
                            saveReviewClicked = false;
                        }
                    }
                );

        } else {
            $('#cr-save-review').attr('disabled', false);
            saveReviewClicked = false;
        }
    }

    // =====================================================
    //      Credentials Page - Filters
    // ======================================================
    function filterCredentialNameClicked(e) {
        e.preventDefault();
        var span = $(this).parent().find('span')[0];
        $(span).toggleClass('ticked');
        var type = $(this).data('type');
        var value = $(this).data(type);
        filterCredentials();

    }

    function filterCredentialTickClicked(e) {
        $(this).toggleClass("ticked");
        var node = $(this).parent().children('a');
        var type = node.data('type');
        var value = node.data(type);
        filterCredentials();
    }

    function filterCredentials(){
        var filterCerts = [];
        var filterSubjects = [];
        var params = {};
        var url = $.url().attr('path');

        $(".filter-credentials .ticked + .filter-credential-type").each(function() {
            var type = $(this).data('type');
            var value = $.trim($(this).data(type));
            if( type == 'certificate')
            {
                filterCerts.push(value);
            }

            if( type == 'subject')
            {
                filterSubjects.push(value);
            }
        });


        if(filterCerts.length > 0) {
            params['credentials'] = filterCerts.join();
        }

        if(filterSubjects.length > 0) {
            params['subjects'] = filterSubjects.join();
        }

        if( !$.isEmptyObject(params) ) {
            url = url+'?' + $.param(params)
        }

        history.replaceState(null, null, url);

        // Ajax query
        $.ajax({
            url: "/maestro" + url
        })
            .done(function(result){
                var response = $.parseJSON(result);
                $('#credentials-cards-row').html( response.cards );
                $('#number-of-credentials').html( response.numCredentials );
            });

    }
    init();
    return {
        'init' : init
    };
})();

export default Credential;
