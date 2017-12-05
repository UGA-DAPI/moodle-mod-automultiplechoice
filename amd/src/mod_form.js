define(['jquery'], function ($) {
    return {
        init: function () {

            $("#id_instructions").on("change", function () {
                var text = $(this).val();
                // update value in hidden textarea (why is this field needed ?)
                $("#id_amc_instructionsprefix").html('<p>' + text + '</p>');
                // and in atto editor field... 
                // can not find any rule on how ids are build in moodle when using $form->addElement(...)
                // nore how to dynamically set id on field
                // so... might not work any more in some times
                $("#id_amc_instructionsprefixeditable").html('<p>' + text + '</p>');
            });

            $("#id_anonymous").on("click", function () {
                if ($("#id_anonymous").is(':checked')) {
                    $("#id_amc_lstudent").attr('disabled', 'disabled');
                    $("#id_amc_lname").val($("#id_amc_lname").data('anon'));
                } else {
                    $("#id_amc_lstudent").removeAttr('disabled');
                    $("#id_amc_lname").val($("#id_amc_lname").data('std'));
                }
            });
        }
    };
});


