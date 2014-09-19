/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$('.async-target > span').addClass('loading');

$('.async-load').each(function(){
	var container = $(this);
	var url = $(this).data('url');
	container.children('.async-target').each(function(){
		$(this).load(url, $(this).data('parameters'), function() {
			$('.async-post-load', container).show();
		});
	});
});
