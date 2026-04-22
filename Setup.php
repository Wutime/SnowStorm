<?php

namespace Wutime\SnowStorm;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public static $usesComposer = true;

    public function checkRequirements(&$errors = [], &$warnings = [])
    {
        if (self::$usesComposer) {
            $vendorDirectory = sprintf('%s/vendor', $this->addOn->getAddOnDirectory());
            if (!file_exists($vendorDirectory)) {
                $errors[] = 'vendor folder does not exist - cannot proceed with addon install';
            }
        }
    }

    public function installStep1()
    {
        $this->removeLegacyInstallTrackerOption();

        $this->schemaManager()->alterTable('xf_user_option', function (Alter $table)
        {
            $table->addColumn('wutime_snowstorm_enable', 'tinyint')->setDefault(1)->after('use_tfa');
        });
    }

    public function upgrade1020070Step1()
    {
        $this->removeLegacyInstallTrackerOption();

        $this->schemaManager()->alterTable('xf_user_option', function (Alter $table)
        {
            $table->addColumn('wutime_snowstorm_enable', 'tinyint')->setDefault(1)->after('use_tfa');
        });
    }

    public function uninstallStep1()
    {
        $this->removeLegacyInstallTrackerOption();

        $this->schemaManager()->alterTable('xf_user_option', function (Alter $table)
        {
            $table->dropColumns(['wutime_snowstorm_enable']);
        });
    }

    protected function removeLegacyInstallTrackerOption()
    {
        $optionId = strtolower(str_replace(['/', '\\'], '_', $this->addOn->addon_id)) . '_install_version';
        \XF::db()->delete('xf_option', 'option_id = ?', $optionId);
    }

}
