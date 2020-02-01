(function($){
    $(document).ready(function(){
        $(document).on('click', '.ptsh-action', function(e){
            e.preventDefault();
            var $this = $(this),
                wrapper = $('#ptsh-target-slug'),
                slug = wrapper.attr('data-slug'),
                action = 'ptsh_';

            if ($this.is('.ptsh-action--save')) {
                action += 'save';
            } else if ($this.is('.ptsh-action--delete')) {
                action += 'delete';
            } else if ($this.is('.ptsh-action--regenerate')) {
                action += 'regenerate';
            } else {
                return false;
            }

            $.ajax({
                url: ajaxurl,
                method: 'GET',
                dataType: 'json',
                data: {
                    action: action,
                    slug: slug,
                    nonce: ptshadmin.nonce,
                },
                success: function(response) {
                    if ( typeof response.data.message === 'undefined' ) {
                        alert(response);
                    } else {
                        alert(response.data.message);
                    }

                    if ( typeof response.data.html !== 'undefined' ) {
                        wrapper.html(response.data.html);
                    }
                },
                fail: function(response) {
                    if ( typeof response.data.message === 'undefined' ) {
                        alert(response);
                    } else {
                        alert(response.data.message);
                    }
                },
            });

            return false;
        });
    });
})(jQuery);