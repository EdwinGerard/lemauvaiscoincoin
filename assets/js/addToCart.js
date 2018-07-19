import $ from "jquery";

$(document).ready(function () {
    let property = $('#addToCart');
    let count = 1;
    property.click(function () {
        if (count == 0) {
            property.css("background", "#ff9f1a");
            property.text('add to cart');
            count = 1;
        }
        else {
            property.css("background", "#000000");
            property.text('Trapped!');
            count = 0;
        }
    });
});

