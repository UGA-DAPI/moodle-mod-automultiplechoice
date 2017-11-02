// this one is included in renderer.php so it's available in every mod page
define(['jquery'], function ($) {

    return {
        init: function () {
            $('.async-target > span').addClass('loading');
            this.asyncLoadComponents();
        },
        asyncLoadComponents: function () {
            $('.async-load').each(this.asyncLoadComponent());
        },
        asyncLoadComponent: function () {
            var container = $(this);
            var url = $(this).data('url');
            container.children('.async-target').each(function () {
                $(this).load(url, $(this).data('parameters'), function () {
                    $('.async-post-load', container).show();
                });
            });
        },
        asyncReloadComponents: function () {
            $('.async-load .async-target').html('<span class="loading" />');
            this.asyncLoadComponents();
        }
    };
});