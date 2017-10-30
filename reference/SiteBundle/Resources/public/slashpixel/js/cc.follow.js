const Utilities = require("./cc.utilities.js").default;

const Follow = (function(){
    var promptShownCookie = 'follow_personalized_page_prompt';

    function init() {
        $('.btn-follow-item').click(followClicked);
        $('[data-toggle="tooltip"]').tooltip();
        btnFollowBind();
    }

    function followClicked(e) {
        e.preventDefault();
        var self = $(this);
        var item = $(this).data('item');
        var itemId = $(this).data('item-id');
        var itemName = $(this).data('item-name');
        var showItemName = $(this).data('show-item-name');
        var following = $(this).data('following');
        var hideLogo  = $(this).data('hide-logo');
        var hideFollowing = $(this).data('hide-following');
        var hideNotification = $(this).data('hide-notification');
        var nextCourseWizard =  $(this).data('next-course-wizard');

        if(nextCourseWizard) {
            // Meet your next course clicked. Save the follows in sessions
            var url = '/next-course/follow/' + item +'/' + itemId;
            if(following) {
                var url = '/next-course/unfollow/' + item + '/' + itemId;
            }

            $.ajax({
                url: url,
                cache: false,
                success: function (r) {
                    // update the state to followed
                    var itemClass = '.btn-follow-item-' + item + '-' + itemId;
                    var btnText = '';
                    var itemText = "<span>" + itemName + "</span>";

                    if(following) {
                        if(showItemName) {
                            btnText = itemText;
                        }
                        // user has click the unfollow button
                        if(!hideFollowing) {
                            btnText = "Follow " + btnText;
                        }
                        $(itemClass).removeClass('active');
                        $(self).data('following',false);
                    } else {

                        if(showItemName) {
                            btnText = "<i>" + itemText + "</i>";
                        }

                        if(!hideFollowing) {
                            btnText = "Following " + btnText;
                        }
                        $(itemClass).addClass('active');

                        $(self).data('following',true);
                    }
                    $(itemClass).find('.btn-follow-item-box').html( btnText );
                    $(self).trigger('followingChanged'); // fire an event to so that onboarding modals can update count

                }
            });
        } else {
            if(self.hasClass('tagboard__tag')) {
                ga('send','event','Follow - Onboarding',item, itemName);
            }

            $.ajax({
                url: "/ajax/isLoggedIn",
                cache: false,
                success: function( result ) {
                    var loggedInResult = $.parseJSON(result);
                    if( loggedInResult.loggedIn ){
                        ga('send','event','Follow',"Logged in", item);


                        // Follow the item
                        var url = '/ajax/follow/' + item +'/' + itemId;
                        if(following) {
                            var url = '/ajax/unfollow/' + item +'/' + itemId;
                        }

                        $.ajax({
                            url: url,
                            cache:false,
                            success: function(r) {
                                var result = JSON.parse(r);
                                if(result['success']) {
                                    // update the state to followed
                                    var itemClass = '.btn-follow-item-' + item + '-' + itemId;
                                    var btnText = '';
                                    var itemText = "<span>" + itemName + "</span>";

                                    if(following) {
                                        if(showItemName) {
                                            btnText = itemText;
                                        }
                                        // user has click the unfollow button
                                        if(!hideFollowing) {
                                            btnText = "Follow " + btnText;
                                        }
                                        $(itemClass).removeClass('active');
                                        $(self).data('following',false);
                                        if(!hideNotification) {
                                            Utilities.notify(
                                                "Unfollowed " + itemName,
                                                "You will no longer receive course notifications and reminders about " + itemName,
                                                "success"
                                            );
                                        }
                                        // Decrement the user following count
                                        decrementFollowCount( item );
                                    } else {

                                        if(showItemName) {
                                            btnText = "<i>" + itemText + "</i>";
                                        }

                                        if(!hideFollowing) {
                                            btnText = "Following " + btnText;
                                        }
                                        $(itemClass).addClass('active');

                                        $(self).data('following',true);
                                        if( result.message.followCount == 10 ) {

                                            if(!hideNotification) {
                                                var recommendationsAlert = " <div class='alert alert-success alert-dismissible' role='alert' style='position: fixed; top: 100px; width: inherit;z-index: 1000000 '><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button> <a href='/user/recommendations'>Congrats! You have unlocked <strong>personalized course recommendations</strong>. Click here to view all recommendations.</a> </div>";
                                                $('.cc-body-content').append(
                                                    recommendationsAlert
                                                );
                                            }

                                        } else {
                                            if(!hideNotification) {
                                                Utilities.notify(
                                                    "Following " + itemName,
                                                    "You will receive regular course notifications and reminders about " + itemName,
                                                    "success"
                                                );
                                            }
                                        }

                                        incrementFollowCount( item );
                                    }
                                    $(itemClass).find('.btn-follow-item-box').html( btnText );
                                    $(self).trigger('followingChanged'); // fire an event to so that onboarding modals can update count

                                } else {
                                    // Show a error notification
                                    Utilities.notify(
                                        "Following Failed" + itemName,
                                        "There was some error with " + itemName + ". Please try again later.",
                                        "error"
                                    );
                                }
                            }
                        });


                    } else {

                        ga('send','event','Follow',"Logged Out", item);

                        // Save the follow info in session
                        $.ajax({
                            url: '/ajax/pre_follow/' + item +'/' + itemId,
                            cache: false,
                            success: function(r){
                                // Show signup modal
                                window.CC.Class.Signup.showSignupModal("btn_follow");
                            }
                        });


                    }
                }
            });
        }
    }

    function incrementFollowCount(item) {
        var counter = item + "-user-follow-count";
        if( $('.' + counter) ) {
            $('.' + counter).text(  parseInt($('.' + counter).text()) + 1);
        }
    }

    function decrementFollowCount(item) {
        var counter = item + "-user-follow-count";
        if( $('.' + counter) ) {
            $('.' + counter).text(  parseInt($('.' + counter).text()) - 1);
        }
    }

    function btnFollowBind() {
        $(document).on('followButton:unfollow', function(event, instance, done) {
            // Fires on an UNFOLLOW ACTION

            /**
             1. Make your ajax call here. on ajax callback fire the `done` method
             2. `instance` will contain any data you put into the `data-follow`
             attribute of the element, which can be used for backend coordination
             3. The timeout is just mock an async call
             **/
            btnFollowAction(instance['item'],instance['item-id'],true,done);
        });

        $(document).on('followButton:follow', function(event, instance, done) {
            // Fires on an FOLLOW ACTION

            btnFollowAction(instance['item'],instance['item-id'],false,done);
        });
    }

    function btnFollowAction(item,itemId,following,done) {
        $.ajax({
            url: "/ajax/isLoggedIn",
            cache: false,
            success: function( result ) {
                var loggedInResult = $.parseJSON(result);
                if( loggedInResult.loggedIn ){
                    ga('send','event','Follow',"Logged in", item);
                    ga('send','event','Follow Button With Count',"Logged in", item);

                    // Follow the item
                    var url = '/ajax/follow/' + item +'/' + itemId;
                    if(following) {
                        var url = '/ajax/unfollow/' + item +'/' + itemId;
                    }

                    $.ajax({
                        url: url,
                        cache:false,
                        success: function(r) {
                            if(following) {
                                done('decrement');
                            } else {
                                done('increment');
                            }
                        }
                    });
                } else {
                    ga('send','event','Follow',"Logged Out", item);
                    ga('send','event','Follow Button With Count',"Logged Out", item);

                    // Save the follow info in session
                    $.ajax({
                        url: '/ajax/pre_follow/' + item +'/' + itemId,
                        cache: false,
                        success: function(r){
                            // do nothing
                            done();
                        }
                    });
                    // Show signup modal
                    window.CC.Class.Signup.showSignupModal("btn_follow");
                }
            }
        });
    }

    /**
     * Shows a prompt asking the user if they want to be taken to the personalization
     * page
     * @param delay prompt delay in milliseconds
     */
    function showPersonalizationPrompt(delay) {

        if ( isPersonalizationPromptShown() ) {
            $.ajax({
                url: "/ajax/isLoggedIn",
                cache: true
            })
                .done(function(result){
                    var loggedInResult = $.parseJSON(result);
                    if( loggedInResult.loggedIn) {

                        // Show the signup form
                        setTimeout(function(){
                            // Check the cookie again
                            if(Cookies.get( promptShownCookie) === undefined ) {
                                window.CC.Class.Signup.followSubjectOnboarding();
                                setPersonalizationPromptShown();
                            }

                        },delay);
                    }
                }
            );
        }
    }

    function setPersonalizationPromptShown() {
        Cookies.set( promptShownCookie, 1, { expires :30} );
    }

    function isPersonalizationPromptShown() {
        return Cookies.get(promptShownCookie) === undefined;
    }

    init();

    return {
        init: init,
        showPersonalizationPrompt:showPersonalizationPrompt,
        setPersonalizationPromptShown: setPersonalizationPromptShown
    }
})();


export default Follow;
