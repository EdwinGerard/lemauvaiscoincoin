import $ from "jquery";

$(document).ready(function() {

    $('#research_categories').hide();
    $('label[for="research_categories"]').hide();
    $('label[for="research_departements"]').hide();
    $('#research_departements').hide();

    $('select[name="research[filter]"]').change(function() {
        let valeur = $(this).val();


        if(valeur == 'category') {
            $('label[for="research_categories"]').show();
            $('#research_categories').show();
            $('label[for="research_departements"]').hide();
            $('#research_departements').hide();
        } else if(valeur == 'department') {
            $('label[for="research_departements"]').show();
            $('#research_departements').show();
            $('label[for="research_categories"]').hide();
            $('#research_categories').hide();
        }else {
            $('#research_categories').hide();
            $('label[for="research_categories"]').hide();
            $('label[for="research_departements"]').hide();
            $('#research_departements').hide();
        }

    });

});