{extends file="layouts/layout.tpl"}

{block name="content"}

<div class="panel">

    <div class="panel-heading">
        <i class="icon-money"></i>
        Cashbook
    </div>

    <div class="panel-body">

        <div class="row">

            <div class="col-lg-12">

                <a href="{$current|escape:'html':'UTF-8'}&addfinance_cashbook&type=income&token={$token|escape:'html':'UTF-8'}"
                   class="btn btn-success">
                    <i class="icon-plus"></i>
                    Cash In
                </a>

                <a href="{$current|escape:'html':'UTF-8'}&addfinance_cashbook&type=expense&token={$token|escape:'html':'UTF-8'}"
                   class="btn btn-danger">
                    <i class="icon-minus"></i>
                    Cash Out
                </a>

            </div>

        </div>

        <hr>

        {$list}

    </div>

</div>

{/block}