<{include file="db:publisher_header.tpl" item=$item}>

<{if $op == 'preview'}>
    <br>
    <{include file="db:publisher_singleitem.tpl" item=$item}>
<{/if}>

<div class="publisher_infotitle"><{$langIntroTitle}></div>
<div class="publisher_infotext"><{$langIntroText}></div>
<br><{$form.javascript}>

<div id="tabs">
    <ul>
        <{foreach item=tab key=key from=$form.tabs}>
            <li><a href="#tab_<{$key}>"><span><{$tab}></span></a></li>
        <{/foreach}>
    </ul>

    <form name="<{$form.name}>" action="<{$form.action}>" method="<{$form.method}>"<{$form.extra}>><!-- start of form elements loop -->
        <{foreach item=tab key=key from=$form.tabs}>
            <div id="tab_<{$key}>">
                <table class="outer" cellspacing="1">
                    <{foreach item=element from=$form.elements}>
                    <{if $element.tab == $key || $element.tab == -1}>
                        <{if !$element.hidden}>
                            <tr>
                                <td class="head" width="30%">
                                    <{if $element.caption != ''}>
                                        <div class='xoops-form-element-caption<{if $element.required}>-required<{/if}>'>
                                            <span class='caption-text'><{$element.caption}></span>
                                            <{if $element.required}>
                                                <span class='caption-marker'>*</span>
                                            <{/if}>
                                        </div>
                                    <{/if}> <{if $element.description}>
                                        <div style="font-weight: normal; font-size:small;"><{$element.description}></div>
                                    <{/if}>
                                </td>
                                <td class="<{cycle values=" even,odd"}>"><{$element.body}></td>
                            </tr>
                        <{/if}>
                    <{/if}>
                    <{/foreach}><!-- end of form elements loop -->
                </table>
            </div>
        <{/foreach}>
        <{foreach item=element from=$form.elements}>
            <{if $element.hidden}>
                <{$element.body}>
            <{/if}>
        <{/foreach}></form>
</div>

<{if $isAdmin == 1}>
    <div class="publisher_adminlinks"><{$publisher_adminpage}></div>
<{/if}>
