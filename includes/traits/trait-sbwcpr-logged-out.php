<?php

/**
 * Processing for logged out/non registered users
 */

trait sbwcpr_logged_out {

    use sbwcpr_img_check;

    public static function sbwcpr_process_logged_out($images, $author, $email, $user_ip, $review, $rating_prod_id, $rating) {

        // check for presence of rating AND review before processing
        if ($rating != '' && $review != '') :
            
            // if images
            if (!empty($images)) :

                // check for valid images
                self::sbwcpr_check_images($images);

                // image count
                $img_count = count($images);

                // uploads path
                $path = wp_upload_dir();

                // loop
                for ($i = 0; $i < $img_count; $i++) {

                    // 6.2.1 setup file for upload and movement to uploads directory
                    $file_name = strtolower(str_replace(' ', '_', $_FILES['sbwcpr_imgs']['name'][$i]));
                    $file_tmp_name = $_FILES['sbwcpr_imgs']['tmp_name'][$i];
                    $destination = $path['path'] . '/' . $file_name;

                    // 6.2.2 if moved to uploads directory successfully, append to review log
                    if (move_uploaded_file($file_tmp_name, $destination)) :
                        $current_time = time('now');
                        $current_date = date('today');
                        $file_location = $destination;
                        $file_url = $path['url'] . '/' . $file_name;
                        file_put_contents(SBWCPR_PATH . 'includes/log/review_img_uploads.log', $current_date . ' ' . $current_time . ' - File location: ' . $file_location . '\r\n', FILE_APPEND);
                    endif;

                    // push file urls to array so that we can attach them to the comment in question
                    $img_urls[] = $file_url;

                    // push files paths to array so that we can properly insert them into the media library once review is inserted
                    $img_paths[] = $destination;
                }
            endif;

            // if image urls present, insert comment and attach image urls as meta for reference on the frontend
            if (is_array($img_urls) && !empty($img_urls)) :

                // produce review arguments
                $review_args = [
                    'comment_approved' => 0,
                    'comment_author' => $author,
                    'comment_author_email' => $email,
                    'comment_author_IP' => $user_ip,
                    'comment_content' => $review,
                    'comment_post_ID' => $rating_prod_id,
                    'comment_meta' => [
                        'sbwcpr_images' => $img_urls,
                        'rating' => $rating
                    ],
                ];

                // insert product review using args
                $review_inserted = wp_insert_comment($review_args);

            // else insert vanilla review
            else :
                // produce review arguments
                $review_args = [
                    'comment_approved' => 0,
                    'comment_author' => $author,
                    'comment_author_email' => $email,
                    'comment_author_IP' => $user_ip,
                    'comment_content' => $review,
                    'comment_post_ID' => $rating_prod_id,
                    'comment_meta' => [
                        'sbwcpr_images' => '',
                        'rating' => $rating
                    ],
                ];

                // insert product review using args
                $review_inserted = wp_insert_comment($review_args);

            endif;

            // 8. if review inserted successfully, display success message, else display aerror
            if ($review_inserted && $img_paths) :

                // add uploaded images to media library
                foreach ($img_paths as $file) :

                    // get file name
                    $filename = basename($file);

                    // upload file
                    $upload_file = wp_upload_bits($filename, null, file_get_contents($file));

                    // if no errors insert attachment
                    if (!$upload_file['error']) :

                        // get file type
                        $wp_filetype = wp_check_filetype($filename, null);

                        // setup file args
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_parent' => $review_inserted,
                            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        // insert file as attachment
                        $attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $review_inserted);

                        // attachment id array
                        $attachment_ids[] = $attachment_id;

                        // if insert attachment produces no errors, update file meta data
                        if (!is_wp_error($attachment_id)) :
                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                            wp_update_attachment_metadata($attachment_id,  $attachment_data);
                        else :
                            echo 'file meta data could not be updated';
                        endif;
                    endif;
                endforeach;

                // add attachment ids to comment meta
                update_comment_meta($review_inserted, 'sbwcpr_att_ids', $attachment_ids);

                pll_e('Your product review has been submitted. Once reviewed by a staff member it will be made public.');
            elseif ($review_inserted && !$img_urls) :
                pll_e('Your product review has been submitted. Once reviewed by a staff member it will be made public.');
            else :
                pll_e('Your review could not be published. Please reload the page and try again.');
            endif;
        endif;
    }
}
