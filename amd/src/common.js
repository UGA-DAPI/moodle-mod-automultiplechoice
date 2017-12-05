define(['jquery'], function ($) {

    return {
        init: function() {
            // listen to select changes in order to submit form and refresh displayed data
            $(".submit-on-change").on('change', function(){
                $(this).closest('form').submit();
            });
        }
    }
});
