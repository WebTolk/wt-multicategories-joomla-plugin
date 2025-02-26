<?php
/**
 * @package    System - WT Multicategories
 * @version     1.1.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2025 Sergey Tolkachyov
 * @license     GNU/GPL https://www.gnu.org/licenses/gpl-3.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wtmulticategories\Field;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\NoteField;

use function defined;
use function simplexml_load_file;
use function strtoupper;

defined('_JEXEC') or die;

class PlugininfoField extends NoteField
{

    protected $type = 'Plugininfo';

    /**
     * Method to get the field input markup for a spacer.
     * The spacer does not have accept input.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        $data = $this->form->getData();
        $element = 	$data->get('element');
        $folder = 	$data->get('folder');


        $doc = Factory::getApplication()->getDocument();
        $wa = $doc->getWebAssetManager();
        $wa->addInlineStyle("
			.plugin-info-img svg:hover * {
				cursor:pointer;
			}
			.plugin-info-img a::before {
				content: '';
			}
		");

        $wt_plugin_info = simplexml_load_file(JPATH_SITE."/plugins/".$folder."/".$element."/".$element.".xml");


        $html = '
				<div class="row shadow py-4">
					<div class="plugin-info-img col-2">
						<a href="https://web-tolk.ru" target="_blank">
									<svg width="200" height="50" xmlns="http://www.w3.org/2000/svg">
										 <g>
										  <title>Go to https://web-tolk.ru</title>
										  <text font-weight="bold" xml:space="preserve" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="32" id="svg_3" y="36.085949" x="8.152073" stroke-opacity="null" stroke-width="0" stroke="#000" fill="#0fa2e6">Web</text>
										  <text font-weight="bold" xml:space="preserve" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="32" id="svg_4" y="36.081862" x="74.239105" stroke-opacity="null" stroke-width="0" stroke="#000" fill="#384148">Tolk</text>
										 </g>
									</svg>
						</a>
					</div>
					<div class="col-10 p-2">
						<span class="badge bg-success">v.'.$wt_plugin_info->version.'</span>
						'.Text::_("PLG_".strtoupper($folder)."_".strtoupper($element)."_DESC").'
					</div>
				</div>
		';
        return $html;
    }

    /**
     * @return  string  The field label markup.
     *
     * @since   1.7.0
     */
    protected function getLabel()
    {
        return ' ';

    }

    /**
     * Method to get the field title.
     *
     * @return  string  The field title.
     *
     * @since   1.7.0
     */
    protected function getTitle()
    {
        return $this->getLabel();
    }
}