<?php

/**
 * JS for processing ajax
 */
trait sbwcpr_js {

    /**
     * Render JS
     */
    public static function sbwcpr_ajax_js() { ?>

        <!-- js -->
        <script type="text/javascript">

            jQuery(document).ready(function($) {

                /* REVIEW SUBMISSION FORM */
                // move img input before
                var img_ul = $('div#sbwcpr_img_input_container');
                $('.form-submit').prepend(img_ul);

                // add class to submit button so that we don't get accidental comment submissions
                $('#commentform input#submit').addClass('sbwcpr_comment_submit');

                // on submit
                $('.sbwcpr_comment_submit').click(function(e) {
                    e.preventDefault();

                    var comment_post_id, rating, review, images, email, author, user_id, user_ip;

                    rating = $('select#rating').val();
                    review = $('textarea#comment').val();
                    comment_post_id = $('#comment_post_ID').val();
                    author = $('#author').val();
                    email = $('#email').val();
                    user_id = $('#sbwcpr_user_id').val();
                    user_ip = $('#sbwcpr_user_ip').val();

                    // if rating & review present, submit
                    if (rating && review) {

                        var form_data = new FormData();
                        var img_count = document.getElementById('sbwcpr_img_input').files.length;

                        for (var index = 0; index < img_count; index++) {
                            form_data.append("sbwcpr_imgs[]", document.getElementById('sbwcpr_img_input').files[index]);
                        }

                        console.log(form_data);
                        
                        return;

                        form_data.append('rating_prod_id', comment_post_id);
                        form_data.append('rating', rating);
                        form_data.append('review', review);
                        form_data.append('author', author);
                        form_data.append('email', email);
                        form_data.append('action', 'save_review_images');
                        form_data.append('user_id', user_id);
                        form_data.append('user_ip', user_ip);

                        var ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>';

                        $.post({
                            url: ajaxurl,
                            data: form_data,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                alert(response);
                                location.reload();
                                // console.log(response);

                            }
                        });
                    }
                });
            });
        </script>
<?php }
}
