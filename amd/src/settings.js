define(['jquery', 'core/str', 'core/notification'], function($, str, notification) {
  return {
    init: function(modeapivalue) {

      this.modeapivalue = String(modeapivalue);
      var modeselected = $('#id_s_mod_automultiplechoice_amcversion').val();
      this.toggleapirulfield(modeselected);

      $('#id_s_mod_automultiplechoice_amcversion').on('change', function(e) {
        this.toggleapirulfield($(e.target).val());
      }.bind(this));

    },
    toggleapirulfield: function(current) {
      // Use readonly instead of disabled since id disabled the field wont submit so previous data wont be erased.
      if (this.modeapivalue === current) {
        $('#id_s_mod_automultiplechoice_amcapiurl').attr('readonly', false);
      } else {
        $('#id_s_mod_automultiplechoice_amcapiurl').attr('value', '');
        $('#id_s_mod_automultiplechoice_amcapiurl').attr('readonly', true);
      }
    }
  }
});
