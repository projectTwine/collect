const PNotify = require('pnotify');

const Utilities = (function(){

    /**
     *  Sends a popup notification to the user
     * @param title
     * @param text
     * @param type
     */
    function notify(title,text,type) {
        new PNotify({
            'title': title,
            'text' : text,
            'type' : type,
            'animation' : 'show',
            'delay':  2000
        });
    }

    /**
     * Sends a popup notification and shows it
     * for a specific amount of time
     * @param title
     * @param text
     * @param type
     * @param delay
     */
    function notifyWithDelay( title, text, type, delay) {
        new PNotify({
            title: title,
            text: text,
            type: type,
            animation: 'show',
            delay:  2000
        });
    }

    /**
     * Checks whether a string is empty
     * @param str
     * @returns {boolean}
     */
    function isEmpty(str) {
        return (!str || 0 === str.length);
    }

    /**
     * Basic check whether email is correct
     * @param email
     * @returns {boolean}
     */
    function validateEmail(email) {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    function hideWidgets() {
        $('w-div').hide();
    }

    return {
        notify: notify,
        notifyWithDelay: notifyWithDelay,
        isEmpty:isEmpty,
        validateEmail:validateEmail,
        hideWidgets: hideWidgets
    };

})();

export default Utilities;
