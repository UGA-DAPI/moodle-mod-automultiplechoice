/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$('.async-target > span').addClass('loading');

function asyncLoadComponents() {
	$('.async-load').each(asyncLoadComponent);
}

function asyncLoadComponent(){
	var container = $(this);
	var url = $(this).data('url');
	container.children('.async-target').each(function(){
		$(this).load(url, $(this).data('parameters'), function() {
			$('.async-post-load', container).show();
		});
	});
}

function asyncReloadComponents() {
	$('.async-load .async-target').html('<span class="loading" />');
	asyncLoadComponents();
}

asyncLoadComponents();
