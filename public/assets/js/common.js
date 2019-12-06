$(function () {

    $('body').on('keypress', '.keypress-input', function (e) {
        if (e.keyCode == 13) {
            IdToPress = $(this).attr('data-targetbtn');
            $('#' + IdToPress).trigger('click');
        }
    })
});

function showAlert(message, type, duration) {
    if (typeof duration == typeof undefined)
        duration = 2000;

    $('#main-alert').addClass('alert-' + type).html(message).fadeIn();
    setTimeout(function () {
        $('#main-alert').html('').removeClass('alert-' + type).fadeOut();
    }, duration);
}

function copyToClipboard(elemID) {
    val = $('#' + elemID).html();
    $('#' + elemID).after('<input id="tempLink" type="text" value="' + val + '"/>');
    $('#tempLink').select();
    document.execCommand('copy');
    $('#tempLink').remove();
}

function validMobile(string) {
    if (/^([0-9]{11})$/.test(string))
        return true;

    return false;
}

function validEmail(string) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(string))
        return true;

    return false;
}

function validPassword(string) {
    if (/^(?=.*\d)(?=.*[a-z]).{8,}$/.test(string))
        return true;

    return false;
}

function isJson(data) {
    var IS_JSON = true;
    try {
        var json = $.parseJSON(data);
    }
    catch (err) {
        IS_JSON = false;
    }
    return IS_JSON;
}

function drawChart(id, labels, values) {
    Chart.defaults.global.defaultFontFamily = "iransans";
    backgroundColor = [
        "#19d447",
        "#4646ff",
        "#ff8f3a",
        "#d272ff",
        "#ff1210",
        "#ffdf28",
        "#518e86",
    ];
    var config = {
        type: 'pie',
        data: {
            datasets: [{
                data: values,
                backgroundColor: backgroundColor,
            }],
            labels: labels
        },
        options: {
            responsive: true
        }
    };

    var ctx = document.getElementById('chart-q-' + id).getContext('2d');
    window.myPie = new Chart(ctx, config);
}

function rgb2hex(rgb) {
    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }

    return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function adjustColor(color, amount) {
    return '#' + color.replace(/^#/, '').replace(/../g, color => ('0' + Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2));
}

function loadPreviewImg(input, elemID) {
    // ex: loadPreviewImg(this,MyImg)
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#' + elemID).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function getShamsiDate(date) {
    date = new Date(date);
    shamsiDate = gregorian_to_jalali(date.getFullYear(), date.getMonth(), date.getDay());
    shamsiDate = shamsiDate[0] + "-" + shamsiDate[1] + "-" + shamsiDate[2];
    return shamsiDate;
}

function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0)
            return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}