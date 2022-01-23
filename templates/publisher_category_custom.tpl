<{include file='db:publisher_header.tpl'}>


<{assign var=theme_name value=$xoTheme->folderName}>

<{*    <{include file=$selectedCategoryTemplate}>*}>
<{*<{include file="$theme_name/modules/publisher/custom/publisher_custom1.tpl"}>*}>

<{include file="$theme_name/modules/publisher/$customtemplate"}>



<{include file='db:publisher_footer.tpl'}>

