import 'bootstrap/dist/js/bootstrap';
import $ from 'jquery';

$('input[type=file]').change(function () {
    let path = this.files[0].name;
    $('input[type=file]').next().text(path);
});