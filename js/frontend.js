(function($){
    $(document).ready(function() {
        function lazyLoadImages() {
            $("img.lazy").each(function () {
                var $this = $(this),
                    elementOffset = $this.offset();

                if ($(window).scrollTop() > (elementOffset.top - 50 - window.screen.availHeight)) {
                    $this.attr("src", $this.attr("data-src")).removeClass("lazy");
                }
            });
        }

        lazyLoadImages();
        $(window).on('scroll', lazyLoadImages);
    });
})(jQuery);