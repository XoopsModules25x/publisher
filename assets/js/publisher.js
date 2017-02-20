var $publisher = jQuery.noConflict();

$publisher(document).ready(function () {
    //Functions to execute on page load
    if ($publisher.isFunction($publisher.fn.tabs)) {
        $publisher("#tabs").tabs();
    }
    var MenuDom = xoopsGetElementById('image_item');
    if (MenuDom != null) {
        for (var i = 0; i < MenuDom.options.length; i++) {
            MenuDom.options[i].selected = true;
        }
    }

});


function publisher_appendSelectOption(fromMenuId, toMenuId) {
    var fromMenuDom = xoopsGetElementById(fromMenuId);
    var toMenuDom = xoopsGetElementById(toMenuId);
    var newOption = new Option(fromMenuDom.options[fromMenuDom.selectedIndex].text, fromMenuDom.options[fromMenuDom.selectedIndex].value);
    newOption.selected = true;
    toMenuDom.options[toMenuDom.options.length] = newOption;
    fromMenuDom.remove(fromMenuDom.selectedIndex);

    var MenuDom = xoopsGetElementById('image_item');
    if (MenuDom != null) {
        for (var i = 0; i < MenuDom.options.length; i++) {
            MenuDom.options[i].selected = true;
        }
    }
}

function publisher_updateSelectOption(fromMenuId, toMenuId) {
    var fromMenuDom = xoopsGetElementById(fromMenuId);
    var toMenuDom = xoopsGetElementById(toMenuId);
    for (var i = 0; i < toMenuDom.options.length; i++) {
        toMenuDom.remove(toMenuDom.options[i]);
    }
    var index = 0;
    for (var i = 0; i < fromMenuDom.options.length; i++) {
        if (fromMenuDom.options[i].selected) {
            var newOption = new Option(fromMenuDom.options[i].text, fromMenuDom.options[i].value);
            toMenuDom.options[index] = newOption;
            index++;
        }
    }
}

// ----------------------- NEW --------------------

/*
function call_php_function(php_function, anyattribut) {
    $.post("functions.php", ("call_function": php_function), ("attribute_name": anyattribut), function(data) {
        // Data is what you get from the echo in the PHP-Script below
        alert(data); // Outputs "hello"
    }
}


function call_php_function(directory) {

}

function myAjax() {
    $.ajax({
        type: "POST",
        url: 'your_url/ajax.php',
        data:{action:'call_this'},
        success:function(html) {
            alert(html);
        }

    });
}

 (function($){
 $(document).ready(function(){
 $.jGrowl("<br>Incorrect Login!<br>using xoops authentication method", {  life:3000 , position: "center", speed: "slow" });
 });
 })(jQuery);





*/
