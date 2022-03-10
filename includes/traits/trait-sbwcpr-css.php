<?php

/**
 * Frontend CSS
 */

trait sbwcpr_css
{
    public static function sbwcpr_frontend_css()
    { ?>
        <!-- css -->
        <style>
            span#sbwcpr_help {
                display: block;
                font-size: 13px;
                font-style: italic;
                line-height: 4;
                margin-top: -22px;
            }

            .sbwcpr_img_container {
                display: flex;
            }

            .sbwcpr_img_container>a {
                display: block;
                width: 19%;
                margin-right: 1%;
                border: 1px solid #ccc;
                border-radius: 3px;
                height: 88.75px;
                overflow: hidden;
            }

            .commentlist>li {
                border: none !important;
            }

        </style>
<?php }
}

?>