<div class="panel">

    <div class="panel-heading">
        <i class="icon-book"></i>
        Finance Ledger
    </div>

    <div class="panel-body">

        <div class="row">

            <div class="col-lg-4 col-lg-offset-4">

                <div class="panel panel-primary">

                    <div class="panel-heading text-center">
                        Cash In Hand
                    </div>

                    <div class="panel-body text-center">

                        <h2 style="margin:0;font-size:32px;font-weight:bold;">
                            {$cash_in_hand|string_format:"%.2f"}
                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <table class="table table-bordered table-striped">

            <thead>

                <tr>

                    <th width="120">Date</th>

                    <th width="100">Transaction ID</th>

                    <th>Description</th>

                    <th width="120" class="text-right">
                        Cash In
                    </th>

                    <th width="120" class="text-right">
                        Cash Out
                    </th>

                    <th width="140" class="text-right">
                        Balance
                    </th>

                </tr>

            </thead>

            <tbody>

                {foreach from=$ledger item=row}

                    <tr>

                        <td>
                            {$row.transaction_date}
                        </td>

                        <td>
                            #{$row.id_cashbook}
                        </td>

                        <td>

                            {$row.description}

                            {if $row.id_order}

                                <br>

                                <small class="text-muted">
                                    Order #{$row.id_order}
                                </small>

                            {/if}

                        </td>

                        <td class="text-right">

                            {if $row.cash_in neq ''}

                                <span class="text-success">
                                    {$row.cash_in|string_format:"%.2f"}
                                </span>

                            {else}

                                -

                            {/if}

                        </td>

                        <td class="text-right">

                            {if $row.cash_out neq ''}

                                <span class="text-danger">
                                    {$row.cash_out|string_format:"%.2f"}
                                </span>

                            {else}

                                -

                            {/if}

                        </td>

                        <td class="text-right">

                            <strong>

                                {$row.balance|string_format:"%.2f"}

                            </strong>

                        </td>

                    </tr>

                {foreachelse}

                    <tr>

                        <td colspan="6" class="text-center">

                            No transactions found.

                        </td>

                    </tr>

                {/foreach}

            </tbody>

        </table>

    </div>

</div>