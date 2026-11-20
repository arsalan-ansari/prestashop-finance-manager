{extends file="layouts/layout.tpl"}

{block name="content"}

<div class="panel">

    <div class="panel-heading">
        <i class="icon-tags"></i>
        Categories
    </div>

    <div class="panel-body">

        <div class="mb-3">
            <a href="{$current|escape:'html':'UTF-8'}&addfinance_category&token={$token|escape:'html':'UTF-8'}"
               class="btn btn-primary">
                <i class="icon-plus"></i>
                Add Category
            </a>
        </div>

        {$list}

    </div>

</div>

{/block}