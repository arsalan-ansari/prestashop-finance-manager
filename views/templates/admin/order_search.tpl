<div class="modal fade"
     id="financeOrderSearchModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button"
                        class="close"
                        data-dismiss="modal">

                    <span>&times;</span>

                </button>

                <h4 class="modal-title">
                    Select Cash Order
                </h4>

            </div>

            <div class="modal-body">

                <div class="form-group">

                    <input
                        type="text"
                        id="finance_order_search"
                        class="form-control"
                        placeholder="Search by Order ID, Customer Name or Phone">

                </div>

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th width="90">
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th width="150">
                                Amount
                            </th>

                            <th width="170">
                                Date
                            </th>

                            <th width="120">

                            </th>

                        </tr>

                    </thead>

                    <tbody id="finance_order_results">

                        <tr>

                            <td colspan="5" class="text-center">

                                Start typing to search...

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-default"
                    data-dismiss="modal">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>