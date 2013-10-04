/* 
 * @license http://www.gnu.org/licenses/gpl-2.0.html  GNU GPL v2
 */

$(document).ready(function() {
    $("#questions-selected").on("change", "select", function(e) {
        var scoring = $(this).val();
		console.log($(this).find('option:selected').first());
		var score = $(this).find('option:selected').first().data('score')
		if (scoring === '') {
			$(this).closest('td').find('input.qscore').removeAttr('readonly');
		} else {
			$(this).closest('td').find('input.qscore').val(score).attr('readonly', 'readonly');
		}
    });
} );
