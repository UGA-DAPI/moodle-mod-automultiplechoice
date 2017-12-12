define(['jquery'], function ($) {
    return {
        init: function () {
            // bind change event
            $("#id_instructions").on("change", function () {
                this.setTopInstructionsValues();
            }.bind(this));
            // set values @page load
            this.setTopInstructionsValues();

            $("#id_anonymous").on("click", function () {
                if ($("#id_anonymous").is(':checked')) {
                    $("#id_amc_lstudent").attr('disabled', 'disabled');
                    $("#id_amc_lname").val($("#id_amc_lname").data('anon'));
                } else {
                    $("#id_amc_lstudent").removeAttr('disabled');
                    $("#id_amc_lname").val($("#id_amc_lname").data('std'));
                }
            });
        },
        setTopInstructionsValues() {
            var text = $("#id_instructions").val();
            // update value in hidden textarea (why is this field needed ? this value is used in form data ?
            // when I update the content of the div contenteditable the content of th hidden field is not updated...)
            $("#id_amc_instructionsprefix").html('<p>' + text + '</p>');
            // and in atto editor field (div contenteditable)...
            // can not find any rule on how ids are build in moodle when using $form->addElement(...)
            // nore how to dynamically set id on field
            // so... might not work any more if this strategy changes
            $("#id_amc_instructionsprefixeditable").html('<p>' + text + '</p>');
        }
    };
});
