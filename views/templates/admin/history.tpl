<div class="panel">

    <div class="panel-heading">
        <i class="icon-history"></i>
        Transaction History
    </div>

    <div class="panel-body">

        {if $transaction && $transaction->id}

            <div class="alert alert-info">
                <strong>Transaction #{$transaction->id_cashbook}</strong>
                &nbsp;|&nbsp;
                {$transaction->transaction_date}
                &nbsp;|&nbsp;
                {$transaction->description|escape:'htmlall':'UTF-8'}
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="160">Date / Time</th>
                        <th width="160">Employee</th>
                        <th width="100">Action</th>
                        <th width="150">Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>

                <tbody>
                    {foreach from=$history item=row}
                        <tr>
                            <td>{$row.date_add}</td>
                            <td>{$row.employee_name|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                {if $row.action == 'Updated'}
                                    <span class="badge badge-warning">Updated</span>
                                {elseif $row.action == 'Added'}
                                    <span class="badge badge-success">Added</span>
                                {else}
                                    <span class="badge badge-danger">Deleted</span>
                                {/if}
                            </td>
                            <td>{$row.field_display|escape:'htmlall':'UTF-8'}</td>
                            <td>{$row.old_display|escape:'htmlall':'UTF-8'}</td>
                            <td>{$row.new_display|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    {foreachelse}
                        <tr>
                            <td colspan="6" class="text-center">
                                No history found for this transaction.
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <a href="{$back_url|escape:'htmlall':'UTF-8'}" class="btn btn-default">
                <i class="icon-arrow-left"></i>
                Back to Cashbook
            </a>

        {else}

            <div class="alert alert-warning">
                Transaction not found.
            </div>

        {/if}

    </div>

</div>
