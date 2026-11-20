$(document).ready(function () {

    const modal = $('#financeOrderSearchModal');
    const search = $('#finance_order_search');
    const results = $('#finance_order_results');

    let timer = null;

    $(document).on('click', '#btnSearchOrder', function (e) {
        e.preventDefault();

        search.val('');
        results.html(
            '<tr><td colspan="5" class="text-center">Start typing to search...</td></tr>'
        );

        modal.modal('show');
        search.focus();
    });

    search.on('keyup', function () {

        clearTimeout(timer);

        timer = setTimeout(function () {

            loadOrders(search.val());

        }, 300);

    });

    function loadOrders(keyword)
    {
        results.html(
            '<tr><td colspan="5" class="finance-search-loading">Searching...</td></tr>'
        );
    
        $.ajax({
    
            url: financeAjaxUrl,
    
            type: 'POST',
    
            dataType: 'json',
    
            data: {
                ajax: 1,
                action: 'searchOrders',
                keyword: keyword
            },
    
            success: function (response) {
    
                if (!response.success) {
    
                    results.html(
                        '<tr><td colspan="5" class="finance-search-error">Unable to load orders.</td></tr>'
                    );
    
                    return;
                }
    
                renderRows(response.orders);
    
            },
    
            error: function () {
    
                results.html(
                    '<tr><td colspan="5" class="finance-search-error">Server error.</td></tr>'
                );
    
            }
    
        });
    
    }

    function renderRows(orders)
    {
        if (!orders.length) {

            results.html(
                '<tr><td colspan="5" class="text-center">No orders found.</td></tr>'
            );

            return;
        }

        let html = '';

        $.each(orders, function (i, order) {

            html += '<tr>';

            html += '<td>#' + order.id_order + '</td>';

            html += '<td>';

            html += '<strong>' + order.customer + '</strong>';

            if (order.reference) {
                html += '<br><small>' + order.reference + '</small>';
            }

            html += '</td>';

            html += '<td>$' + parseFloat(order.total_paid_tax_incl).toFixed(2) + '</td>';

            html += '<td>' + order.date_add + '</td>';

            html += '<td>';

            html += '<button type="button"';

            html += ' class="btn btn-primary btn-sm finance-select-order"';

            html += ' data-id="' + order.id_order + '"';

            html += ' data-amount="' + order.total_paid_tax_incl + '"';

            html += ' data-customer="' + order.customer + '"';

            html += '>';

            html += 'Select';

            html += '</button>';

            html += '</td>';

            html += '</tr>';

        });

        results.html(html);
    }

    $(document).on('click', '.finance-select-order', function () {

        const id = $(this).data('id');
        const amount = $(this).data('amount');
        const customer = $(this).data('customer');
    
        $('#id_order').val(id);
    
        $('#amount')
            .val(parseFloat(amount).toFixed(2))
            .prop('readonly', true);
    
        $('#description').val(
            'Cash Sale #' + id + ' - ' + customer
        );
    
        $('#finance_order_number').text('#' + id);
    
        $('#finance_order_customer').text(customer);
    
        $('#finance_order_amount').text(
            '$' + parseFloat(amount).toFixed(2)
        );
    
        $('#finance-order-placeholder').hide();
    
        $('#finance-selected-order').show();
    
        modal.modal('hide');
    
    });
    
    $(document).on('click', '#finance_change_order', function () {

        $('#finance-selected-order').hide();
    
        $('#finance-order-placeholder').show();
    
        $('#id_order').val('');
        
        $('#amount').val('');
    
        $('#finance_order_number').text('-');
    
        $('#finance_order_customer').text('-');
    
        $('#finance_order_amount').text('-');
    
        $('#finance_order_search').val('');
    
        $('#finance_order_results').html(
            '<tr><td colspan="5" class="text-center">Start typing to search...</td></tr>'
        );
    
        $('#financeOrderSearchModal').modal('show');
    
        $('#finance_order_search').focus();
    
    });
    
    if (typeof financeSelectedOrder !== 'undefined') {

        $('#id_order').val(financeSelectedOrder.id_order);
    
        $('#finance_order_number').text(
            '#' + financeSelectedOrder.id_order
        );
    
        $('#finance_order_customer').text(
            financeSelectedOrder.customer
        );
    
        $('#finance_order_amount').text(
            '$' + parseFloat(financeSelectedOrder.amount).toFixed(2)
        );
    
        $('#description').val(
            'Cash Sale #' +
            financeSelectedOrder.id_order +
            ' - ' +
            financeSelectedOrder.customer
        );
        
        $('#finance-order-placeholder').hide();
    
        $('#finance-selected-order').show();
    
        $('#amount')
            .val(parseFloat(financeSelectedOrder.amount).toFixed(2))
            .prop('readonly', true);
    
    }
    
    function updateIncomeSource()
    {
        
        const source = $('input[name="income_source"]:checked').val();
        
        if (typeof source === 'undefined') {
            return;
        }

        if (source === 'order') {

            if (!$('#id_order').val()) {
            
                $('#finance-order-placeholder').show();
                $('#finance-selected-order').hide();
            
            } else {
            
                $('#finance-order-placeholder').hide();
                $('#finance-selected-order').show();
            
            }
        
            $('#btnSearchOrder').prop('disabled', false);
        
            $('#amount')
                .prop('readonly', true)
                .addClass('disabled');
        
        } else {

            $('#finance-order-placeholder').hide();

            $('#finance-selected-order').hide();

            $('#id_order').val('');

            $('#finance_order_number').text('-');
            $('#finance_order_customer').text('-');
            $('#finance_order_amount').text('-');

            $('#amount')
                .val('')
                .prop('readonly', false)
                .removeClass('disabled');

            $('#description').val('');
        }
    }

    if ($('input[name="income_source"]').length) {
    
        $('input[name="income_source"]').on('change', function () {
            updateIncomeSource();
        });
    
    }

    if ($('input[name="income_source"]').length) {
        updateIncomeSource();
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Manual Editing
    |--------------------------------------------------------------------------
    */
    
    $(document).on('keydown', '#amount', function () {
    
        const source = $('input[name="income_source"]:checked').val();
    
        if (source === 'order') {
            return false;
        }
    
    });
    
    /*
    |--------------------------------------------------------------------------
    | Prevent Paste
    |--------------------------------------------------------------------------
    */
    
    $(document).on('paste', '#amount', function (e) {
    
        const source = $('input[name="income_source"]:checked').val();
    
        if (source === 'order') {
            e.preventDefault();
        }
    
    });
    
    $('form').on('submit', function (e) {

        var type = $('#type').val();
    
        if (!$('#transaction_date').val()) {
            alert('Please select a transaction date.');
            e.preventDefault();
            return false;
        }
    
        if (type === 'income') {
    
            var source = $('input[name="income_source"]:checked').val();
    
            if (source === 'order') {
    
                if (!$('#id_order').val()) {
                    alert('Please select a Cash Sale.');
                    e.preventDefault();
                    return false;
                }
    
            } else {
    
                if ($.trim($('#amount').val()) === '' || parseFloat($('#amount').val()) <= 0) {
                    alert('Please enter an amount.');
                    e.preventDefault();
                    return false;
                }
    
            }
    
        } else {
    
            if ($('#id_category').length && !$('#id_category').val()) {
                alert('Please select a category.');
                e.preventDefault();
                return false;
            }
    
            if ($.trim($('#amount').val()) === '' || parseFloat($('#amount').val()) <= 0) {
                alert('Please enter an amount.');
                e.preventDefault();
                return false;
            }
    
        }
    
    });

});