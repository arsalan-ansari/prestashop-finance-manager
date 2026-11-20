<?php

function upgrade_module_1_0_4($module)
{
    if (Configuration::get('FINANCE_PABBLY_WEBHOOK_ENABLED') === false) {
        Configuration::updateValue('FINANCE_PABBLY_WEBHOOK_ENABLED', 0);
    }

    if (Configuration::get('FINANCE_PABBLY_WEBHOOK_URL') === false) {
        Configuration::updateValue('FINANCE_PABBLY_WEBHOOK_URL', '');
    }

    return true;
}
