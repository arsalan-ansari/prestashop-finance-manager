{extends file="layouts/layout.tpl"}

{block name="content"}

<div class="row">

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                Today's Cash In
            </div>
            <div class="card-body">
                <h2 class="text-success">
                    {$today_income|floatval|string_format:"%.2f"}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                Today's Cash Out
            </div>
            <div class="card-body">
                <h2 class="text-danger">
                    {$today_expense|floatval|string_format:"%.2f"}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                This Month Cash In
            </div>
            <div class="card-body">
                <h2 class="text-success">
                    {$month_income|floatval|string_format:"%.2f"}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                This Month Cash Out
            </div>
            <div class="card-body">
                <h2 class="text-danger">
                    {$month_expense|floatval|string_format:"%.2f"}
                </h2>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">

    <div class="col-lg-12">

        <div class="card">

            <div class="card-header">
                Finance Manager
            </div>

            <div class="card-body">

                <p>
                    Finance Manager module installed successfully.
                </p>

                <p>
                    Dashboard is ready.
                </p>

                <p>
                    Next Part:
                    Categories CRUD
                </p>

            </div>

        </div>

    </div>

</div>

{/block}