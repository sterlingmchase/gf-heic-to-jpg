<?php
namespace GF_HEIC_To_JPG;

class Gravity_Forms_Handler {
    public function __construct() {
        add_action( 'gform_enqueue_scripts', [ $this, 'enqueue_form_scripts' ], 10, 2 );
        add_filter( 'gform_file_upload_mime_types', [ $this, 'add_gform_heic_mime_types' ], 10, 3 );
    }

    public function enqueue_form_scripts( $form, $is_ajax ) {
        foreach ( $form['fields'] as $field ) {
            if ( $field->type === 'fileupload' ) {
                printf(
                    '<script>
                        jQuery(document).ready(function($) {
                            gfHeicToJpg.init();
                        });
                    </script>'
                );
            }
        }
    }

    public function add_gform_heic_mime_types( $mime_types, $form_id, $field_id ) {
        // Add HEIC and HEIF to the allowed mime types for Gravity Forms
        $mime_types['heic'] = 'image/heic';
        $mime_types['heif'] = 'image/heif';
        return $mime_types;
    }
}
