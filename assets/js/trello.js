$( function() {
    var url = 'trellostatus.php';
    $('ul[itemid^="sort"]').sortable({
        connectWith: ".sortable",
        receive: function (e, ui) {
            var status_id = $(ui.item).parent(".sortable").data("status-id");
            var item_id = $(ui.item).data("item-id");
            $.ajax({
                url: url+'?status='+status_id+'&itemid='+item_id,
                success: function(response){
                }
            });
        }
    }).disableSelection();
} );
