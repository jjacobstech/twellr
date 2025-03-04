import './bootstrap';
import Dropzone from 'dropzone';

// Prevent Dropzone from auto discovering the element
Dropzone.autoDiscover = false;

document.addEventListener('DOMContentLoaded', function () {
    // Configure Dropzone
    const dropzone = new Dropzone('#file-upload', {
        url: '/upload', // Your upload route
        paramName: 'file', // The name that will be used to transfer the file
        maxFilesize: 5, // MB
        maxFiles: 10,
        acceptedFiles: '.jpg,.jpeg,.png,.gif,.pdf', // Allowed file types

        // Custom preview template
        previewTemplate: document.getElementById('preview-template').innerHTML,

        // Customise the progress bar
        uploadprogress: function (file, progress, bytesSent) {
            // Custom progress handling
            if (file.previewElement) {
                let progressElement = file.previewElement.querySelector('.dz-upload');
                progressElement.style.width = progress + '%';

                // Optional: Add text to show percentage
                if (progressElement) {
                    progressElement.textContent = progress.toFixed(0) + '%';
                }
            }
        },

        // Success and error handling
        success: function (file, response) {
            file.previewElement.classList.add('dz-success');
            console.log('File uploaded successfully', response);
        },

        error: function (file, errorMessage) {
            file.previewElement.classList.add('dz-error');
            console.error('Upload error', errorMessage);
        }
    });
});
