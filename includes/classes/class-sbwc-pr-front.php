<?php

/**
 * Front display and upload
 */

class sbwc_pr_front
{

    use sbwcpr_css,
        sbwcpr_js,
        sbwcpr_user_ip;

    /**
     * init
     */
    public static function init()
    {

        // enqueue css and js
        add_action('wp_footer', [__CLASS__, 'sbwc_pr_scripts']);

        // add file input to review form
        add_action('comment_form', [__CLASS__, 'add_pr_image_input']);

        // save images to review ajax
        add_action('wp_ajax_nopriv_sbwcpr_save_review_data', [__CLASS__, 'sbwcpr_save_review_data']);
        add_action('wp_ajax_sbwcpr_save_review_data', [__CLASS__, 'sbwcpr_save_review_data']);

        // display comment images
        add_filter('comments_array', [__CLASS__, 'display_review_images'], 10, 2);

        // register pll strings
        if (function_exists('pll_register_string')) :
            pll_register_string('sbwcpr_string_1', 'Select review images to attach (optional)');
            pll_register_string('sbwcpr_string_2', 'Note: Maximum file size per image is 1mb. Image formats allowed are jpg, jpeg and png. Maximum 5 images allowed.');
            pll_register_string('sbwcpr_string_3', 'You are attempting to upload more images than allowed. Please remove some images and try again.');
            pll_register_string('sbwcpr_string_4', 'You are trying to upload unsupported file types. Only jpg, jpeg and png files are allowed.');
            pll_register_string('sbwcpr_string_5', 'One or more of your images are too big in terms of file size. Please upload images with a file size of 0.3mb or less.');
            pll_register_string('sbwcpr_string_6', 'Your product review has been submitted. Once reviewed by a staff member it will be made public.');
            pll_register_string('sbwcpr_string_7', 'Your review could not be published. Please reload the page and try again.');
        endif;
    }

    /**
     * Enqueue css and js
     *
     * @return void
     */
    public static function sbwc_pr_scripts()
    {

        // only load on product single
        if (is_product()) :
            wp_enqueue_script('sbwcpr-magnific-js', SBWCPR_URI . 'includes/magnific/magnific.min.js', ['jquery'], '1.1.0', true);
            wp_enqueue_script('sbwcpr-js', self::sbwcpr_ajax_js(), ['jquery', 'sbwcpr-magnific-js'], false, true);
            wp_enqueue_style('sbwcpr-magnific-css', SBWCPR_URI . 'includes/magnific/magnific.min.css', [], false, 'all');
            wp_enqueue_style('sbwcpr-css', self::sbwcpr_frontend_css(), [], false, 'all');
        endif;
    }

    /**
     * ADD IMAGE UPLOAD INPUT TO COMMENT FORM
     *
     * @return void
     */
    public static function add_pr_image_input()
    {

        // check if user is logged in and setup accordingly
        if (is_user_logged_in()) :
            $logged_in  = 'yes';
            $user_data  = get_userdata(get_current_user_id());
            $user_email = $user_data->data->user_email ? $user_data->data->user_email : get_user_meta(get_current_user_id(), 'billing_email', true);
            $author     = $user_data->data->display_name ? $user_data->data->display_name : $user_data->data->user_login;
        else :
            $logged_in = 'no';
        endif;

?>

        <div id="sbwcpr_img_input_container" data-user-logged-in="<?php echo $logged_in; ?>">

            <label for="sbwcpr_img_input"><?php pll_e('Select review images to attach (optional)'); ?></label>
            
            <span id="sbwcpr_help"><?php pll_e('Note: Maximum file size per image is 1mb. Image formats allowed are jpg, jpeg and png. Maximum 5 images allowed.'); ?></span>

            <!-- review images -->
            <input type="file" name="sbwcpr_img_input" id="sbwcpr_img_input" multiple>

            <!-- user id -->
            <input type="hidden" name="sbwcpr_user_ip" id="sbwcpr_user_ip" value="<?php echo self::get_user_ip(); ?>">

            <?php if (is_user_logged_in()) : ?>

                <!-- user id -->
                <input type="hidden" name="sbwcpr_user_id" id="sbwcpr_user_id" value="<?php echo get_current_user_id(); ?>">

                <!-- author -->
                <input type="hidden" name="author" id="author" value="<?php echo $author; ?>">

                <!-- email -->
                <input type="hidden" name="email" id="email" value="<?php echo $user_email; ?>">

            <?php endif; ?>

            <input type="hidden" name="sbwcpr_nonce" id="sbwcpr_nonce" value="<?php echo wp_create_nonce('sbwc publish product review'); ?>">
        </div>

<?php

    }

   /**
    * ADD IMAGES HTML TO REVIEWS/COMMENTS
    *
    * @param  array $comments - array of existing comments
    * @return void
    */
    public static function display_review_images($comments)
    {
        // if no comments, bail
        if (count($comments) < 1) :
            return $comments;
        endif;

        // loop to find which comments have images attached and display images
        foreach ($comments as $comment) :

            // if images present, appent to comment_content
            if (get_comment_meta($comment->comment_ID, 'sbwcpr_images', true)) :

                if (get_comment_meta($comment->comment_ID, 'sbwcpr_att_ids', true)) :

                    // ***********************************************
                    // ORIGINAL IMPLEMENTATION BACKWARDS COMPATIBILITY
                    // ***********************************************

                    // get attachment img ids
                    $attachment_ids = get_comment_meta($comment->comment_ID, 'sbwcpr_att_ids', true);

                    // append image data/html
                    $comment->comment_content .= '<div class="sbwcpr_img_container">';

                    if (is_array($attachment_ids) && !empty($attachment_ids)) :

                        foreach ($attachment_ids as $att_id) :

                            $img_url = wp_get_attachment_url($att_id);
                            $src     = wp_get_attachment_image_src($att_id, 'thumbnail', true)[0];

                            $comment->comment_content .= '<a class="sbwcpr_img" href="' . $img_url . '">';
                            $comment->comment_content .= '<img src="' . $src . '">';
                            $comment->comment_content .= '</a>';

                        endforeach;

                    endif;
                    $comment->comment_content .= '</div>';
                endif;

            endif;

            // *************************************************
            // COMMENTS IMAGES RELOADED BACKWARDS COMPATIBILITY
            // *************************************************
            if (get_comment_meta($comment->comment_ID, 'comment_image_reloaded', true)) :

                // append image data/html
                $comment->comment_content .= '<div class="sbwcpr_img_container comm_img_rel">';
                $attachment_ids = get_comment_meta($comment->comment_ID, 'comment_image_reloaded', true);

                // loop through img id array, get url/src and display
                foreach ($attachment_ids as $att_id) :

                    $img_url = wp_get_attachment_url($att_id);
                    $src     = wp_get_attachment_image_src($att_id, 'thumbnail', true)[0];

                    if (file_exists($img_url) && file_exists($src)) :
                        $comment->comment_content .= '<a class="sbwcpr_img" href="' . $img_url . '">';
                        $comment->comment_content .= '<img src="' . $src . '">';
                        $comment->comment_content .= '</a>';
                    endif;

                endforeach;
                
                $comment->comment_content .= '</div>';

            endif;
        endforeach;

        /**
         * return all comments so that original comment data will display,
         * otherwise only our image and image links will display
         */
        return $comments;
    }

    /**
     * SAVE COMMENT/REVIEW DATA AND IMAGES VIA AJAX
     * 
     * updated/revised 10 March 2022
     * 
     * @return void
     */
    public static function sbwcpr_save_review_data()
    {

        // check nonce
        check_ajax_referer('sbwc publish product review');

        // setup vars
        $rating     = $_POST['rating'];
        $review     = wp_strip_all_tags($_POST['review']);
        $prod_id    = $_POST['prod_id'];
        $email      = $_POST['email'];
        $user_id    = $_POST['user_id'];
        $author     = $_POST['author'];
        $user_ip    = $_POST['user_ip'];
        $images     = $_FILES['sbwcpr_imgs'];
        $user_agent = $_POST['user_agent'];

        // setup initial comment data
        $comment_data = [
            'comment_agent'        => $user_agent,
            'comment_approved'     => 0,
            'comment_author'       => $author,
            'comment_author_email' => $email ? $email : '',
            'comment_author_IP'    => $user_ip,
            'comment_content'      => $review,
            'comment_post_ID'      => $prod_id,
            'user_id'              => $user_id,
            'comment_meta'         => [
                'rating' => $rating
            ]
        ];

        // insert comment
        $comment_id = wp_insert_comment($comment_data);

        // if comment insertion failed, send error message and bail
        if (is_object($comment_id) && is_wp_error($comment_id)) :
            wp_send_json($comment_id->get_error_message());
        endif;

        // retrieve upload path/url
        $upload_dir_data = wp_upload_dir();
        $target_dir      = $upload_dir_data['path'] . '/';
        $target_url      = $upload_dir_data['url'] . '/';

        // retrieve submitted image data
        $img_names     = $images['name'];
        $img_types     = $images['type'];
        $img_errors    = $images['error'];
        $tmp_img_names = $images['tmp_name'];
        $img_sizes     = $images['size'];

        // setup allowed types array
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        // setup failed uploads array
        $failed_arr = [];

        // setup success arr
        $success_arr = [];

        // setup file url arr
        $file_urls = [];

        // setup attachment ids array
        $img_attachment_ids = [];

        // include WP image.php in order to enable generation of attached image meta data
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // loop and upload if certain checks are passed
        foreach ($img_names as $index => $img_name) :

            $type      = $img_types[$index];
            $error     = $img_errors[$index];
            $temp_name = $tmp_img_names[$index];
            $size      = $img_sizes[$index];

            // check size and skip if over 500k
            if ($size > 500000) :
                continue;
                $failed_arr[] = $img_name;
            endif;

            // check error and skip if found
            if ($error !== 0) :
                continue;
                $failed_arr[] = $img_name;
            endif;

            // check for invalid image types and skip if found
            if (!in_array($type, $allowed_types)) :
                continue;
                $failed_arr[] = $img_name;
            endif;

            // setup file name if all previous checks are passed
            $file_name = $target_dir . $img_name;
            $file_url  = $target_url . $img_name;

            // upload image to uploads directory
            if (move_uploaded_file($temp_name, $file_name)) :

                // populate $success_arr and $file_urls arrays
                $success_arr[] = $img_name;
                $file_urls[]   = $file_url;

                // ******************
                // insert attachment
                // ******************

                // retrieve file type
                $file_type = wp_check_filetype(basename($file_name), null);

                // generate attachment data
                $attachment_data = [
                    'guid'           => $upload_dir_data['url'] . '/' . basename($file_name),
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_name)),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ];

                // insert image as attachment
                $attached_img_id = wp_insert_attachment($attachment_data, $file_name, $comment_id);

                // push attached image id to $img_attachment_ids array
                $img_attachment_ids[] = $attached_img_id;

                // generate attachment data and update
                $attached_img_data = wp_generate_attachment_metadata($attached_img_id, $file_name);
                wp_update_attachment_metadata($attached_img_id, $attached_img_data);

            endif;

        endforeach;

        // attach image urls to comment
        if (!empty($file_urls) && !empty($img_attachment_ids)) :
            $comment_imgs_urls_updated = update_comment_meta($comment_id, 'sbwcpr_images', maybe_serialize($file_urls));
            $comment_img_ids_updated = update_comment_meta($comment_id, 'sbwcpr_att_ids', maybe_unserialize($img_attachment_ids));
        else :
            $comment_imgs_urls_updated = update_comment_meta($comment_id, 'sbwcpr_images', 'none');
            $comment_img_ids_updated = update_comment_meta($comment_id, 'sbwcpr_att_ids', 'none');
        endif;

        // send error/success messages as appropriate
        if (count($success_arr) === count($file_urls) && $comment_imgs_urls_updated !== false && $comment_img_ids_updated !== false) :
            wp_send_json(__('Your review has been submitted and will be published once reviewed by one of our staff members.'));
        elseif (count($success_arr) !== count($file_urls) && count($success_arr) !== 0) :
            wp_send_json(__('Your review has been submitted, but some images failed to upload. Your review will be published once reviewed by one of our staff members.'));
        elseif (count($success_arr) === 0) :
            wp_send_json(__('Your review has been submitted, however, the images you supplied failed to upload. Please check file types (gif,jpg and png allowed) and image file size (images bigger than 0.5mb are not allowed.'));
        endif;

        wp_die();
    }
}
sbwc_pr_front::init();
?>