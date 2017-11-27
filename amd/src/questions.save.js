define(['jquery', 'jqueryui', 'mod_automultiplechoice/jquery.dataTables', 'core/str', 'core/notification'], function ($, jqui, dataTables, str, notification) {

    return {
        init: function () {

            $("#questions-selected").sortable();

            $("#questions-selected").on("click", "button", function (e) {
                var $li = $(this).closest('li');
                var title = $li.find('.question-title').text();
                console.log(title);
                var confirmMsg = str.get_string('question_remove_confirm', 'mod_automultiplechoice', title);

                $.when(confirmMsg).done(function(localizedEditString) {
                    if (confirm(localizedEditString)) {
                      $(this).closest('li').remove();
                    }
                });


            });
        }

    }

});
