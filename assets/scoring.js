/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$(document).ready(function() {
    $("#questions-selected").on("change", "select", function(e) {
        var scoring = $(this).val();
		console.log($(this).find('option:selected').first());
		var score = $(this).find('option:selected').first().data('score');
		if (scoring === '') {
			$(this).closest('td').find('input.qscore').removeAttr('readonly');
		} else {
			$(this).closest('td').find('input.qscore').val(score).attr('readonly', 'readonly');
		}
    });

	var expectedTotalScore = parseInt($('#expected-total-score').text());
    $("#questions-selected").on("keyup", "input.qscore", function(e) {
        var total = 0;
		inputs = $("#questions-selected input.qscore").each(function(index) {
			total += parseFloat($(this).val());
		});
		total = Math.round(4*total) / 4;
		$('#computed-total-score').html(total)
				.parent().toggleClass("score-mismatch", total !== Math.floor(expectedTotalScore));
    });
	$("#questions-selected input.qscore").first().keyup();
} );
