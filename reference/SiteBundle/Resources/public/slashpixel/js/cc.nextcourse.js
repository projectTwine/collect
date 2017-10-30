const Follow = require("./cc.follow.js").default;

const NextCourse = (function(){
    function init() {
        $( document).ready(function () {
            $('[data-action="meet-your-next-course"]').click(showPickSubjectsStep);
        });
    }

    function showPickSubjectsStep()
    {
        var url = '/next-course/pick-subjects';
        ga('send','event','Meet your next course', 'Pick Subjects','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");
                $("#next-course-pick-subjects-modal").modal("show");

                // Init and attach event handlers to the follow buttons
                Follow.init();
                $("#next-course-pick-subjects-modal").find('.tagboard__tag').bind("followingChanged",  updatePickSubjectsFooter);

                // Hookup next and skip buttons
                $('#next-course-pick-subjects__next').click(function(){
                    ga('send','event','Meet your next course', 'Pick Subjects','Next');
                    $("#next-course-pick-subjects-modal").modal("hide"); // hide the modal
                    // $("#next-course-pick-subjects-modal").remove();
                    showPickProvidersStep();
                });


            },
            async: false
        })
    }

    function updatePickSubjectsFooter()
    {
        var nextButton = $('#next-course-pick-subjects__next');
        var numFollows = $("#next-course-pick-subjects-modal").find('.tagboard__tag.active').length;

        var percentage = numFollows*100/5;
        $("#next-course-pick-subjects-modal .meter__bar").width( percentage + '%');

        if(numFollows >= 5) {
            $(nextButton).addClass('active');
            $(nextButton).find("span").text('Pick more subjects or move on to Step 2 (of 2)');
        } else {
            var followsLeft = 5 - numFollows;
            $(nextButton).removeClass('active');
            if( followsLeft == 1) {
                $(nextButton).find("span").text('One more to go...');
            } else {
                $(nextButton).find("span").text('Pick ' + followsLeft + ' or more subjects');
            }
        }
    }

    function showPickProvidersStep()
    {
        var url = '/next-course/pick-providers';
        ga('send','event','Meet your next course', 'Pick Providers','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");
                $("#next-course-pick-providers-modal").modal("show");

                // Init and attach event handlers to the follow buttons
                Follow.init();
                $("#next-course-pick-providers-modal").find('.tagboard__tag').bind("followingChanged",  updatePickProvidersFooter);

                // Hookup next button
                $('#next-course-pick-providers__next').click(function(){
                    ga('send','event','Meet your next course', 'Pick Providers','Next');
                    $("#next-course-pick-providers-modal").modal("hide"); // hide the modal
                    // $("#next-course-pick-providers-modal").remove();
                    showLoadingScreenStep();
                });
            },
            async: false
        })
    }

    function updatePickProvidersFooter() {
        var nextButton = $('#next-course-pick-providers__next');
        var numFollows = $("#next-course-pick-providers-modal").find('.tagboard__tag.active').length;

        var percentage = numFollows*100/5;
        $("#next-course-pick-providers-modal .meter__bar").width( percentage + '%');

        if(numFollows >= 5) {
            $(nextButton).addClass('active');
            $(nextButton).find("span").text('Pick more providers or click to generate recommendations');
        } else {
            var followsLeft = 5 - numFollows;
            $(nextButton).removeClass('active');
            if( followsLeft == 1) {
                $(nextButton).find("span").text('One more to go...');
            } else {
                $(nextButton).find("span").text('Pick ' + followsLeft + ' or more providers');
            }
        }
    }

    function showLoadingScreenStep() {
        var url = '/next-course/loading-screen';
        ga('send','event','Meet your next course', 'Loading Screen','Shown');
        $.ajax({
            url: url,
            cache: false,
            success: function( result ) {
                var response = $.parseJSON(result);
                $(response.modal).appendTo("body");
                $("#next-course-loading-screen-modal").modal("show");

                var stepTime = 600;
                var progressBar =  $("#next-course-loading-screen-modal .meter__bar");
                var nextText = $('#next-course-loading-screen__next__text');
                // update the loading bar
                setTimeout(function(){
                    $(progressBar).width('20%');
                    $(nextText).html("<b style='color: black'>5..</b>4..3..2..1");
                    setTimeout(function(){
                        $(progressBar).width( '40%');
                        $(nextText).html("<b style='color: black'>5..4..</b>3..2..1");
                        setTimeout(function(){
                            $(progressBar).width( '60%');
                            $(nextText).html("<b style='color: black'>5..4..3..</b>2..1");
                            setTimeout(function(){
                                $(progressBar).width( '80%');
                                $(nextText).html("<b style='color: black'>5..4..3..2..</b>1");
                                setTimeout(function(){
                                    $(progressBar).width( '100%');
                                    $(nextText).html("<b style='color: black'>5..4..3..2..1</b>");

                                    // Redirect
                                    window.location = '/course-recommendations';
                                },stepTime)
                            },stepTime)
                        },stepTime)
                    },stepTime)
                },stepTime);


            },
            async: false
        })
    }
    init();

    return {
        init: init,
        showPickSubjectsStep: showPickSubjectsStep,
        showPickProvidersStep: showPickProvidersStep,
        showLoadingScreenStep: showLoadingScreenStep
    }
})();

export default NextCourse;
