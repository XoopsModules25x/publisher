<title>Trello Like Drag and Drop Cards for Publisher Articles</title>

<script src="<{xoAppUrl browse.php?Frameworks/jquery/jquery.js}>"></script>
<script src="<{xoAppUrl browse.php?Frameworks/jquery/plugins/jquery.ui.js}>"></script>
<script src="<{$mod_url|default:false}>/assets/js/trello.js"></script>

<link rel="stylesheet" href="<{$xoops_url}>/modules/system/css/ui/base/ui.all.css" type="text/css">
<link rel="stylesheet" type="text/css" href="<{$mod_url|default:false}>/assets/css/trello.css">

<div class="task-board">
    <{foreach from=$statusArray key=k item=v}>
        <div class="status-card">
            <div class="card-header">
                <span class="card-header-text"><{$v}></span>
            </div>
            <ul class="sortable ui-sortable"
                id="sort<{$k}>"
                data-status-id="<{$k}>">
                <{foreach item=item from=$statusResult}>
                    <{if $item.status == $k}>
                        <li class="text-row ui-sortable-handle"
                            data-item-id="<{$item.itemid}>">
                            <{$item.itemid}> <{$item.title}>
                        </li>
                    <{/if}>
                <{/foreach}>
            </ul>
        </div>
    <{/foreach}>
</div>
