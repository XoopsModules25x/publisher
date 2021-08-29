<{if $indexfooter|default:false}>
       <{$indexfooter}>
    <{/if}>

<{if $isAdmin|default:0 == 1}>
    <div class="publisher_adminlinks"><{$publisher_adminpage}></div>
<{/if}>

<{if ($commentatarticlelevel|default:false && $item.cancomment|default:false) || $com_rule|default:0 != 0}>
    <table border="0" width="100%" cellspacing="1" cellpadding="0" align="center">
        <tr>
            <td colspan="3" align="left">
                <div style="text-align: center; padding: 3px; margin:3px;"> <{$commentsnav}> <{$lang_notice|default:''}></div>
                <div style="margin:3px; padding: 3px;">
                    <!-- start comments loop -->
                    <{if $comment_mode|default:'' == "flat"}>
                        <{include file="db:system_comments_flat.tpl"}>
                    <{elseif $comment_mode|default:'' == "thread"}>
                        <{include file="db:system_comments_thread.tpl"}>
                    <{elseif $comment_mode|default:'' == "nest"}>
                        <{include file="db:system_comments_nest.tpl"}>
                    <{/if}>
                    <!-- end comments loop -->
                </div>
            </td>
        </tr>
    </table>
<{/if}>

<{if $rssfeed_link|default:'' != ''}>
    <div id="publisher_rpublisher_feed"><{$rssfeed_link|default:false}></div>
<{/if}>

<{include file='db:system_notification_select.tpl'}>
