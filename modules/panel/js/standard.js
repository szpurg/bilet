$(document).ready(function() {
    $(".checkall").click(function() {
       if ($(this).is(':checked')) {
           $(this).closest('.container').find('input[type="checkbox"]').prop('checked', true);
       }
       else {
           $(this).closest('.container').find('input[type="checkbox"]').prop('checked', false);
       }
    });
    $('.remove').click(function() {
        if (!confirm("Na pewno usunąć ten element?")) {
            return false;
        }
    });
    
});