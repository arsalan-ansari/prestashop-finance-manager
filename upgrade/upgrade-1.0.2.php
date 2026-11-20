<?php

function upgrade_module_1_0_2($module)
{
    // Enable transaction history by default when upgrading an existing installation.
    if (Configuration::get('FINANCE_HISTORY_ENABLED') === false) {
        Configuration::updateValue('FINANCE_HISTORY_ENABLED', 1);
    }

    return true;
}
