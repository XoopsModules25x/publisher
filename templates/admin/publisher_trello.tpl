<title>Trello Like Drag and Drop Cards for Project Management Software</title>

<{*    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">*}>
<{*    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>*}>
<{*    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>*}>


<script src="<{xoAppUrl browse.php?Frameworks/jquery/jquery.js}>"></script>
<script src="<{xoAppUrl browse.php?Frameworks/jquery/plugins/jquery.ui.js}>"></script>
<script src="<{$publisher_url}>/assets/js/trello.js"></script>

<link rel="stylesheet" href="<{$xoops_url}>/modules/system/css/ui/base/ui.all.css" type="text/css">
<link rel="stylesheet" type="text/css" href="<{$publisher_url}>/assets/css/trello.css">


<div class="task-board">
    <{foreach from=$statusArray key=k item=v}>
        <div class="status-card">
            <div class="card-header">
                <span class="card-header-text"><{$v}></span>
            </div>

            <{foreach item=item from=$statusResult}>
                <ul class="sortable ui-sortable"
                    id="sort<{$item.itemid}>"
                    data-status-id="<{$item.status}>">
                    <{if $item.status == $k}>
                        <li class="text-row ui-sortable-handle"
                            data-item-id="<{$item.itemid}>">
                            <{$item.itemid}> <{$item.title}>
                        </li>
                    <{/if}>
                </ul>
            <{/foreach}>

        </div>
    <{/foreach}>
</div>


