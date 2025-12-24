<?php

namespace crmeb\command;

use app\services\system\config\SystemConfigServices;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class OverseasInit extends Command
{
    protected function configure()
    {
        $this->setName('overseas:init')
            ->setDescription('Initialize Overseas Lite defaults (small-team backend).');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!config('overseas.enabled', false)) {
            $output->warning('Overseas Lite is disabled (app.overseas_mode=0). Nothing to do.');
            return 0;
        }

        /** @var SystemConfigServices $config */
        $config = app()->make(SystemConfigServices::class);

        $this->upsertConfig($config, 'site_name', 'Overseas Store');
        $this->upsertConfig($config, 'custom_admin_js', '');
        $this->upsertConfig($config, 'model_checkbox', []);

        $output->info('Overseas Lite defaults applied.');
        return 0;
    }

    /**
     * @param SystemConfigServices $config
     * @param string $menuName
     * @param mixed $value
     * @return void
     */
    protected function upsertConfig(SystemConfigServices $config, string $menuName, $value): void
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);
        if ($config->count(['menu_name' => $menuName])) {
            $config->update($menuName, ['value' => $encoded], 'menu_name');
        } else {
            $config->save([
                'menu_name' => $menuName,
                'type' => 'text',
                'input_type' => 'input',
                'config_tab_id' => 1,
                'value' => $encoded,
                'status' => 1,
                'info' => '',
            ]);
        }
    }
}
