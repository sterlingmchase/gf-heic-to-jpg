var gfHeicToJpg = (function () {
    return {
        init: function () {
            console.log('gfHeicToJpg initialized');
            jQuery(document).on('gform_post_render', function (event, formId) {
                // Remove any existing event listeners to prevent duplicate handling - messages were firing twice
                jQuery('input[type="file"]').off('change').on('change', function (event) {
                    console.log('File input changed');
                    gfHeicToJpg.convertFile(event.target);
                });
            });
        },

        convertFile: function (input) {
            const file = input.files[0];

            // Check file type or extension
            const isHeic = (file.type === 'image/heic' || file.type === 'image/heif');
            const isHeicExtension = file.name.toLowerCase().endsWith('.heic') || file.name.toLowerCase().endsWith('.heif');

            if (file && (isHeic || isHeicExtension)) {
                console.log('HEIC file detected:', file.name);
                gfHeicToJpg.showNotification(input, 'Converting iPhone HEIC file to work on websites.', 'success');
                const submitButton = document.querySelector('input[type="submit"]'); // Finds the first submit button in the document
                if (submitButton) {
                    submitButton.disabled = true; // Disables the submit button
                }
                heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.8,
                }).then(function (resultBlob) {
                    // Replace .heic or .heif (case-insensitive) with .jpg
                    const newFileName = file.name.replace(/\.heic$/i, '.jpg').replace(/\.heif$/i, '.jpg');
                    const newFile = new File([resultBlob], newFileName, {type: 'image/jpeg'});
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(newFile);
                    input.files = dataTransfer.files;
                    console.log('File replaced with JPG version:', newFile.name);
                    gfHeicToJpg.showNotification(input, 'HEIC file converted to JPG successfully!', 'success');
                    console.log('HEIC to JPG conversion successful');
                }).catch(function (error) {
                    console.error('HEIC to JPG conversion failed:', error);
                    alert('Conversion Failed');
                    gfHeicToJpg.showNotification(input, 'HEIC to JPG conversion failed. Please try again.', 'error');
                }).finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
            } else {
                console.log('No HEIC file detected or file type is not HEIC:', file);
            }
        },

        showNotification: function (input, message, type) {
            const notification = jQuery('<div class="gf-heic-notification ' + type + '">' +
                (type === 'success' ? '<span class="icon success-icon">&#10003;</span> ' : '<span class="icon error-icon">&#10060;</span>') +
                message + '</div>');
            notification.insertAfter(jQuery(input));
            notification.addClass('show');
            setTimeout(function () {
                notification.fadeOut(400, function () {
                    jQuery(this).remove();
                });
            }, 5000); // Hide after 5 seconds
        }
    };
})();

jQuery(document).ready(function () {
    console.log('Document ready, initializing gfHeicToJpg');
    gfHeicToJpg.init();
});
