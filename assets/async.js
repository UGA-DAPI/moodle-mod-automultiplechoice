/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$('.async-target > span.loading').addClass('loading');

$.ajaxSetup({
	async: false
});
$('.async-load').each(function(){
	var url = $(this).data('url');
	console.log(url);
	$(this).children('.async-target').each(function(){
		console.log($(this).data('parameters'));
		$(this).load(url, $(this).data('parameters'));
	});
});
