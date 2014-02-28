<style type="text/css">
#bottomLinks{ldelim}display:none;{rdelim}
</style>
<form id="MailrelaySettingsForm" name="MailrelaySettingsForm" method="POST" action="index.php">
<input type="hidden" name="module" value="Configurator">
<input type="hidden" name="action" value="syncUsers">
{foreach from=$messages item=message}
    <span class="success">{$message}</span>
    <br /><br />
{/foreach}
{foreach from=$errors item=error}
    <span class="error">{$error}</span>
    <br /><br />
{/foreach}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <td style="padding-bottom: 2px;" width="100%">
        <input title="{$MOD.LBL_MAILRELAY_SYNC_BUTTON}" class="button" type="submit" value="{$MOD.LBL_MAILRELAY_SYNC_BUTTON}">
        <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" onclick="document.location.href='index.php?module=Administration&action=index'" class="button" type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
    </td>
</tr>
<tr>
    <td width="100%">
        <table id="connectionSettingsTable" width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        <tr>
            <td align="left" class="dataField" width="25%" nowrap="nowrap">
                {$MOD.LBL_MAILRELAY_GROUPSTOSYNC_USERS}<br /><br />
                <select id="groups_users" name="groups_users[]" multiple="multiple" size="{$groups|@count}">
                {foreach key=key item=item from=$groups}
                <option value="{$item.id}" {if $item.id|in_array:$settings.groups_users}selected="selected"{/if}>{$item.name}</option>
                {/foreach}
                </select>
                <span id="groups_users_error" class="error" style="display:none;"></span>
            </td>
            <td align="left" class="dataField" width="25%" nowrap="nowrap">
                {$MOD.LBL_MAILRELAY_GROUPSTOSYNC_LEADS}<br /><br />
                <select id="groups_leads" name="groups_leads[]" multiple="multiple" size="{$groups|@count}">
                {foreach key=key item=item from=$groups}
                <option value="{$item.id}" {if $item.id|in_array:$settings.groups_leads}selected="selected"{/if}>{$item.name}</option>
                {/foreach}
                </select>
                <span id="groups_leads_error" class="error" style="display:none;"></span>
            </td>
            <td align="left" class="dataField" width="25%" nowrap="nowrap">
                {$MOD.LBL_MAILRELAY_GROUPSTOSYNC_ACCOUNTS}<br /><br />
                <select id="groups_accounts" name="groups_accounts[]" multiple="multiple" size="{$groups|@count}">
                {foreach key=key item=item from=$groups}
                <option value="{$item.id}" {if $item.id|in_array:$settings.groups_accounts}selected="selected"{/if}>{$item.name}</option>
                {/foreach}
                </select>
                <span id="groups_accounts_error" class="error" style="display:none;"></span>
            </td>
            <td align="left" class="dataField" width="25%" nowrap="nowrap">
                {$MOD.LBL_MAILRELAY_GROUPSTOSYNC_CONTACTS}<br /><br />
                <select id="groups_contacts" name="groups_contacts[]" multiple="multiple" size="{$groups|@count}">
                {foreach key=key item=item from=$groups}
                <option value="{$item.id}" {if $item.id|in_array:$settings.groups_contacts}selected="selected"{/if}>{$item.name}</option>
                {/foreach}
                </select>
                <span id="groups_contacts_error" class="error" style="display:none;"></span>
            </td>
        </tr>
        </table>
    </td>
</td>
</table>
<div style="padding-top: 2px;">
    <input title="{$MOD.LBL_MAILRELAY_SYNC_BUTTON}" class="button" type="submit" value="{$MOD.LBL_MAILRELAY_SYNC_BUTTON}">
    <input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}" onclick="document.location.href='index.php?module=Administration&action=index'" class="button" type="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</div>
</form>
<!-- {$JAVASCRIPT} -->
<script type="text/javascript">
    $('#MailrelaySettingsForm').submit(function(){ldelim}
        var required_fields = [];

        if (this.elements['groups_users'].value == '' &&
            this.elements['groups_leads'].value == '' &&
            this.elements['groups_accounts'].value == '' &&
            this.elements['groups_contacts'].value == '') {ldelim}
            required_fields = ['groups_users', 'groups_leads', 'groups_accounts', 'groups_contacts'];
        {rdelim}

        var checked = true;
        var blink = function(field, color, source_color, step, step_number) {ldelim}
            if (typeof(step_number) == 'undefined') {ldelim}
                step_number = 0;
            {rdelim}
            if (typeof(source_color) == 'string') {ldelim}
                source_color = source_color.substr(1);
                var source_color = {ldelim}
                    'r': parseInt(source_color.slice(0, 2), 16),
                    'g': parseInt(source_color.slice(2, 4), 16),
                    'b': parseInt(source_color.slice(4, 6), 16)
                {rdelim};
            {rdelim}
            if (typeof(color) == 'string') {ldelim}
                color = color.substr(1);
                var color = {ldelim}
                    'r': parseInt(color.slice(0, 2), 16),
                    'g': parseInt(color.slice(2, 4), 16),
                    'b': parseInt(color.slice(4, 6), 16)
                {rdelim};
            {rdelim}
            if (typeof(step) == 'undefined') {ldelim}
                var step = {ldelim}
                    'r': (source_color['r'] - color['r']) / 100,
                    'g': (source_color['g'] - color['g']) / 100,
                    'b': (source_color['b'] - color['b']) / 100
                {rdelim};
            {rdelim}
            field.style.backgroundColor = 'RGB(' + Math.round(color['r']) + ', ' + Math.round(color['g']) + ', ' + Math.round(color['b']) + ')';
            color['r'] = color['r'] + step['r'];
            color['g'] = color['g'] + step['g'];
            color['b'] = color['b'] + step['b'];
            if (step_number < 100) {ldelim}
                step_number ++;
                setTimeout(function(){ldelim}blink(field, color, source_color, step, step_number){rdelim}, 15);
            {rdelim} else {ldelim}
                field.style.backgroundColor = 'RGB(' + source_color['r'] + ', ' + source_color['g'] + ', ' + source_color['b'] + ')';
            {rdelim}
        {rdelim}
            for(var i = 0; i < required_fields.length; i++) {ldelim}
                var field = required_fields[i];
                var error_span = document.getElementById(field + '_error');
                if (this.elements[field].value == '') {ldelim}
                    checked = false;
                    blink(this.elements[field], '#ff0000', '#ffffff');
                    error_span.innerHTML = "{$MOD.LBL_REQUIRED_FIELD}";
                    error_span.style.display = 'block';
                {rdelim} else {ldelim}
                    error_span.style.display = 'none';
                {rdelim}
            {rdelim}
        return checked;
    {rdelim});
</script>