<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Manager - Cashbook Print</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #111; margin: 0; }
        .print-header { margin-bottom: 20px; }
        h1 { margin: 0 0 6px; font-size: 22px; }
        .meta { font-size: 12px; color: #555; }
        .actions { margin-bottom: 15px; }
        .actions button { padding: 8px 14px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 7px; }
        th { background: #eee; text-align: left; }
        .right { text-align: right; }
        .income { color: #087f23; }
        .expense { color: #c00; }
        .summary { margin-top: 18px; width: 360px; margin-left: auto; }
        .summary td { padding: 6px 8px; }
        .summary .total { font-weight: bold; }
        .footer { margin-top: 20px; font-size: 10px; color: #777; }
        @media print {
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="actions">
    <button type="button" onclick="window.print();">Print</button>
</div>

<div class="print-header">
    <h1>Finance Manager - Cashbook</h1>
    <div class="meta">
        Period: {$from} to {$to}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th width="90">Date</th>
            <th width="70">ID</th>
            <th width="85">Type</th>
            <th width="130">Category</th>
            <th width="75">Order</th>
            <th>Description</th>
            <th width="110" class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
            <tr>
                <td>{$row.transaction_date}</td>
                <td>#{$row.id_cashbook}</td>
                <td>{$row.type|ucfirst}</td>
                <td>{$row.category|escape:'htmlall':'UTF-8'}</td>
                <td>{if $row.id_order}#{$row.id_order}{else}-{/if}</td>
                <td>{$row.description|escape:'htmlall':'UTF-8'}</td>
                <td class="right {if $row.type == 'income'}income{else}expense{/if}">
                    {if $row.type == 'income'}+{else}-{/if}{$row.amount|string_format:"%.2f"}
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="7" style="text-align:center;">No transactions found for this period.</td>
            </tr>
        {/foreach}
    </tbody>
</table>

<table class="summary">
    <tr>
        <td>Cash In</td>
        <td class="right income">+{$cash_in|string_format:"%.2f"}</td>
    </tr>
    <tr>
        <td>Cash Out</td>
        <td class="right expense">-{$cash_out|string_format:"%.2f"}</td>
    </tr>
    <tr class="total">
        <td>Net</td>
        <td class="right">{$net|string_format:"%.2f"}</td>
    </tr>
</table>

<div class="footer">
    Printed by {$employee_name|escape:'htmlall':'UTF-8'} on {$smarty.now|date_format:"%d-%m-%Y %H:%M"}
</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html>
