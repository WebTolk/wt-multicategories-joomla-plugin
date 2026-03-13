<?php
/**
 * CLI command to rebuild WT Multicategories map.
 *
 * @package    System - WT Multicategories
 * @version    1.2.0
 * @Author     Sergey Tolkachyov, https://web-tolk.ru
 * @copyright  Copyright (C) 2025 Sergey Tolkachyov
 * @license    GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since      1.3.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Console;

use Joomla\Console\Command\AbstractCommand;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Plugin\System\Wtmulticategories\Service\MappingService;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function is_string;
use function strtolower;
use function trim;

defined('_JEXEC') or die;

/**
 * Rebuild mapping table rows from custom field values.
 *
 * @since  1.3.0
 */
class RebuildMappingsCommand extends AbstractCommand
{
    /**
     * Default command name.
     *
     * @var string
     * @since 1.3.0
     */
    protected static $defaultName = 'wtmulticategories:rebuild-map';

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since 1.3.0
     */
    protected function configure(): void
    {
        $help = <<<HELP
<info>%command.name%</info> rebuilds the WT Multicategories mapping table from custom field values.

Examples:
  <info>php %command.full_name%</info>
  <info>php %command.full_name% --context=content</info>
  <info>php %command.full_name% --context=contact --field-id=12</info>
HELP;

        $this->setDescription('Rebuild WT Multicategories map table');
        $this->setHelp($help);
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'Which mapping context to rebuild: all, content, contact', 'all');
        $this->addOption('field-id', null, InputOption::VALUE_REQUIRED, 'Override configured field id for the selected single context');
    }

    /**
     * Execute the command.
     *
     * @param   InputInterface   $input
     * @param   OutputInterface  $output
     *
     * @return  int
     *
     * @since 1.3.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io           = new SymfonyStyle($input, $output);
        $contextInput = strtolower(trim((string) $input->getOption('context')));
        $fieldIdInput = $input->getOption('field-id');
        $fieldId      = is_string($fieldIdInput) ? (int) trim($fieldIdInput) : 0;

        if (!in_array($contextInput, ['all', 'content', 'contact'], true))
        {
            $io->error('Invalid --context value. Allowed values: all, content, contact');

            return Command::INVALID;
        }

        if ($fieldId > 0 && $contextInput === 'all')
        {
            $io->error('Use --field-id only with --context=content or --context=contact.');

            return Command::INVALID;
        }

        $plugin = PluginHelper::getPlugin('system', 'wtmulticategories');
        $params = new Registry($plugin->params ?? '');

        $targets = [];

        if ($contextInput === 'all' || $contextInput === 'content')
        {
            $targets[] = [
                'label'   => 'com_content.article',
                'context' => 'com_content.article',
                'fieldId' => $contextInput === 'content' && $fieldId > 0
                    ? $fieldId
                    : (int) $params->get('multicategories_com_content_field_id', 0),
            ];
        }

        if ($contextInput === 'all' || $contextInput === 'contact')
        {
            $targets[] = [
                'label'   => 'com_contact.contact',
                'context' => 'com_contact.contact',
                'fieldId' => $contextInput === 'contact' && $fieldId > 0
                    ? $fieldId
                    : (int) $params->get('multicategories_com_contact_field_id', 0),
            ];
        }

        $service = new MappingService();
        $service->setDatabase(Factory::getContainer()->get(DatabaseDriver::class));

        $io->title('WT Multicategories map rebuild');

        $rebuiltCounts = [];
        $skipped       = [];

        foreach ($targets as $target)
        {
            if ((int) $target['fieldId'] <= 0)
            {
                $skipped[] = $target['label'];
                $io->warning('Skip ' . $target['label'] . ': field id is not configured.');
                continue;
            }

            $count = $service->rebuildAllMappings($target['context'], (int) $target['fieldId']);
            $rebuiltCounts[] = $target['label'] . ' => ' . $count;
            $io->text('Rebuilt ' . $target['label'] . ' using field #' . (int) $target['fieldId'] . ': ' . $count . ' rows.');
        }

        if (empty($rebuiltCounts))
        {
            $io->error('Nothing rebuilt. Configure a field id or pass --field-id.');

            return Command::FAILURE;
        }

        $io->success('Done: ' . implode('; ', $rebuiltCounts));

        if (!empty($skipped))
        {
            $io->note('Skipped: ' . implode(', ', $skipped));
        }

        return Command::SUCCESS;
    }
}
