var gfHeicToJpg = (function() {
    return {
        init: function() {
            console.log('gfHeicToJpg initialized');
            jQuery(document).on('gform_post_render', function(event, formId) {
                console.log('gform_post_render triggered for form', formId);
                jQuery('input[type="file"]').on('change', function(event) {
                    console.log('File input changed');
                    gfHeicToJpg.convertFile(event.target);
                });
            });
        },

        convertFile: function(input) {
            var file = input.files[0];
            if (file && (file.type === 'image/heic' || file.type === 'image/heif')) {
                console.log('HEIC file detected:', file.name);
                heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.8,
                }).then(function(resultBlob) {
                    console.log('HEIC to JPG conversion successful');
                    var newFile = new File([resultBlob], file.name.replace('.heic', '.jpg').replace('.heif', '.jpg'), { type: 'image/jpeg' });
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(newFile);
                    input.files = dataTransfer.files;
                    console.log('File replaced with JPG version:', newFile.name);
                    gfHeicToJpg.showNotification(input, 'HEIC file converted to JPG successfully!', 'success');
                }).catch(function(error) {
                    console.error('HEIC to JPG conversion failed:', error);
                    gfHeicToJpg.showNotification(input, 'HEIC to JPG conversion failed. Please try again.', 'error');
                });
            } else {
                console.log('No HEIC file detected or file type is not HEIC:', file);
            }
        },

        showNotification: function(input, message, type) {
            var notification = jQuery('<div class="gf-heic-notification ' + type + '">' +
                (type === 'success' ? '<span class="icon success-icon">&#10003;</span> ' : '<span class="icon error-icon">&#10060;</span>') + 
                message + '</div>');
            notification.insertAfter(jQuery(input));
            notification.addClass('show');
            setTimeout(function() {
                notification.fadeOut(400, function() {
                    jQuery(this).remove();
                });
            }, 5000); // Hide after 5 seconds
        }
    };
})();

jQuery(document).ready(function() {
    console.log('Document ready, initializing gfHeicToJpg');
    gfHeicToJpg.init();
});
