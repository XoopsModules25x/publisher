<form name="search" action="<{$block.publisher_url}>/search.php" method="post">
    <table class="outer" border="0" cellpadding="1" cellspacing="0" align="center" width="95%">
        <tr>
            <td class="even"><strong><{$smarty.const._SR_KEYWORDS}></strong>
            </td>
        </tr>
        <tr>
            <td class="odd">
                <input type="text" name="term" title="term" value="<{$block.search_term}>" size="15">
            </td>
        </tr>
        <!-- <tr>
        <td class="head" align="right"><strong><{$smarty.const._SR_TYPE}></strong></td>
        <td class="even"><{$block.typeSelect}></td>
      </tr>  -->
        <tr>
            <td class="even">
                <strong><{$smarty.const._CO_PUBLISHER_CATEGORY}></strong></td>
        </tr>
        <tr>
            <td class="odd"><{$block.category_select}></td>
        </tr>
        <!--  <tr>
            <td class="head" align="right"><strong><{$smarty.const._SR_SEARCHIN}></strong></td>
            <td class="even"><{$block.searchSelect}></td>
          </tr>    -->
        <tr>
            <td class="even">
                <strong><{$smarty.const._CO_PUBLISHER_UID}></strong>&nbsp;</td>
        </tr>
        <tr>
            <td class="odd">
                <input type="text" name="uname" title="uname" value="<{$block.search_user}>" size="15">
            </td>
        </tr>
        <!-- <tr>
       <td class="head" align="right"><strong><{$smarty.const._CO_PUBLISHER_SORTBY}></strong>&nbsp;</td>
       <td class="even"><{$block.sortbySelect}></td>
     </tr>   --><{if $block.search_rule}>
        <tr>
            <td class="even"><strong><{$smarty.const._SR_SEARCHRULE}></strong>&nbsp;
            </td>
        </tr>
        <tr>
            <td class="odd"><{$block.search_rule}></td>
        </tr>
        <{/if}>
        <tr>
            <td class="odd">
                <input type="submit" name="submit" value="<{$smarty.const._SEARCH}>">
            </td>
        </tr>
    </table>
</form>
