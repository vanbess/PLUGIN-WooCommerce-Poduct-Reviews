<?php

/**
 * Checks validity of submitted images
 */

trait sbwcpr_img_check {
    public static function sbwcpr_check_images($images) {
        // 3. set max allowed images
        $max_images = 5;

        // 3.1 count images
        $img_count = count($images['name']);

        // 3.2 throw error if image count is above max allowed images
        if ($img_count > $max_images) :
            pll_e('You are attempting to upload more images than allowed. Please remove some images and try again.');
            wp_die();
        endif;

        // 4. set allowed file types
        $allowed_types = ['image/jpg', 'image/jpeg', 'image/png'];

        // 4.1 get file types
        foreach ($images as $key => $data) :
            if ($key == 'type') :
                $submitted_types = $data;
            endif;
        endforeach;

        // 4.2 if submitted files types don't match allowed types, throw error and bail
        foreach ($submitted_types as $type) :
            if (!in_array($type, $allowed_types)) :
                pll_e('You are trying to upload unsupported file types. Only jpg, jpeg and png files are allowed.');
                wp_die();
            endif;
        endforeach;

        // 5. set allowed file size
        $max_file_size = 5242880;

        // 5.1 get file sizes
        foreach ($images as $key => $data) :
            if ($key == 'size') :
                $submitted_sizes = $data;
            endif;
        endforeach;

        // 5.2 if submitted files sizes too big, throw error and bail
        foreach ($submitted_sizes as $size) :
            if ($size > $max_file_size) :
                pll_e('One or more of your images are too big in terms of file size. Please upload images with a file size of 0.3mb or less.');
                wp_die();
            endif;
        endforeach;
    }
}
