<?php

/**
 * JS for processing ajax
 */
trait sbwcpr_js
{

    /**
     * Render JS
     */
    public static function sbwcpr_ajax_js()
    { ?>

        <!-- js -->
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // ***************************
                // SUBMIT COMMENT/REVIEW FORM
                // ***************************

                // retrieve current url
                var url = $(location).attr('href');

                // retrieve logged in status
                var logged_in = $('#sbwcpr_img_input_container').data('user-logged-in');

                // clear all previously populated inputs
                if (logged_in === 'yes') {
                    $('#comment, #sbwcpr_img_input, #rating').val('');
                } else {
                    $('#comment, #sbwcpr_img_input, #rating, #author, #email').val('');
                }

                // grab image input
                var img_input_cont = $('#sbwcpr_img_input_container');

                // move image input container to before comment form submit button
                $('.form-submit').before(img_input_cont);

                // hijack submit action to do our stuff
                $('#commentform').submit(function(e) {

                    e.preventDefault();

                    // ajax url
                    var aju = '<?php echo admin_url('admin-ajax.php') ?>';

                    // setup vars
                    var comment_post_id, rating, review, email, author, user_id, user_ip, img_count;

                    rating = $('select#rating').val();
                    review = $('textarea#comment').val();
                    comment_post_id = $('#comment_post_ID').val();
                    author = $('#author').val();
                    email = $('#email').val();
                    user_id = $('#sbwcpr_user_id').val();
                    user_ip = $('#sbwcpr_user_ip').val();

                    // if no rating
                    if (!rating) {
                        alert('<?php _e('Please rate the product before submitting.') ?>');
                        return;
                    }

                    // if no review
                    if (!review || !email || !author) {
                        alert('<?php _e('Please fill out all required fields before submitting.') ?>');
                        return;
                    }

                    // check email validity
                    function isEmail(email_address) {
                        var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        return regex.test(email);
                    }

                    if (!isEmail(email)) {
                        alert('<?php _e('Please provide a valid email address.') ?>');
                        return;
                    }

                    // init new form data object
                    var form_data = new FormData();

                    // get image count
                    var img_count = document.getElementById('sbwcpr_img_input').files.length;

                    // img count loop to push submitted image to form data object
                    for (var index = 0; index < img_count; index++) {
                        form_data.append("sbwcpr_imgs[]", document.getElementById('sbwcpr_img_input').files[index]);
                    }

                    // append rest of submitted data to form_data object
                    form_data.append('action', 'sbwcpr_save_review_data');
                    form_data.append('_ajax_nonce', $('#sbwcpr_nonce').val());
                    form_data.append('rating', rating);
                    form_data.append('review', review);
                    form_data.append('prod_id', comment_post_id);
                    form_data.append('author', author);
                    form_data.append('email', email);
                    form_data.append('user_id', user_id);
                    form_data.append('user_ip', user_ip);
                    form_data.append('user_agent', '<?php echo $_SERVER['HTTP_USER_AGENT'] ?>');

                    // temp change submit button text
                    $('#submit').val('<?php _e('Processing...') ?>');

                    // submit via ajax
                    $.ajax({
                        type: 'POST',
                        url: aju,
                        data: form_data,
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(response) {
                            alert(response);
                            location.replace(url);
                        }
                    });
                });

                // **********************************
                // DISPLAY REVIEW IMAGES IN LIGHTBOX
                // **********************************
                $('.test-popup-link').magnificPopup({
                    type: 'image'
                    // other options
                });

            });
        </script>
<?php }
}
