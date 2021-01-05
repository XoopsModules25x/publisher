<{include file="db:publisher_header.tpl" item=$item|default:false}>

<{if $op|default:false == 'preview'}>
    <br>
    <{include file="db:publisher_singleitem.tpl" item=$item}>
<{/if}>

<div class="publisher_infotitle"><{$langIntroTitle|default:''}></div>
<div class="publisher_infotext"><{$langIntroText|default:''}></div>
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
                         <{if !$element.hidden|default:false}>
                            <tr>
                                <td class="head" width="30%">
                                    <{if $element.caption|default:false != ''}>
                                        <div class='xoops-form-element-caption<{if $element.required}>-required<{/if}>'>
                                            <span class='caption-text'><{$element.caption}></span>
                                            <{if $element.required}>
                                                <span class='caption-marker'>*</span>
                                            <{/if}>
                                        </div>
                                    <{/if}> <{if $element.description|default:false}>
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
            <{if $element.hidden|default:false}>
                <{$element.body}>
            <{/if}>
        <{/foreach}>
    </form>
</div>

<{if $isAdmin|default:0 == 1}>
    <div class="publisher_adminlinks"><{$publisher_adminpage}></div>
<{/if}>
