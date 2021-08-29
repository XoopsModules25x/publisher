<{if $collapsable_heading|default:0 == 1}>
    <script type="text/javascript"><!--
        function goto_URL(object) {
            window.location.href = object.options[object.selectedIndex].value;
        }

        function toggle(id) {
            if (document.getElementById) {
                obj = document.getElementById(id);
            }
            if (document.all) {
                obj = document.all[id];
            }
            if (document.layers) {
                obj = document.layers[id];
            }
            if (obj) {
                if (obj.style.display == "none") {
                    obj.style.display = "";
                } else {
                    obj.style.display = "none";
                }
            }
            return false;
        }

        var iconClose = new Image();
        iconClose.src = 'assets/images/links/close12.gif';
        var iconOpen = new Image();
        iconOpen.src = 'assets/images/links/open12.gif';

        function toggleIcon(iconName) {
            if (document.images[iconName].src == window.iconOpen.src) {
                document.images[iconName].src = window.iconClose.src;
            } else if (document.images[iconName].src == window.iconClose.src) {
                document.images[iconName].src = window.iconOpen.src;
            }
        }

        //-->
    </script>
<{/if}>

<{if $publisher_display_breadcrumb|default:false}>
    <!-- Do not display breadcrumb if you are on indexpage or you do not want to display the module name -->
    <{if $module_home|default:false || $categoryPath|default:false}>
        <ul class="publisher_breadcrumb">
            <{if $module_home|default:false}>
                <li><{$module_home}></li>
            <{/if}>
            <{$categoryPath}>
        </ul><br>
    <{/if}>
<{/if}>

<{if $title_and_welcome|default:false && $lang_mainintro|default:'' != ''}>
                <span><{$lang_mainintro}></span>
<{/if}>
