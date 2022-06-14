require('./bootstrap');

// CKEditor
window.ClassicEditor = require('@ckeditor/ckeditor5-build-classic');

// Alpine
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// SweetAlert2
window.Swal = require('sweetalert2');

// Dropzone
import Dropzone from "dropzone";
window.Dropzone = Dropzone;

// Glider JS
import Glider from 'glider-js';
window.Glider = Glider;

// Flexslider
window.Flexslider = require('flexslider');
