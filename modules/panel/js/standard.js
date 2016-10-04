var sectorsActions = {
    init: function() {
        $(document).ready(function() {
            sectorsActions.updateHeaderValues();
            $('#sectors input[type="checkbox"]').click(sectorsActions.updateHeaderValues);
        });
    },
    updateHeaderValues: function() {
        var $inputs = $('#sectors input[type="checkbox"]:not(".checkall"):checked');
        var $h2 = $('#sectors > label > h2');
        $h2.find('span').remove();
        var available = 0;
        $inputs.each(function() {
            var $parent = $(this).parent();
            var value = $parent.text().replace(/^.+?\(([0-9]+?)\)/g, "$1");
            if (value) {
                available += parseInt(value);
            }
        });
        $('<span> [' + $inputs.length + '] (' + available + ')</span>').appendTo($h2);
    }
};

$(document).ready(function() {
    var captchaLoginProcess = null;
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
    $('.captchaActivator').click(function() {
        var login = $(this).closest('.userContainer').find('label').first().text().trim();
        captchaLoginProcess = login;
        window.open($(this).attr('href'), '_blank');
        keepCheckingUser($(this), login);
        return false;
    });
    
    function keepCheckingUser($setter, login) {
        var inv = setInterval(function() {
            $.get('/panel/checkCaptcha/' + login, {}, function(response) {
                if (response.status && response.status === 'ok') {
                    clearInterval(inv);
                    captchaLoginProcess = null;
                    $setter.remove();
                }
            }, 'json');
            
        }, 1000);
    }
});