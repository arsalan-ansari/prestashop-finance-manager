<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class FinanceManager extends Module
{
    public function __construct()
    {
        $this->name = 'financemanager';
        $this->tab = 'administration';
        $this->version = '1.0.4';
        $this->author = 'Arsalan';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->is_configurable = true;

        parent::__construct();

        $this->displayName = $this->trans(
            'Finance Manager',
            [],
            'Modules.Financemanager.Admin'
        );

        $this->description = $this->trans(
            'Simple cashbook module for offline sales and expenses.',
            [],
            'Modules.Financemanager.Admin'
        );

        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    public function install()
    {
        return parent::install()
            && $this->installDatabase()
            && $this->installTabs();
    }

    public function uninstall()
    {
        return $this->uninstallTabs()
            && $this->uninstallDatabase()
            && parent::uninstall();
    }

    protected function installDatabase()
    {
        $file = dirname(__FILE__) . '/sql/install.php';

        if (!file_exists($file)) {
            return false;
        }

        return (bool) include $file;
    }

    protected function uninstallDatabase()
    {
        $file = dirname(__FILE__) . '/sql/uninstall.php';

        if (!file_exists($file)) {
            return false;
        }

        return (bool) include $file;
    }

    protected function installTabs()
    {
        $tabs = [
            [
                'class_name' => 'AdminFinanceDashboard',
                'name' => 'Finance Manager',
                'parent' => 'AdminParentOrders',
            ],
            [
                'class_name' => 'AdminFinanceCashbook',
                'name' => 'Cashbook',
                'parent' => 'AdminFinanceDashboard',
            ],
            [
                'class_name' => 'AdminFinanceCategories',
                'name' => 'Categories',
                'parent' => 'AdminFinanceDashboard',
            ],
            [
                'class_name' => 'AdminFinanceReports',
                'name' => 'Reports',
                'parent' => 'AdminFinanceDashboard',
            ],
            [
                'class_name' => 'AdminFinanceHistory',
                'name' => 'History',
                'parent' => 'AdminFinanceDashboard',
            ],
        ];

        foreach ($tabs as $tabData) {
            if (!$this->installTab(
                $tabData['class_name'],
                $tabData['name'],
                $tabData['parent']
            )) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallTabs()
    {
        $classes = [
            'AdminFinanceHistory',
            'AdminFinanceReports',
            'AdminFinanceCategories',
            'AdminFinanceCashbook',
            'AdminFinanceDashboard',
        ];

        foreach ($classes as $class) {
            $idTab = (int) Tab::getIdFromClassName($class);

            if ($idTab) {
                $tab = new Tab($idTab);

                if (Validate::isLoadedObject($tab)) {
                    if (!$tab->delete()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function installTab($className, $name, $parentClass)
    {
        if (Tab::getIdFromClassName($className)) {
            return true;
        }

        $tab = new Tab();

        $tab->active = 1;
        $tab->class_name = $className;
        $tab->module = $this->name;
        $parentId = (int) Tab::getIdFromClassName($parentClass);
        if (!$parentId) {
            return false;
        }
        
        $tab->id_parent = $parentId;

        foreach (Language::getLanguages(false) as $language) {
            $tab->name[(int) $language['id_lang']] = $name;
        }

        return $tab->add();
    }

    public function getContent()
    {
        if (!Context::getContext()->employee->isSuperAdmin()) {
            return $this->displayError(
                $this->trans(
                    'Only the SuperAdmin can access Finance Manager configuration.',
                    [],
                    'Modules.Financemanager.Admin'
                )
            );
        }

        $output = '';

        if (Tools::isSubmit('submitFinanceManagerSettings')) {
            $enabled = (int)Tools::getValue('FINANCE_HISTORY_ENABLED');
            $webhookEnabled = (int)Tools::getValue('FINANCE_PABBLY_WEBHOOK_ENABLED');
            $webhookUrl = trim(Tools::getValue('FINANCE_PABBLY_WEBHOOK_URL'));

            Configuration::updateValue('FINANCE_HISTORY_ENABLED', $enabled ? 1 : 0);
            Configuration::updateValue('FINANCE_PABBLY_WEBHOOK_ENABLED', $webhookEnabled ? 1 : 0);
            Configuration::updateValue('FINANCE_PABBLY_WEBHOOK_URL', $webhookUrl);

            $output .= $this->displayConfirmation(
                $this->trans(
                    'Settings updated successfully.',
                    [],
                    'Modules.Financemanager.Admin'
                )
            );
        }

        $output .= $this->renderConfigurationForm();

        return $output;
    }

    protected function renderConfigurationForm()
    {
        if (Configuration::get('FINANCE_HISTORY_ENABLED') === false) {
            Configuration::updateValue('FINANCE_HISTORY_ENABLED', 1);
        }

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->trans(
                        'Finance Manager Settings',
                        [],
                        'Modules.Financemanager.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans(
                            'Transaction History',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                        'name' => 'FINANCE_HISTORY_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'history_enabled_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'history_enabled_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                        'desc' => $this->trans(
                            'When enabled, Finance Manager records who added, changed, or deleted a cashbook transaction. History is visible to SuperAdmin only.',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans(
                            'Pabbly Webhook',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                        'name' => 'FINANCE_PABBLY_WEBHOOK_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'pabbly_webhook_enabled_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'pabbly_webhook_enabled_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                        'desc' => $this->trans(
                            'Send Finance Manager transaction events to Pabbly Connect.',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans(
                            'Pabbly Webhook URL',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                        'name' => 'FINANCE_PABBLY_WEBHOOK_URL',
                        'desc' => $this->trans(
                            'Paste the Pabbly Connect webhook listener URL here.',
                            [],
                            'Modules.Financemanager.Admin'
                        ),
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                    'class' => 'btn btn-primary pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitFinanceManagerSettings';

        $helper->fields_value['FINANCE_HISTORY_ENABLED'] = (int)Configuration::get(
            'FINANCE_HISTORY_ENABLED',
            null,
            null,
            null,
            1
        );

        $helper->fields_value['FINANCE_PABBLY_WEBHOOK_ENABLED'] = (int)Configuration::get(
            'FINANCE_PABBLY_WEBHOOK_ENABLED',
            null,
            null,
            null,
            1
        );

        $helper->fields_value['FINANCE_PABBLY_WEBHOOK_URL'] = (string)Configuration::get(
            'FINANCE_PABBLY_WEBHOOK_URL'
        );

        return $helper->generateForm([$fieldsForm]);
    }

}