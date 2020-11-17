$( function() {
    let url = 'trellostatus.php';
    $('ul[id^="sort"]').sortable({
        connectWith: ".sortable",
        receive: function (e, ui) {
            let status_id = $(ui.item).parent(".sortable").data("status-id");
            let item_id = $(ui.item).data("item-id");
            $.ajax({
                url: url+'?statusId='+status_id+'&itemId='+item_id,
                success: function(response){
                }
            });
        }
    }).disableSelection();
} );
